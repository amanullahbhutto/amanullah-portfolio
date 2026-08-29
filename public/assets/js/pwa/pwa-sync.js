/**
 * PwaSync: Real-time Synchronization Engine for Progressive Web App
 * Handles online/offline transitions, queued outbox push, delta pulls, idempotency and UI badges.
 */
class PwaSync {
    constructor() {
        this.isSyncing = false;
        this.csrfToken = null;
        this.statusEndpoint = '/pwa/status';
        this.pushEndpoint = '/pwa/sync/push';
        this.pullEndpoint = '/pwa/sync/pull';
        this.appState = {
            isOnline: navigator.onLine,
            isAppActive: true,
            pendingCount: 0,
            lastSyncedAt: null,
            appVersion: '1.0.0',
        };
    }

    async init() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const userId = document.querySelector('meta[name="auth-user-id"]')?.getAttribute('content') || 'guest';

        await window.PwaDB.init(userId);

        // Register online/offline event listeners
        window.addEventListener('online', () => this.handleNetworkChange(true));
        window.addEventListener('offline', () => this.handleNetworkChange(false));

        // Sync when app comes to foreground
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                this.syncNow();
            }
        });

        // Listen for manual sync buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-pwa-sync-now]');
            if (btn) {
                e.preventDefault();
                this.syncNow(true);
            }
        });

        // Listen for Service Worker background sync triggers
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'TRIGGER_PWA_SYNC') {
                    this.syncNow();
                }
            });
        }

        // Initial check & sync
        await this.refreshPendingCount();
        if (navigator.onLine) {
            this.syncNow();
        } else {
            this.updateBadge('offline');
        }
    }

    async handleNetworkChange(isOnline) {
        this.appState.isOnline = isOnline;
        if (isOnline) {
            this.updateBadge('syncing');
            if (window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('info', 'Internet connection restored. Synchronizing data...');
            }
            await this.syncNow();
        } else {
            this.updateBadge('offline');
            if (window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('warning', 'You are now offline. Changes will be saved locally.');
            }
        }
    }

    async refreshPendingCount() {
        try {
            const pending = await window.PwaDB.getPendingOutbox();
            this.appState.pendingCount = pending.length;
            return pending;
        } catch (e) {
            console.error('Failed to get pending count:', e);
            return [];
        }
    }

    async enqueueAction(entity, action, payload = {}, tempId = null) {
        const item = {
            uuid: crypto.randomUUID(),
            idempotency_key: crypto.randomUUID(),
            entity,
            action,
            payload,
            temp_id: tempId || `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        };

        await window.PwaDB.addOutbox(item);
        await this.refreshPendingCount();

        if (navigator.onLine) {
            this.syncNow();
        } else {
            this.updateBadge('pending');
        }

        return item;
    }

    async syncNow(isManual = false) {
        if (this.isSyncing) return;
        if (!navigator.onLine) {
            this.updateBadge('offline');
            if (isManual && window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('warning', 'Internet connection is unavailable.');
            }
            return;
        }

        this.isSyncing = true;
        this.updateBadge('syncing');

        try {
            // 1. Verify Application Active Status
            const statusRes = await fetch(this.statusEndpoint, {
                headers: { 'Accept': 'application/json' }
            });

            if (statusRes.ok) {
                const statusData = await statusRes.json();
                this.appState.isAppActive = statusData.is_active;
                this.appState.appVersion = statusData.app_version;

                if (!statusData.is_active) {
                    this.updateBadge('disabled');
                    window.dispatchEvent(new CustomEvent('pwa:app-disabled', { detail: statusData }));
                    this.isSyncing = false;
                    return;
                }
            }

            // 2. Fetch pending outbox operations
            const pending = await this.refreshPendingCount();

            if (pending.length > 0) {
                const pushPayload = {
                    operations: pending.map(item => ({
                        uuid: item.uuid,
                        idempotency_key: item.idempotency_key,
                        entity: item.entity,
                        action: item.action,
                        temp_id: item.temp_id,
                        payload: item.payload,
                        retry_count: item.retry_count || 0,
                    }))
                };

                const pushRes = await fetch(this.pushEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(pushPayload),
                });

                if (pushRes.ok) {
                    const pushResult = await pushRes.json();
                    if (pushResult.success && pushResult.synced_operations) {
                        for (const op of pushResult.synced_operations) {
                            if (op.status === 'synced' || op.status === 'already_synced') {
                                await window.PwaDB.removeOutbox(op.uuid);
                            } else {
                                await window.PwaDB.updateOutbox(op.uuid, {
                                    status: 'failed',
                                    error: op.error || 'Sync failed on server',
                                    retry_count: (op.retry_count || 0) + 1,
                                });
                            }
                        }
                    }
                } else if (pushRes.status === 401) {
                    this.updateBadge('auth_required');
                    this.isSyncing = false;
                    return;
                }
            }

            // 3. Pull latest delta updates
            const lastSyncedAt = await window.PwaDB.getMeta('last_synced_at');
            const pullUrl = lastSyncedAt ? `${this.pullEndpoint}?last_synced_at=${encodeURIComponent(lastSyncedAt)}` : this.pullEndpoint;

            const pullRes = await fetch(pullUrl, {
                headers: { 'Accept': 'application/json' }
            });

            if (pullRes.ok) {
                const pullResult = await pullRes.json();
                if (pullResult.success && pullResult.data) {
                    // Update cached tasbeehs
                    if (pullResult.data.tasbeehs) {
                        for (const t of pullResult.data.tasbeehs) {
                            await window.PwaDB.setCachedRecord('tasbeehs', t.id, t);
                        }
                    }
                    if (pullResult.data.zikr_summary) {
                        await window.PwaDB.setMeta('zikr_summary', pullResult.data.zikr_summary);
                    }
                    await window.PwaDB.setMeta('last_synced_at', pullResult.server_time || new Date().toISOString());
                }
            }

            await this.refreshPendingCount();
            this.updateBadge('synced');

            if (isManual && window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('success', 'Data synchronized successfully with server!');
            }

            window.dispatchEvent(new CustomEvent('pwa:sync-completed'));
        } catch (err) {
            console.error('PwaSync error:', err);
            await this.refreshPendingCount();
            this.updateBadge(this.appState.pendingCount > 0 ? 'pending' : 'online');
        } finally {
            this.isSyncing = false;
        }
    }

    updateBadge(state) {
        const badges = document.querySelectorAll('[data-pwa-sync-badge]');
        badges.forEach(badge => {
            badge.className = 'pwa-sync-badge';
            switch (state) {
                case 'syncing':
                    badge.classList.add('badge-syncing');
                    badge.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i><span>Syncing...</span>';
                    break;
                case 'offline':
                    badge.classList.add('badge-offline');
                    badge.innerHTML = `<i class="bi bi-cloud-slash-fill me-1"></i><span>Offline${this.appState.pendingCount > 0 ? ` (${this.appState.pendingCount})` : ''}</span>`;
                    break;
                case 'pending':
                    badge.classList.add('badge-pending');
                    badge.innerHTML = `<i class="bi bi-hourglass-split me-1"></i><span>Pending (${this.appState.pendingCount})</span>`;
                    break;
                case 'synced':
                    badge.classList.add('badge-synced');
                    badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i><span>Synced</span>';
                    setTimeout(() => {
                        if (badge.classList.contains('badge-synced')) {
                            badge.classList.remove('badge-synced');
                            badge.classList.add('badge-online');
                            badge.innerHTML = '<i class="bi bi-wifi me-1"></i><span>Online</span>';
                        }
                    }, 3000);
                    break;
                case 'disabled':
                    badge.classList.add('badge-disabled');
                    badge.innerHTML = '<i class="bi bi-slash-circle-fill me-1"></i><span>App Disabled</span>';
                    break;
                case 'auth_required':
                    badge.classList.add('badge-auth');
                    badge.innerHTML = '<i class="bi bi-lock-fill me-1"></i><span>Login Required</span>';
                    break;
                default:
                    badge.classList.add('badge-online');
                    badge.innerHTML = '<i class="bi bi-wifi me-1"></i><span>Online</span>';
                    break;
            }
        });
    }

    // High-level offline action helpers
    async saveZikrCount(tasbeehId, count) {
        return this.enqueueAction('zikr_count', 'create', {
            tasbeeh_id: parseInt(tasbeehId, 10),
            count: parseInt(count, 10)
        });
    }

    async completeTasbeehToday(tasbeehId) {
        return this.enqueueAction('tasbeeh_complete_today', 'update', {
            tasbeeh_id: parseInt(tasbeehId, 10)
        });
    }

    async completeAllTasbeehsToday() {
        return this.enqueueAction('zikr_complete_all', 'update', {});
    }

    async resetTasbeeh(tasbeehId) {
        return this.enqueueAction('tasbeeh_reset_single', 'update', {
            tasbeeh_id: parseInt(tasbeehId, 10)
        });
    }

    async resetAllTasbeehs() {
        return this.enqueueAction('zikr_reset_all', 'update', {});
    }

    async resetLifetime() {
        return this.enqueueAction('lifetime_reset', 'update', {});
    }
}

window.PwaSync = new PwaSync();
document.addEventListener('DOMContentLoaded', () => window.PwaSync.init());

