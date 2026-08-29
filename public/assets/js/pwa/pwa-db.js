/**
 * PwaDB: IndexedDB Management for Offline Storage & Queued Outbox
 * Partitioned by User ID for strict data isolation.
 */
class PwaDB {
    constructor() {
        this.db = null;
        this.userId = null;
        this.dbName = 'pwa_offline_db';
        this.version = 1;
    }

    async init(userId = 'guest') {
        this.userId = userId;
        const currentDbName = `${this.dbName}_${this.userId}`;

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(currentDbName, this.version);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // 1. Outbox Store for offline mutations
                if (!db.objectStoreNames.contains('outbox')) {
                    const outboxStore = db.createObjectStore('outbox', { keyPath: 'uuid' });
                    outboxStore.createIndex('status', 'status', { unique: false });
                    outboxStore.createIndex('created_at', 'created_at', { unique: false });
                    outboxStore.createIndex('entity', 'entity', { unique: false });
                }

                // 2. Cached business records per entity
                if (!db.objectStoreNames.contains('cached_records')) {
                    const recordsStore = db.createObjectStore('cached_records', { keyPath: 'cache_key' });
                    recordsStore.createIndex('entity', 'entity', { unique: false });
                    recordsStore.createIndex('record_id', 'record_id', { unique: false });
                }

                // 3. Conflicts store for manual conflict review
                if (!db.objectStoreNames.contains('conflicts')) {
                    db.createObjectStore('conflicts', { keyPath: 'uuid' });
                }

                // 4. App metadata store
                if (!db.objectStoreNames.contains('meta')) {
                    db.createObjectStore('meta', { keyPath: 'key' });
                }
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                resolve(this.db);
            };

