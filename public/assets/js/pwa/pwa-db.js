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

        return new Promise((resolve, reject) => {
            const store = this._tx('outbox', 'readwrite');
            const req = store.put(record);
            req.onsuccess = () => resolve(record);
            req.onerror = () => reject(req.error);
        });
    }

    async getPendingOutbox() {
        return new Promise((resolve, reject) => {
            const store = this._tx('outbox', 'readonly');
            const req = store.getAll();
            req.onsuccess = () => {
                const pending = (req.result || []).filter(item => item.status === 'pending' || item.status === 'failed');
                // Sort by created_at ascending to maintain execution order
                pending.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                resolve(pending);
            };
            req.onerror = () => reject(req.error);
        });
    }

    async updateOutbox(uuid, updates) {
        return new Promise((resolve, reject) => {
            const store = this._tx('outbox', 'readwrite');
            const getReq = store.get(uuid);
            getReq.onsuccess = () => {
                const item = getReq.result;
                if (!item) return resolve(null);
                Object.assign(item, updates);
                const putReq = store.put(item);
                putReq.onsuccess = () => resolve(item);
                putReq.onerror = () => reject(putReq.error);
            };
            getReq.onerror = () => reject(getReq.error);
        });
    }

    async removeOutbox(uuid) {
        return new Promise((resolve, reject) => {
            const store = this._tx('outbox', 'readwrite');
            const req = store.delete(uuid);
            req.onsuccess = () => resolve(true);
            req.onerror = () => reject(req.error);
        });
    }

    async clearOutbox() {
        return new Promise((resolve, reject) => {
            const store = this._tx('outbox', 'readwrite');
            const req = store.clear();
            req.onsuccess = () => resolve(true);
            req.onerror = () => reject(req.error);
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

