/**
 * PwaInstaller: Manages Progressive Web App Installation, Universal Guides, Service Worker Registration & App Status Overlays
 */
class PwaInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true || document.referrer.includes('android-app://');
    }

    init() {
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.setupAppDisabledListener();
        this.checkInitialAppStatus();
        this.initInstallButtons();
    }

    getSwUrl() {
        const meta = document.querySelector('meta[name="pwa-sw-url"]');
        return meta ? meta.getAttribute('content') : '/sw.js';
    }

    getStatusUrl() {
        const meta = document.querySelector('meta[name="pwa-status-url"]');
        return meta ? meta.getAttribute('content') : '/pwa/status';
    }

    initInstallButtons() {
        // If not already in standalone mode, show install buttons
        if (!this.isStandalone) {
            this.toggleInstallButtons(true);
        } else {
            this.toggleInstallButtons(false);
        }
    }

    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const swUrl = this.getSwUrl();
                const registration = await navigator.serviceWorker.register(swUrl, { scope: '/' });
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
        // Android / Chrome / Edge beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            if (!this.isStandalone) {
                this.toggleInstallButtons(true);
            }
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

            const modalDirectBtn = e.target.closest('#pwaModalDirectInstallBtn');
            if (modalDirectBtn) {
                e.preventDefault();
                this.handleDirectModalInstall();
            }
        });
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
        // 1. If standard browser prompt available (Android / Chrome / Edge)
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

        // 2. Open Universal Installation Modal
        const installModalEl = document.getElementById('pwaInstallModal');
        if (installModalEl && window.bootstrap) {
            // Select appropriate tab based on device
            if (this.isIos) {
                const iosTabBtn = document.getElementById('pwa-ios-tab');
                if (iosTabBtn) {
                    const tab = new bootstrap.Tab(iosTabBtn);
                    tab.show();
                }
            } else {
                const androidTabBtn = document.getElementById('pwa-android-tab');
                if (androidTabBtn) {
                    const tab = new bootstrap.Tab(androidTabBtn);
                    tab.show();
                }
            }

            const modal = new bootstrap.Modal(installModalEl);
            modal.show();
            return;
        }

        // 3. Fallback alert
        if (this.isIos) {
            alert('To install on iPhone/iPad: Tap the Share button (⎋) at bottom and select "Add to Home Screen".');
        } else {
            alert('To install this app: Tap your browser menu (⋮) and select "Install app" or "Add to Home screen".');
        }
    }

    async handleDirectModalInstall() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            if (outcome === 'accepted') {
                this.toggleInstallButtons(false);
                const installModalEl = document.getElementById('pwaInstallModal');
                if (installModalEl && window.bootstrap) {
                    const modal = bootstrap.Modal.getInstance(installModalEl);
                    if (modal) modal.hide();
                }
            }
        } else {
            const inlineAlert = document.getElementById('pwa-install-inline-alert');
            if (inlineAlert) {
                inlineAlert.classList.remove('d-none');
            } else {
                alert('Please tap your browser menu (⋮) at top right and select "Install app" or "Add to Home screen".');
            }
        }
    }

    setupAppDisabledListener() {
        window.addEventListener('pwa:app-disabled', (e) => {
            const data = e.detail || {};
            this.showDisabledOverlay(data.messages?.disabled || 'Mobile application is currently disabled by administrator.');
        });
    }

    async checkInitialAppStatus() {
        try {
            const statusUrl = this.getStatusUrl();
            const res = await fetch(statusUrl);
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
document.addEventListener('DOMContentLoaded', () => {
    window.PwaInstaller.init();
});
