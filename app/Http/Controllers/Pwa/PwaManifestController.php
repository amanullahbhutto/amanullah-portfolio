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
        $appVersion = preg_replace('/[^0-9a-zA-Z\.\-]/', '', $settings->app_version ?? '1.0.0');
        $cacheVersion = 'portfolio-pwa-v' . ($appVersion ?: '1.0.0');
        
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
            '/admin/namaz/attendance',
            '/admin/namaz/dashboard',
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

// Activate Event: Preserve & Migrate Cache Without Data Loss
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(async (cacheNames) => {
            const currentCache = await caches.open(CACHE_NAME);
            for (const name of cacheNames) {
                if (name !== CACHE_NAME && name.startsWith('portfolio-pwa-')) {
                    try {
                        const oldCache = await caches.open(name);
                        const oldKeys = await oldCache.keys();
                        for (const key of oldKeys) {
                            const oldResp = await oldCache.match(key);
                            if (oldResp) {
                                await currentCache.put(key, oldResp);
                            }
                        }
                        await caches.delete(name);
                    } catch (e) {}
                }
            }
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
                    // 1. Try exact request match first
                    let cachedResponse = await caches.match(request);
                    // 2. Try matching request ignoring query string (e.g. ?user_id=1, ?source=pwa)
                    if (!cachedResponse) {
                        cachedResponse = await caches.match(request, { ignoreSearch: true });
                    }
                    // 3. Try matching clean pathname (e.g. /admin/zikr/tasbeeh/8)
                    if (!cachedResponse && url && url.pathname) {
                        cachedResponse = await caches.match(url.pathname);
                    }
                    if (!cachedResponse && url && url.pathname) {
                        cachedResponse = await caches.match(url.pathname, { ignoreSearch: true });
                    }
                    if (!cachedResponse) {
                        cachedResponse = await caches.match(request.url, { ignoreSearch: true });
                    }
                    // 4. If looking for a tasbeeh counter page (/admin/zikr/tasbeeh/...) fallback to another cached tasbeeh or /admin/zikr
                    if (!cachedResponse && url && url.pathname.includes('/admin/zikr/tasbeeh')) {
                        cachedResponse = (await caches.match('/admin/zikr')) || (await caches.match('/admin/tasbeehs'));
                    }
                    // 5. If looking for any admin route, fallback to /admin/zikr, /admin/tasbeehs, or /admin
                    if (!cachedResponse && url && url.pathname.startsWith('/admin')) {
                        cachedResponse = (await caches.match('/admin/zikr')) || (await caches.match('/admin/tasbeehs')) || (await caches.match('/admin'));
                    }
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // 6. Return dedicated offline fallback page
                    const offlinePage = (await caches.match(OFFLINE_URL)) || (await caches.match('/pwa/offline'));
                    if (offlinePage) {
                        return offlinePage;
                    }
                    // 7. Fallback to any cached HTML page in cache
                    try {
                        const cache = await caches.open(CACHE_NAME);
                        const keys = await cache.keys();
                        for (const key of keys) {
                            if (key.url.includes('/admin') || key.url.includes('/pwa')) {
                                const m = await cache.match(key);
                                if (m) return m;
                            }
                        }
                    } catch (e) {}

                    // 8. Clean styled fallback response (never plain text)
                    return new Response('<!DOCTYPE html><html lang="ur" dir="ltr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline Mode</title><style>body{background:#070d18;color:#f1f5f9;font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px;text-align:center}.card{background:#08111e;border:1px solid #142845;border-radius:24px;padding:36px 24px;max-width:440px;width:100%;box-shadow:0 20px 50px rgba(0,0,0,0.8)}h3{color:#fff;margin:12px 0 8px}p{color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:24px}.btn{background:#38bdf8;color:#070d18;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:none;cursor:pointer;font-size:0.95rem;transition:0.2s}.btn:hover{background:#0ea5e9}.btn-alt{background:transparent;color:#94a3b8;border:1px solid #1e293b;margin-top:10px}.btn-alt:hover{color:#fff;border-color:#38bdf8}</style></head><body><div class="card"><div style="font-size:3rem;line-height:1">📿</div><h3>Offline Mode Active</h3><p>Aap internet se disconnected hain. Zikr Dashboard aur Counter offline chal raha hai:</p><div style="display:flex;flex-direction:column;gap:10px"><a href="/admin/zikr" class="btn">Open Zikr Dashboard</a><button onclick="window.location.reload()" class="btn btn-alt">Refresh Page</button></div></div></body></html>', {
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

