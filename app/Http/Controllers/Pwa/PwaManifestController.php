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
        $cacheVersion = 'portfolio-pwa-v' . preg_replace('/[^0-9a-zA-Z\.\-]/', '', $settings->app_version ?? '1.0.0');
        // Precache URLs list (relative paths match current origin and scheme automatically)
        $precacheUrls = [
            '/',
            '/pwa/offline',
            '/assets/css/app.css',
            '/assets/js/app.js',
            '/assets/js/pwa/pwa-db.js',
            '/assets/js/pwa/pwa-sync.js',
            '/assets/js/pwa/pwa-installer.js',
            '/assets/pwa-icons/icon-192x192.png',
            '/assets/pwa-icons/icon-512x512.png',
        ];

        $precacheJson = json_encode($precacheUrls, JSON_UNESCAPED_SLASHES);

        $swScript = <<<JS
/* Progressive Web App Service Worker */
const CACHE_NAME = '{$cacheVersion}';
const OFFLINE_URL = '/pwa/offline';
const PRECACHE_ASSETS = {$precacheJson};

// Install Event: Cache Core App Shell & Static Assets with Resilient Fallback
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_ASSETS.map((url) => {
                    return fetch(url).then((response) => {
                        if (response && response.ok) {
                            return cache.put(url, response);
                        }
                    }).catch((err) => {
                        console.warn('PWA: Precaching skipped for asset:', url);
                    });
                })
            );
        })
    );
});

// Activate Event: Clear Stale Cache Versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event Strategy:
// - Static Assets & Fonts: Cache First (fallback to network)
// - HTML & Dynamic Routes: Network First (fallback to Cache or Offline Page)
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Don't intercept Laravel CSRF, Auth logout, or dynamic Sync API calls
    if (url.pathname.includes('/logout') || url.pathname.includes('/pwa/sync/push')) {
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
                    // Update cache in background
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {/* Ignore network errors on background refresh */});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const cloned = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned));
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

    // 2. HTML Navigation Strategy -> Network First with Offline Fallback
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const cloned = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
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
            })
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

