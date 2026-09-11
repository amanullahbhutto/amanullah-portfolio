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
        this.clientId = (typeof crypto !== 'undefined' && crypto.randomUUID)
            ? crypto.randomUUID()
            : 'client_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        window.PWA_CLIENT_ID = this.clientId;
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

        // Read dynamic endpoint URLs from meta tags if available
        const statusMeta = document.querySelector('meta[name="pwa-status-url"]');
        if (statusMeta && statusMeta.getAttribute('content')) {
            this.statusEndpoint = statusMeta.getAttribute('content');
        }
        const pushMeta = document.querySelector('meta[name="pwa-sync-push-url"]');
        if (pushMeta && pushMeta.getAttribute('content')) {
            this.pushEndpoint = pushMeta.getAttribute('content');
        }
        const pullMeta = document.querySelector('meta[name="pwa-sync-pull-url"]');
        if (pullMeta && pullMeta.getAttribute('content')) {
            this.pullEndpoint = pullMeta.getAttribute('content');
        }

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
            this.warmOfflineCache();
        } else {
            this.updateBadge('offline');
        }

        // Automatic periodic background sync heartbeat (every 20s if pending items exist)
        setInterval(() => {
            if (navigator.onLine && !this.isSyncing) {
                this.refreshPendingCount().then(pending => {
                    if (pending.length > 0) {
                        this.syncNow();
                    }
                });
            }
        }, 20000);
    }

    async warmOfflineCache() {
        if (!('caches' in window) || !navigator.onLine) return;
        try {
            const names = await caches.keys();
            const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
            if (!pwaCacheName) return;

            const cache = await caches.open(pwaCacheName);

            // 1. Immediately cache current page
            cache.add(window.location.href).catch(() => {});
            cache.add(window.location.pathname).catch(() => {});
            if (window.location.search) {
                cache.add(window.location.pathname + window.location.search).catch(() => {});
            }

            // 2. Comprehensive Admin Routes to pre-cache with active user session
            const selectedUserId = document.querySelector('meta[name="selected-user-id"]')?.getAttribute('content');
            const routesToWarm = [
                '/',
                '/admin',
                '/admin/zikr',
                '/admin/tasbeehs',
                '/admin/namaz-attendance',
                '/admin/namaz-attendance/dashboard',
                '/admin/namaz-settings',
                '/admin/date-of-births',
                '/pwa/offline'
            ];

            if (selectedUserId) {
                routesToWarm.push(`/admin/zikr?user_id=${selectedUserId}`);
                routesToWarm.push(`/admin/tasbeehs?user_id=${selectedUserId}`);
            }

            // Check if application is running in a subfolder
            const basePath = window.location.pathname.includes('/admin')
                ? window.location.pathname.substring(0, window.location.pathname.indexOf('/admin'))
                : '';
            if (basePath) {
                routesToWarm.push(`${basePath}/admin/zikr`);
                routesToWarm.push(`${basePath}/admin/tasbeehs`);
                if (selectedUserId) {
                    routesToWarm.push(`${basePath}/admin/zikr?user_id=${selectedUserId}`);
                    routesToWarm.push(`${basePath}/admin/tasbeehs?user_id=${selectedUserId}`);
                }
            }

            // 3. Scan DOM for all active Tasbeeh counter URLs and navigation links on page
            document.querySelectorAll('a[href*="/admin/zikr"], a[href*="/admin/tasbeehs"]').forEach(a => {
                const href = a.getAttribute('href');
                if (href && !routesToWarm.includes(href)) {
                    routesToWarm.push(href);
                }
            });

            // Also check IndexedDB for any cached tasbeeh master records
            try {
                const cachedTasbeehs = await window.PwaDB.getAllCachedRecords('tasbeehs');
                if (Array.isArray(cachedTasbeehs)) {
                    cachedTasbeehs.forEach(t => {
                        if (t && t.id) {
                            const url = `/admin/zikr/tasbeeh/${t.id}`;
                            if (!routesToWarm.includes(url)) routesToWarm.push(url);
                        }
                    });
                }
            } catch (e) {}

            // 4. Fetch and store each route in parallel
            await Promise.allSettled(
                routesToWarm.map(route => {
                    return fetch(route, { credentials: 'same-origin' })
                        .then(res => {
                            if (res && res.ok && res.status === 200) {
                                cache.put(route, res.clone()).catch(() => {});
                                try {
                                    const parsedUrl = new URL(route, window.location.origin);
                                    if (parsedUrl.pathname !== route) {
                                        cache.put(parsedUrl.pathname, res.clone()).catch(() => {});
                                    }
                                } catch (e) {}
                            }
                        })
                        .catch(() => {});
                })
            );
        } catch (e) {
            console.warn('PwaSync warmOfflineCache notice:', e);
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
            // Quick backup syncs in case WiFi / DNS was still settling
            setTimeout(() => {
                if (navigator.onLine && !this.isSyncing) {
                    this.refreshPendingCount().then(pending => {
                        if (pending && pending.length > 0) this.syncNow();
                    });
                }
            }, 1500);
            setTimeout(() => {
                if (navigator.onLine && !this.isSyncing) {
                    this.refreshPendingCount().then(pending => {
                        if (pending && pending.length > 0) this.syncNow();
                    });
                }
            }, 3500);
            this.warmOfflineCache();
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

    _generateUUID() {
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
            try { return crypto.randomUUID(); } catch (_) {}
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    async enqueueAction(entity, action, payload = {}, tempId = null) {
        const item = {
            uuid: this._generateUUID(),
            idempotency_key: this._generateUUID(),
            entity,
            action,
            payload,
            created_at: new Date().toISOString(),
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
            // 1. Verify Application Active Status (Non-blocking check so offline push is never killed by status lag)
            try {
                const statusRes = await fetch(this.statusEndpoint, {
                    headers: { 'Accept': 'application/json' },
                    signal: typeof AbortSignal !== 'undefined' && AbortSignal.timeout ? AbortSignal.timeout(4000) : undefined
                });

                if (statusRes && statusRes.ok) {
                    const statusData = await statusRes.json();
                    this.appState.isAppActive = statusData.is_active;
                    this.appState.appVersion = statusData.app_version;

                    // Persist max_offline_days so cached pages can enforce the setting
                    if (statusData.max_offline_days && parseInt(statusData.max_offline_days, 10) > 0) {
                        try { localStorage.setItem('pwa_max_offline_days', String(parseInt(statusData.max_offline_days, 10))); } catch (_) {}
                    }

                    if (!statusData.is_active) {
                        this.updateBadge('disabled');
                        window.dispatchEvent(new CustomEvent('pwa:app-disabled', { detail: statusData }));
                        this.isSyncing = false;
                        return;
                    }
                }
            } catch (statusErr) {
                console.warn('PwaSync status check notice (proceeding with outbox push):', statusErr);
            }

            // 2. Fetch pending outbox operations
            const pending = await this.refreshPendingCount();

            let syncedCount = 0;
            let hasTasbeehSync = false;
            let hasNamazSync = false;

            if (pending.length > 0) {
                const selectedUserId = document.querySelector('meta[name="selected-user-id"]')?.getAttribute('content');
                const pushPayload = {
                    user_id: selectedUserId ? parseInt(selectedUserId, 10) : undefined,
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
                                syncedCount++;
                                const origItem = pending.find(p => p.uuid === op.uuid);
                                const entityType = (origItem && origItem.entity) || '';
                                if (entityType.includes('zikr') || entityType.includes('tasbeeh')) {
                                    hasTasbeehSync = true;
                                }
                                if (entityType.includes('namaz')) {
                                    hasNamazSync = true;
                                }
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
            const selectedUserId = document.querySelector('meta[name="selected-user-id"]')?.getAttribute('content');
            let pullUrl = this.pullEndpoint;
            const queryParams = [];
            if (lastSyncedAt) queryParams.push(`last_synced_at=${encodeURIComponent(lastSyncedAt)}`);
            if (selectedUserId) queryParams.push(`user_id=${encodeURIComponent(selectedUserId)}`);
            if (queryParams.length > 0) pullUrl += `?${queryParams.join('&')}`;

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
                    if (pullResult.data.namaz_stats) {
                        await window.PwaDB.setMeta('namaz_stats', pullResult.data.namaz_stats);
                    }
                    if (pullResult.data.namaz_ledger) {
                        await window.PwaDB.setMeta('namaz_ledger', pullResult.data.namaz_ledger);
                    }
                    if (pullResult.data.muslim_users) {
                        await window.PwaDB.setMeta('muslim_users', pullResult.data.muslim_users);
                    }
                    await window.PwaDB.setMeta('last_synced_at', pullResult.server_time || new Date().toISOString());
                }
            }

            await this.refreshPendingCount();
            this.updateBadge('synced');

            if (syncedCount > 0 && window.App && typeof window.App.showToast === 'function') {
                let msg = 'Aap ka offline data kamyabi se database me add kar diya gaya hai!';
                if (hasTasbeehSync && hasNamazSync) {
                    msg = 'Aap ka offline data (Tasbeeh aur Namaz) kamyabi se database me add kar diya gaya hai!';
                } else if (hasTasbeehSync) {
                    msg = 'Aap ka offline Tasbeeh data kamyabi se database me add kar diya gaya hai!';
                } else if (hasNamazSync) {
                    msg = 'Aap ka offline Namaz data kamyabi se database me add kar diya gaya hai!';
                }
                window.App.showToast('success', msg);
            } else if (isManual && window.App && typeof window.App.showToast === 'function') {
                window.App.showToast('success', 'Data synchronized successfully with server!');
            }

            // After tasbeeh/zikr sync: invalidate cached HTML pages so fresh data loads
            if (hasTasbeehSync || syncedCount > 0) {
                this._invalidateZikrPageCaches();
            }

            window.dispatchEvent(new CustomEvent('pwa:sync-completed', {
                detail: { syncedCount, hasTasbeehSync, hasNamazSync, data: (pullResult && pullResult.data) ? pullResult.data : null }
            }));
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
    async saveZikrCount(tasbeehId, count, date = null, shouldBroadcast = true) {
        const todayStr = date || (new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0'));
        try {
            localStorage.setItem('pwa_zikr_active_date', todayStr);
        } catch (_) {}
        const actionItem = await this.enqueueAction('zikr_count', 'create', {
            tasbeeh_id: parseInt(tasbeehId, 10),
            count: parseInt(count, 10),
            date: todayStr,
        });
        if (shouldBroadcast) {
            this.broadcastZikrCountUpdate(tasbeehId, count);
        }
        return actionItem;
    }

    async completeTasbeehToday(tasbeehId, count = null) {
        const todayStr = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0');
        let addCount = count ? parseInt(count, 10) : 0;
        if (!addCount || isNaN(addCount)) {
            const card = document.getElementById(`tasbeeh-card-${tasbeehId}`) || document.querySelector(`[data-tasbeeh-card="${tasbeehId}"]`);
            addCount = parseInt(card?.dataset?.dailyTarget || '100', 10) || 100;
        }
        return this.saveZikrCount(tasbeehId, addCount, todayStr, true);
    }

    async completeAllTasbeehsToday() {
        const todayStr = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0');
        const actionItem = await this.enqueueAction('zikr_complete_all', 'update', {
            date: todayStr
        });
        this.broadcastEvent('ZIKR_COMPLETE_ALL', { date: todayStr });
        return actionItem;
    }

    async resetTasbeeh(tasbeehId) {
        const actionItem = await this.enqueueAction('tasbeeh_reset_single', 'update', {
            tasbeeh_id: parseInt(tasbeehId, 10)
        });
        this.broadcastEvent('ZIKR_RESET_SINGLE', { tasbeehId: String(tasbeehId) });
        return actionItem;
    }

    async resetAllTasbeehs() {
        const actionItem = await this.enqueueAction('zikr_reset_all', 'update', {});
        this.broadcastEvent('ZIKR_RESET_ALL', {});
        return actionItem;
    }

    async resetLifetime() {
        const actionItem = await this.enqueueAction('lifetime_reset', 'update', {});
        this.broadcastEvent('ZIKR_LIFETIME_RESET', {});
        return actionItem;
    }

    async updateNamazStatus(userId, date, prayer, status) {
        const actionItem = await this.enqueueAction('namaz_attendance_status', 'update', {
            user_id: parseInt(userId, 10),
            attendance_date: date,
            prayer: prayer,
            status: status || ''
        });
        this.broadcastEvent('NAMAZ_STATUS_UPDATE', {
            userId: parseInt(userId, 10),
            date: date,
            prayer: prayer,
            status: status || ''
        });
        return actionItem;
    }

    async updateNamazDay(userId, date, statuses) {
        const actionItem = await this.enqueueAction('namaz_attendance_day', 'update', {
            user_id: parseInt(userId, 10),
            attendance_date: date,
            fajr_status: statuses.fajr || '',
            zuhr_status: statuses.zuhr || '',
            asr_status: statuses.asr || '',
            maghrib_status: statuses.maghrib || '',
            isha_status: statuses.isha || '',
        });
        this.broadcastEvent('NAMAZ_DAY_UPDATE', {
            userId: parseInt(userId, 10),
            date: date,
            statuses: statuses
        });
        return actionItem;
    }

    async updateNamazStartDate(userId, startDate) {
        const actionItem = await this.enqueueAction('namaz_start_date', 'update', {
            user_id: parseInt(userId, 10),
            namaz_start_date: startDate
        });
        this.broadcastEvent('NAMAZ_START_DATE_UPDATE', {
            userId: parseInt(userId, 10),
            startDate: startDate
        });
        return actionItem;
    }

    async deleteNamazAttendance(attendanceId, userId, date) {
        const actionItem = await this.enqueueAction('namaz_attendance_delete', 'delete', {
            attendance_id: attendanceId ? parseInt(attendanceId, 10) : null,
            user_id: parseInt(userId, 10),
            attendance_date: date || null
        });
        this.broadcastEvent('NAMAZ_ATTENDANCE_DELETE', {
            attendanceId: attendanceId,
            userId: parseInt(userId, 10),
            date: date || null
        });
        return actionItem;
    }

    async saveDateOfBirth(data) {
        return this.enqueueAction('date_of_birth', 'create', data);
    }

    async updateDateOfBirth(id, data) {
        return this.enqueueAction('date_of_birth', 'update', { id: parseInt(id, 10), ...data });
    }

    broadcastEvent(eventType, data = {}) {
        try {
            const payload = {
                type: eventType,
                clientId: this.clientId,
                ...data,
                timestamp: Date.now()
            };
            if ('BroadcastChannel' in window) {
                if (!this.zikrBroadcastChannel) {
                    this.zikrBroadcastChannel = new BroadcastChannel('portfolio_zikr_channel');
                }
                this.zikrBroadcastChannel.postMessage(payload);
            }
            localStorage.setItem('pwa_zikr_live_broadcast', JSON.stringify(payload));
        } catch (e) {}
    }

    broadcastZikrCountUpdate(tasbeehId, delta) {
        this.broadcastEvent('ZIKR_COUNT_INCREMENT', {
            tasbeehId: String(tasbeehId),
            delta: parseInt(delta, 10) || 0
        });
    }

    /**
     * Invalidate stale HTML caches for /admin/zikr and /admin/tasbeehs after sync.
     * Then, if the current page is one of those, reload it so fresh server data appears.
     */
    async _invalidateZikrPageCaches() {
        const zikrPatterns = [
            '/admin/zikr',
            '/admin/tasbeehs',
        ];

        // 1. Delete from Service Worker Cache
        try {
            if ('caches' in window) {
                const names = await caches.keys();
                const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                if (pwaCacheName) {
                    const cache = await caches.open(pwaCacheName);
                    const cachedReqs = await cache.keys();
                    for (const req of cachedReqs) {
                        const url = req.url || '';
                        const isZikrPage = zikrPatterns.some(pat => {
                            try {
                                const parsed = new URL(url);
                                return parsed.pathname === pat || parsed.pathname.startsWith(pat + '?') || parsed.pathname.startsWith(pat + '/');
                            } catch (_) { return url.includes(pat); }
                        });
                        // Only delete non-counter pages (don't wipe tasbeeh/6 etc.)
                        const isCounterPage = url.includes('/admin/zikr/tasbeeh/');
                        if (isZikrPage && !isCounterPage) {
                            await cache.delete(req).catch(() => {});
                        }
                    }
                }
            }
        } catch (e) {
            console.warn('PwaSync _invalidateZikrPageCaches cache delete error:', e);
        }

        // 2. If we are currently on /admin/zikr or /admin/tasbeehs — update DOM in-place instead of reloading.
        //    Reloading causes a flash and loses any visual context. Use reconcile with pulled server data instead.
        try {
            const currentPath = window.location.pathname;
            const isOnZikrOrTasbeehs = zikrPatterns.some(pat =>
                currentPath === pat || currentPath.startsWith(pat + '?') || currentPath.startsWith(pat + '/')
            );
            const isCounterPage = currentPath.includes('/admin/zikr/tasbeeh/');
            if (isOnZikrOrTasbeehs && !isCounterPage) {
                // The pwa:sync-completed event already triggers reconcileZikrOfflineCounts(data).
                // No reload needed — data is updated smoothly via DOM reconcile.
                // Only reload as a last resort if no tasbeeh cards are present on the page.
                const hasCards = document.querySelectorAll('[id^="tasbeeh-card-"]').length > 0;
                if (!hasCards) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1800);
                }
            }
        } catch (e) {}
    }
}

window.PwaSync = new PwaSync();
document.addEventListener('DOMContentLoaded', () => window.PwaSync.init());