            request.onerror = (event) => {
                console.error('PwaDB init error:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    // Helper: Run transaction
    _tx(storeName, mode = 'readonly') {
        if (!this.db) throw new Error('PwaDB is not initialized.');
        return this.db.transaction(storeName, mode).objectStore(storeName);
    }

    // LocalStorage Mirror Helpers for 100% Durability
    _backupToLocalStorage(record) {
        try {
            const key = `pwa_outbox_backup_${this.userId}`;
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            const index = existing.findIndex(i => i.uuid === record.uuid);
            if (index >= 0) {
                existing[index] = record;
            } else {
                existing.push(record);
            }
            localStorage.setItem(key, JSON.stringify(existing));
        } catch (e) {
            console.warn('localStorage backup error:', e);
        }
    }

    _removeFromLocalStorage(uuid) {
        try {
            const key = `pwa_outbox_backup_${this.userId}`;
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            const filtered = existing.filter(i => i.uuid !== uuid);
            localStorage.setItem(key, JSON.stringify(filtered));
        } catch (e) {
            console.warn('localStorage cleanup error:', e);
        }
    }

    _getLocalStorageBackup() {
        try {
            const key = `pwa_outbox_backup_${this.userId}`;
            return JSON.parse(localStorage.getItem(key) || '[]');
        } catch (e) {
            return [];
        }
    }

    // Outbox Operations
    async addOutbox(item) {
        const record = {
            uuid: item.uuid || crypto.randomUUID(),
            idempotency_key: item.idempotency_key || crypto.randomUUID(),
            user_id: this.userId,
            entity: item.entity,
            action: item.action || 'create', // create, update, delete
            temp_id: item.temp_id || null,
            payload: item.payload || {},
            created_at: new Date().toISOString(),
            status: 'pending', // pending, syncing, synced, failed
            retry_count: 0,
            error: null,
        };

        this._backupToLocalStorage(record);

        return new Promise((resolve, reject) => {
            try {
                const store = this._tx('outbox', 'readwrite');
                const req = store.put(record);
                req.onsuccess = () => resolve(record);
                req.onerror = () => {
                    console.warn('IndexedDB outbox write error, saved in localStorage backup:', req.error);
                    resolve(record);
                };
            } catch (e) {
                console.warn('IndexedDB unavailable, preserved in localStorage backup:', e);
                resolve(record);
            }
        });
    }

    async getPendingOutbox() {
        return new Promise((resolve) => {
            const backupItems = this._getLocalStorageBackup().filter(item => item.status === 'pending' || item.status === 'failed');

            try {
                const store = this._tx('outbox', 'readonly');
                const req = store.getAll();
                req.onsuccess = () => {
                    const dbPending = (req.result || []).filter(item => item.status === 'pending' || item.status === 'failed');
                    
                    // Merge unique records from IndexedDB and localStorage
                    const itemMap = new Map();
                    dbPending.forEach(item => itemMap.set(item.uuid, item));
                    backupItems.forEach(item => {
                        if (!itemMap.has(item.uuid)) {
                            itemMap.set(item.uuid, item);
                        }
                    });

                    const merged = Array.from(itemMap.values());
                    merged.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    resolve(merged);
                };
                req.onerror = () => {
                    resolve(backupItems);
                };
            } catch (e) {
                resolve(backupItems);
            }
        });
    }

    async updateOutbox(uuid, updates) {
        // Update in localStorage backup
        const backupItems = this._getLocalStorageBackup();
        const target = backupItems.find(i => i.uuid === uuid);
        if (target) {
            Object.assign(target, updates);
            try {
                localStorage.setItem(`pwa_outbox_backup_${this.userId}`, JSON.stringify(backupItems));
            } catch (e) {}
        }

        return new Promise((resolve) => {
            try {
                const store = this._tx('outbox', 'readwrite');
                const getReq = store.get(uuid);
                getReq.onsuccess = () => {
                    const item = getReq.result || target;
                    if (!item) return resolve(null);
                    Object.assign(item, updates);
                    const putReq = store.put(item);
                    putReq.onsuccess = () => resolve(item);
                    putReq.onerror = () => resolve(item);
                };
                getReq.onerror = () => resolve(target);
            } catch (e) {
                resolve(target);
            }
        });
    }

    async removeOutbox(uuid) {
        this._removeFromLocalStorage(uuid);

        return new Promise((resolve) => {
            try {
                const store = this._tx('outbox', 'readwrite');
                const req = store.delete(uuid);
                req.onsuccess = () => resolve(true);
                req.onerror = () => resolve(true);
            } catch (e) {
                resolve(true);
            }
        });
    }

    async clearOutbox() {
        try {
            localStorage.removeItem(`pwa_outbox_backup_${this.userId}`);
        } catch (e) {}

        return new Promise((resolve) => {
            try {
                const store = this._tx('outbox', 'readwrite');
                const req = store.clear();
                req.onsuccess = () => resolve(true);
                req.onerror = () => resolve(true);
            } catch (e) {
                resolve(true);
            }
        });
    }

    // Cached Records Operations
    async setCachedRecord(entity, id, data) {
        const cacheKey = `${entity}_${id}`;
        const record = {
            cache_key: cacheKey,
            entity,
            record_id: id,
            data,
            updated_at: new Date().toISOString(),
        };

        return new Promise((resolve, reject) => {
            const store = this._tx('cached_records', 'readwrite');
            const req = store.put(record);
            req.onsuccess = () => resolve(record);
            req.onerror = () => reject(req.error);
        });
    }

    async getCachedRecord(entity, id) {
        const cacheKey = `${entity}_${id}`;
        return new Promise((resolve, reject) => {
            const store = this._tx('cached_records', 'readonly');
            const req = store.get(cacheKey);
            req.onsuccess = () => resolve(req.result ? req.result.data : null);
            req.onerror = () => reject(req.error);
        });
    }

    async getAllCachedRecords(entity) {
        return new Promise((resolve, reject) => {
            const store = this._tx('cached_records', 'readonly');
            const req = store.getAll();
            req.onsuccess = () => {
                const filtered = (req.result || [])
                    .filter(r => r.entity === entity)
                    .map(r => r.data);
                resolve(filtered);
            };
            req.onerror = () => reject(req.error);
        });
    }

    // Metadata
    async setMeta(key, value) {
        return new Promise((resolve, reject) => {
            const store = this._tx('meta', 'readwrite');
            const req = store.put({ key, value, updated_at: new Date().toISOString() });
            req.onsuccess = () => resolve(true);
            req.onerror = () => reject(req.error);
        });
    }

    async getMeta(key) {
        return new Promise((resolve, reject) => {
            const store = this._tx('meta', 'readonly');
            const req = store.get(key);
            req.onsuccess = () => resolve(req.result ? req.result.value : null);
            req.onerror = () => reject(req.error);
        });
    }

    // Purge user's local database completely (e.g. on logout)
    static async purgeUserDatabase(userId) {
        if (!userId) return;
        return new Promise((resolve) => {
            const req = indexedDB.deleteDatabase(`pwa_offline_db_${userId}`);
            req.onsuccess = () => resolve(true);
            req.onerror = () => resolve(false);
            req.onblocked = () => resolve(false);
        });
    }
}

window.PwaDB = new PwaDB();

