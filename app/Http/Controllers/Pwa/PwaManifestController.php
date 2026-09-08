<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Services\PwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PwaManifestController extends Controller
{
    protected PwaService $pwaService;

    public function __construct(PwaService $pwaService)
    {
        $this->pwaService = $pwaService;
    }

    /**
     * Serves dynamic Web App Manifest JSON with correct HTTP headers.
     */
    public function manifest(): JsonResponse
    {
        $manifest = $this->pwaService->getManifestData();

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Serves dynamic Service Worker script with cache versioning and offline fallback configuration.
     */
    public function serviceWorker(): Response
    {
        $settings = $this->pwaService->getSettings();
        $cacheVersion = 'portfolio-pwa-v' . preg_replace('/[^0-9a-zA-Z\.\-]/', '', $settings->app_version ?? '1.0.0') . '-' . filemtime(__FILE__);
        
        // Dynamic Tasbeeh Counter URLs
        $tasbeehUrls = [];
        try {
            $activeTasbeehIds = \App\Models\Tasbeeh::query()
                ->where('is_active', true)
                ->pluck('id');
            foreach ($activeTasbeehIds as $tId) {
                $tasbeehUrls[] = "/admin/zikr/tasbeeh/{$tId}";
            }
        } catch (\Throwable $e) {
            // Silently ignore if table not ready
        }

        // Precache URLs list
        $precacheUrls = array_unique(array_merge([
            '/',
            '/pwa/offline',
            '/admin',
            '/admin/namaz-attendance',
            '/admin/zikr',
            '/admin/tasbeehs',
            '/admin/date-of-births',
            '/assets/css/app.css',
            '/assets/js/app.js',
            '/assets/js/pwa/pwa-db.js',
            '/assets/js/pwa/pwa-sync.js',
            '/assets/js/pwa/pwa-installer.js',
            '/assets/pwa-icons/icon-192x192.png',
            '/assets/pwa-icons/icon-512x512.png',
        ], $tasbeehUrls));

        $precacheJson = json_encode(array_values($precacheUrls), JSON_UNESCAPED_SLASHES);

        $swScript = <<<JS
/* Progressive Web App Service Worker */
const CACHE_NAME = '{$cacheVersion}';
const OFFLINE_URL = '/pwa/offline';
const PRECACHE_ASSETS = {$precacheJson};

// Helper: check if request is safe to cache (http/https only)
function isCacheable(req) {
    if (!req) return false;
    const urlStr = typeof req === 'string' ? req : (req.url || '');
    return urlStr.startsWith('http://') || urlStr.startsWith('https://');
}

// Install Event: Cache Core App Shell & Static Assets with Resilient Fallback
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_ASSETS.map((url) => {
                    return fetch(url, { credentials: 'same-origin' }).then((response) => {
                        if (response && response.ok && !response.bodyUsed && isCacheable(url)) {
                            try {
                                const resClone = response.clone();
                                return cache.put(url, resClone).catch(() => {});
                            } catch (e) {}
                        }
                    }).catch(() => {
                        // Silently ignore precaching errors for dynamic routes
                    });
                })
            );
        }).catch(() => {})
    );
});

// Activate Event: Clear Stale Cache Versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name).catch(() => {});
                    }
                })
            );
        }).then(() => self.clients.claim()).catch(() => {})
    );
});

// Fetch Event Strategy
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (!request || !request.url) return;

    // Strictly ignore non-http / non-https schemes (e.g. chrome-extension://, moz-extension://)
    if (!request.url.startsWith('http://') && !request.url.startsWith('https://')) {
        return;
    }

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    let url;
    try {
        url = new URL(request.url);
    } catch (e) {
        return;
    }

    // Don't intercept dynamic Sync API calls, status, or auth endpoints
    if (url.pathname.includes('/logout') || url.pathname.includes('/pwa/sync/push') || url.pathname.includes('/pwa/status') || url.pathname.includes('/pwa/sync/pull')) {
        return;
    }

    // 1. Static Asset Strategy (CSS, JS, Fonts, Images, Icons) -> Cache First
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font' ||
        request.destination === 'image' ||
        url.pathname.startsWith('/assets/') ||
        url.hostname.includes('cdn.jsdelivr.net')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Refresh in background if online
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200 && !networkResponse.bodyUsed && isCacheable(request)) {
                            try {
                                const clone = networkResponse.clone();
                                caches.open(CACHE_NAME).then((cache) => cache.put(request, clone).catch(() => {})).catch(() => {});
                            } catch (e) {}
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200 && !networkResponse.bodyUsed && isCacheable(request)) {
                        try {
                            const clone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone).catch(() => {})).catch(() => {});
                        } catch (e) {}
                    }
                    return networkResponse;
                }).catch(() => {
                    // Offline fallback for images
                    if (request.destination === 'image') {
                        return caches.match('/assets/pwa-icons/icon-192x192.png');
                    }
                });
            })
        );
        return;
    }

    // 2. HTML Navigation Strategy -> Network First with Resilient Cache Fallback
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200 && !networkResponse.bodyUsed && isCacheable(request)) {
                        try {
                            const clone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, clone.clone()).catch(() => {});
                                if (url && url.pathname) {
                                    cache.put(url.pathname, clone.clone()).catch(() => {});
                                }
                            }).catch(() => {});
                        } catch (e) {}
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Try exact request match first
                    let cachedResponse = await caches.match(request);
                    // Try matching request ignoring query string (e.g. ?user_id=1, ?source=pwa)
                    if (!cachedResponse) {
                        cachedResponse = await caches.match(request, { ignoreSearch: true });
                    }
                    // Try matching clean pathname (e.g. /admin/zikr/tasbeeh/8)
                    if (!cachedResponse && url && url.pathname) {
                        cachedResponse = await caches.match(url.pathname);
                    }
                    if (!cachedResponse && url && url.pathname) {
                        cachedResponse = await caches.match(url.pathname, { ignoreSearch: true });
                    }
                    if (!cachedResponse) {
                        cachedResponse = await caches.match(request.url, { ignoreSearch: true });
                    }
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Return dedicated offline fallback page
                    const offlinePage = await caches.match(OFFLINE_URL);
                    return offlinePage || new Response('Offline: Connection unavailable.', {
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Default network fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// Background Sync Handler (if supported by browser)
self.addEventListener('sync', (event) => {
    if (event.tag === 'pwa-sync-queue') {
        event.waitUntil(
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({ type: 'TRIGGER_PWA_SYNC' });
                });
            }).catch(() => {})
        );
    }
});
JS;

        return response($swScript, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Serves dedicated responsive offline fallback page.
     */
    public function offline(): View
    {
        $settings = $this->pwaService->getSettings();

        return view('admin.pwa.offline', compact('settings'));
    }
}

