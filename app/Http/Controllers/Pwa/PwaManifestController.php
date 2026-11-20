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
     * Serves dynamic Service Worker script with cache versioning and comprehensive offline fallback configuration.
     */
    public function serviceWorker(): Response
    {
        $settings = $this->pwaService->getSettings();
        $appVersion = preg_replace('/[^0-9a-zA-Z\.\-]/', '', $settings->app_version ?? '1.0.0');
        $cssMtime = file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time();
        $cacheVersion = 'portfolio-pwa-v' . ($appVersion ?: '1.0.0') . '-' . $cssMtime;
        
        // Dynamic Tasbeeh Counter URLs
        $tasbeehUrls = [];
        try {
            $tasbeehs = \App\Models\Tasbeeh::query()->get(['id', 'user_id']);
            foreach ($tasbeehs as $t) {
                $tasbeehUrls[] = "/admin/zikr/tasbeeh/{$t->id}";
                if ($t->user_id) {
                    $tasbeehUrls[] = "/admin/zikr/tasbeeh/{$t->id}?user_id={$t->user_id}";
                }
                try {
                    $tasbeehUrls[] = url("/admin/zikr/tasbeeh/{$t->id}");
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            // Silently ignore if table not ready
        }

        // Comprehensive Precache Assets List for 100% Offline Resilience
        $coreRoutes = [
            '/',
            '/pwa/offline',
            '/admin',
            '/admin/zikr',
            '/admin/tasbeehs',
            '/admin/duas',
            '/admin/namaz-attendance',
            '/admin/namaz-attendance/dashboard',
            '/admin/namaz-settings',
            '/admin/date-of-births',
            '/admin/programs',
            '/assets/css/app.css',
            '/assets/js/app.js',
            '/assets/js/pwa/pwa-db.js',
            '/assets/js/pwa/pwa-sync.js',
            '/assets/js/pwa/pwa-installer.js',
            '/vendor/bootstrap/bootstrap.min.css',
            '/vendor/bootstrap/bootstrap.bundle.min.js',
            '/vendor/bootstrap-icons/bootstrap-icons.min.css',
            '/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
            '/vendor/bootstrap-icons/fonts/bootstrap-icons.woff',
            '/assets/fonts/pdms-saleem-quranfont.ttf',
            '/assets/fonts/indopak-quran.woff2',
            '/assets/images/amanullah.png',
            '/assets/pwa-icons/icon-192x192.png',
            '/assets/pwa-icons/icon-512x512.png',
        ];

        $precacheUrls = [];
        foreach ($coreRoutes as $r) {
            $precacheUrls[] = $r;
            try {
                $precacheUrls[] = url($r);
            } catch (\Throwable $e) {}
        }
        $precacheUrls = array_values(array_unique(array_merge($precacheUrls, $tasbeehUrls)));

        $precacheJson = json_encode(array_values($precacheUrls), JSON_UNESCAPED_SLASHES);

        $swScript = <<<JS
/* Progressive Web App Service Worker - High Resilience Offline Engine */
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
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.allSettled(
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

// Activate Event: Clean Purge Old Versions & Claim Clients Immediately
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(async (cacheNames) => {
            for (const name of cacheNames) {
                if (name !== CACHE_NAME && name.startsWith('portfolio-pwa-')) {
                    try {
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
    if (
        url.pathname.includes('/logout') ||
        url.pathname.includes('/pwa/sync/push') ||
        url.pathname.includes('/pwa/status') ||
        url.pathname.includes('/pwa/sync/pull')
    ) {
        return;
    }

    // 1. Application Scripts & Styles (CSS, JS, Vendor, PWA) -> Network-First with Cache Fallback
    const isAppScriptOrStyle = (
        request.destination === 'style' ||
        request.destination === 'script' ||
        url.pathname.includes('/assets/js/') ||
        url.pathname.includes('/assets/css/') ||
        url.pathname.includes('/vendor/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js')
    );

    if (isAppScriptOrStyle) {
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
                    const cache = await caches.open(CACHE_NAME);
                    let cached = await cache.match(request);
                    if (!cached) cached = await cache.match(request, { ignoreSearch: true });
                    if (!cached && url && url.pathname) cached = await cache.match(url.pathname, { ignoreSearch: true });
                    if (!cached && url && url.pathname) {
                        const keys = await cache.keys();
                        for (const k of keys) {
                            if (k.url.includes(url.pathname)) {
                                cached = await cache.match(k);
                                if (cached) break;
                            }
                        }
                    }
                    return cached;
                })
        );
        return;
    }

    // 2. Static Assets (Fonts, Images, Icons, CDN) -> Cache First with Network Fallback
    const isStaticAsset = (
        request.destination === 'font' ||
        request.destination === 'image' ||
        url.pathname.startsWith('/assets/pwa-icons/') ||
        url.pathname.startsWith('/assets/images/') ||
        url.pathname.startsWith('/assets/fonts/') ||
        url.pathname.includes('/fonts/') ||
        url.pathname.endsWith('.ttf') ||
        url.pathname.endsWith('.woff') ||
        url.pathname.endsWith('.woff2') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.jpg') ||
        url.pathname.endsWith('.svg') ||
        url.hostname.includes('cdn.jsdelivr.net') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com')
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request, { ignoreSearch: true }).then(async (cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                if (url && url.pathname) {
                    const cache = await caches.open(CACHE_NAME);
                    const pathMatch = await cache.match(url.pathname, { ignoreSearch: true });
                    if (pathMatch) return pathMatch;
                }

                return fetch(request).then((networkResponse) => {
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
                }).catch(async () => {
                    // Offline fallback for images
                    if (request.destination === 'image' || url.pathname.endsWith('.png') || url.pathname.endsWith('.jpg')) {
                        return (await caches.match('/assets/pwa-icons/icon-192x192.png')) ||
                               (await caches.match('/assets/images/amanullah.png'));
                    }
                    // Offline fallback for fonts
                    if (request.destination === 'font' || url.pathname.endsWith('.woff2') || url.pathname.endsWith('.ttf')) {
                        const cache = await caches.open(CACHE_NAME);
                        const keys = await cache.keys();
                        for (const k of keys) {
                            if (k.url.endsWith('.woff2') || k.url.endsWith('.ttf')) {
                                const m = await cache.match(k);
                                if (m) return m;
                            }
                        }
                    }
                });
            })
        );
        return;
    }

    // 3. HTML Navigation Strategy -> Network First with Resilient Multi-tier Cache Fallback
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
                                if (url && url.href) {
                                    cache.put(url.href, clone.clone()).catch(() => {});
                                }
                            }).catch(() => {});
                        } catch (e) {}
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    // Tier 1: Exact request match
                    let cachedResponse = await cache.match(request);
                    if (!cachedResponse) {
                        cachedResponse = await cache.match(request, { ignoreSearch: true });
                    }
                    if (!cachedResponse && url && url.pathname) {
                        cachedResponse = await cache.match(url.pathname, { ignoreSearch: true });
                    }
                    if (!cachedResponse && url && url.href) {
                        cachedResponse = await cache.match(url.href, { ignoreSearch: true });
                    }

                    // Tier 2: Search cached keys intelligently
                    if (!cachedResponse && url && url.pathname) {
                        const keys = await cache.keys();
                        
                        // A. If navigating to a tasbeeh counter page (/admin/zikr/tasbeeh/...)
                        if (url.pathname.includes('/admin/zikr/tasbeeh/')) {
                            // Try exact tasbeeh path match in keys
                            for (const k of keys) {
                                if (k.url.includes(url.pathname)) {
                                    cachedResponse = await cache.match(k);
                                    if (cachedResponse) break;
                                }
                            }
                            // If this specific counter page isn't cached, fallback to any cached counter page
                            if (!cachedResponse) {
                                for (const k of keys) {
                                    if (k.url.includes('/admin/zikr/tasbeeh/')) {
                                        cachedResponse = await cache.match(k);
                                        if (cachedResponse) break;
                                    }
                                }
                            }
                        }

                        // B. Try matching requested pathname in cached keys
                        if (!cachedResponse) {
                            for (const k of keys) {
                                if (k.url.includes(url.pathname)) {
                                    cachedResponse = await cache.match(k);
                                    if (cachedResponse) break;
                                }
                            }
                        }

                        // C. Fallback for /admin routes -> /admin/zikr, /admin/tasbeehs, /admin, etc.
                        if (!cachedResponse && url.pathname.includes('/admin')) {
                            const fallbacks = ['/admin/zikr', '/admin/tasbeehs', '/admin', '/admin/namaz-attendance', '/admin/duas', '/admin/programs'];
                            for (const fb of fallbacks) {
                                for (const k of keys) {
                                    if (k.url.includes(fb)) {
                                        cachedResponse = await cache.match(k);
                                        if (cachedResponse) break;
                                    }
                                }
                                if (cachedResponse) break;
                            }
                        }
                    }

                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // Tier 3: Return dedicated offline fallback page
                    const offlinePage = (await cache.match(OFFLINE_URL, { ignoreSearch: true })) ||
                                       (await cache.match('/pwa/offline', { ignoreSearch: true }));
                    if (offlinePage) {
                        return offlinePage;
                    }

                    // Tier 4: Fallback to any cached HTML page in cache
                    try {
                        const keys = await cache.keys();
                        for (const key of keys) {
                            if (key.url.includes('/admin') || key.url.includes('/pwa')) {
                                const m = await cache.match(key);
                                if (m) return m;
                            }
                        }
                    } catch (e) {}

                    // Tier 5: Clean styled fallback response
                    return new Response('<!DOCTYPE html><html lang="ur" dir="ltr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline Mode</title><style>body{background:#070d18;color:#f1f5f9;font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px;text-align:center}.card{background:#08111e;border:1px solid #142845;border-radius:24px;padding:36px 24px;max-width:440px;width:100%;box-shadow:0 20px 50px rgba(0,0,0,0.8)}h3{color:#fff;margin:12px 0 8px}p{color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:24px}.btn{background:#38bdf8;color:#070d18;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:none;cursor:pointer;font-size:0.95rem;transition:0.2s}.btn:hover{background:#0ea5e9}.btn-alt{background:transparent;color:#94a3b8;border:1px solid #1e293b;margin-top:10px}.btn-alt:hover{color:#fff;border-color:#38bdf8}</style></head><body><div class="card"><div style="font-size:3rem;line-height:1">📿</div><h3>Offline Mode Active</h3><p>Aap internet se disconnected hain. Zikr Dashboard aur Counter offline chal raha hai:</p><div style="display:flex;flex-direction:column;gap:10px"><a href="/admin/zikr" class="btn">Open Zikr Dashboard</a><button onclick="window.location.reload()" class="btn btn-alt">Refresh Page</button></div></div></body></html>', {
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Default network fallback with cache fallback
    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && !networkResponse.bodyUsed && isCacheable(request)) {
                    try {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone).catch(() => {})).catch(() => {});
                    } catch (e) {}
                }
                return networkResponse;
            })
            .catch(async () => {
                return (await caches.match(request, { ignoreSearch: true })) ||
                       (url && url.pathname ? await caches.match(url.pathname, { ignoreSearch: true }) : null);
            })
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
