/**
 * PwaInstaller: Manages Progressive Web App Installation, iOS Guides, Service Worker Registration & App Status Overlays
 */
class PwaInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    init() {
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.setupAppDisabledListener();
        this.checkInitialAppStatus();
    }

    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                console.log('PWA Service Worker registered with scope:', registration.scope);

                // Listen for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('PWA: New version available. Reloading recommended.');
                            }
                        });
                    }
                });
            } catch (err) {
                console.warn('PWA Service Worker registration failed:', err);
            }
        }
    }

    setupInstallPrompt() {
        // Android / Desktop beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.toggleInstallButtons(true);
        });

        // App successfully installed
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.toggleInstallButtons(false);
            if (window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('success', 'Mobile Application installed successfully!');
            }
        });

        // Wire up clicks on all install buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-pwa-install-btn]');
            if (btn) {
                e.preventDefault();
                this.triggerInstall();
            }
        });

        // If iOS and not already standalone, make install buttons visible
        if (this.isIos && !this.isStandalone) {
            this.toggleInstallButtons(true);
        }
    }

    toggleInstallButtons(show) {
        const buttons = document.querySelectorAll('[data-pwa-install-btn]');
        buttons.forEach(btn => {
            if (show) {
                btn.classList.remove('d-none');
            } else {
                btn.classList.add('d-none');
            }
        });
    }

    async triggerInstall() {
        // 1. If standard browser prompt available (Android / Chrome)
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            console.log('User response to install prompt:', outcome);
            this.deferredPrompt = null;
            if (outcome === 'accepted') {
                this.toggleInstallButtons(false);
            }
            return;
        }

        // 2. If iOS Safari -> Open iOS Guide Modal
        if (this.isIos) {
            const iosModalEl = document.getElementById('pwaIosInstallModal');
            if (iosModalEl && window.bootstrap) {
                const modal = new bootstrap.Modal(iosModalEl);
                modal.show();
            } else {
                alert('To install on iPhone/iPad: Tap the Share button at bottom and select "Add to Home Screen".');
            }
            return;
        }

        // 3. Fallback instructions for other browsers
        alert('To install this app, tap your browser menu (⋮) and select "Install app" or "Add to Home screen".');
    }

    setupAppDisabledListener() {
        window.addEventListener('pwa:app-disabled', (e) => {
            const data = e.detail || {};
            this.showDisabledOverlay(data.messages?.disabled || 'Mobile application is currently disabled by administrator.');
        });
    }

    async checkInitialAppStatus() {
        try {
            const res = await fetch('/pwa/status');
            if (res.ok) {
                const data = await res.json();
                if (!data.is_active) {
                    this.toggleInstallButtons(false);
                    if (this.isStandalone) {
                        this.showDisabledOverlay(data.messages?.disabled || 'Application is currently disabled.');
                    }
                }
            }
        } catch (e) {
            // If offline, ignore network errors
        }
    }

    showDisabledOverlay(message) {
        let overlay = document.getElementById('pwa-disabled-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'pwa-disabled-overlay';
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(7,13,24,0.98);z-index:999999;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center;backdrop-filter:blur(10px);';
            overlay.innerHTML = `
                <div style="max-width:440px;background:#0c1626;border:1px solid #dc2626;border-radius:24px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.9);color:#fff;">
                    <div style="width:64px;height:64px;border-radius:50%;background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.4);display:inline-flex;align-items:center;justify-content:center;color:#ef4444;margin-bottom:16px;">
                        <i class="bi bi-slash-circle fs-1"></i>
                    </div>
                    <h4 style="font-weight:700;margin-bottom:8px;color:#fff;">Application Disabled</h4>
                    <p style="color:#94a3b8;font-size:0.95rem;line-height:1.6;margin-bottom:24px;">${message}</p>
                    <button type="button" class="btn btn-outline-theme btn-sm px-4" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Check Again
                    </button>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }
}

window.PwaInstaller = new PwaInstaller();
document.addEventListener('DOMContentLoaded', () => window.PwaInstaller.init());

