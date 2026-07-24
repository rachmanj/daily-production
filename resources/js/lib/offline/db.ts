import { openDB, type DBSchema, type IDBPDatabase } from 'idb';

export interface DraftEntry {
    uuid: string;
    site_id: number;
    production_date: string;
    production?: unknown[];
    fuel?: unknown[];
    deployments?: unknown[];
    site_info?: Record<string, unknown>;
    updated_at: string;
}

export interface SyncQueueItem {
    id: string;
    uuid: string;
    payload: DraftEntry;
    created_at: string;
    attempts: number;
}

interface MineOpsDB extends DBSchema {
    draftEntries: {
        key: string;
        value: DraftEntry;
        indexes: { 'by-date': string };
    };
    syncQueue: {
        key: string;
        value: SyncQueueItem;
        indexes: { 'by-created': string };
    };
}

const DB_NAME = 'arka-mineops';
const DB_VERSION = 1;

let dbPromise: Promise<IDBPDatabase<MineOpsDB>> | null = null;

function getDb(): Promise<IDBPDatabase<MineOpsDB>> {
    if (!dbPromise) {
        dbPromise = openDB<MineOpsDB>(DB_NAME, DB_VERSION, {
            upgrade(db) {
                const drafts = db.createObjectStore('draftEntries', { keyPath: 'uuid' });
                drafts.createIndex('by-date', 'production_date');

                const queue = db.createObjectStore('syncQueue', { keyPath: 'id' });
                queue.createIndex('by-created', 'created_at');
            },
        });
    }

    return dbPromise;
}

export async function saveDraft(entry: DraftEntry): Promise<void> {
    const db = await getDb();
    await db.put('draftEntries', { ...entry, updated_at: new Date().toISOString() });
}

export async function getDraft(uuid: string): Promise<DraftEntry | undefined> {
    const db = await getDb();
    return db.get('draftEntries', uuid);
}

export async function getAllDrafts(): Promise<DraftEntry[]> {
    const db = await getDb();
    return db.getAll('draftEntries');
}

export async function deleteDraft(uuid: string): Promise<void> {
    const db = await getDb();
    await db.delete('draftEntries', uuid);
}

export async function enqueueSync(item: SyncQueueItem): Promise<void> {
    const db = await getDb();
    await db.put('syncQueue', item);
}

export async function getSyncQueue(): Promise<SyncQueueItem[]> {
    const db = await getDb();
    const items = await db.getAll('syncQueue');
    return items.sort((a, b) => a.created_at.localeCompare(b.created_at));
}

export async function removeSyncItem(id: string): Promise<void> {
    const db = await getDb();
    await db.delete('syncQueue', id);
}

export async function getPendingSyncCount(): Promise<number> {
    const db = await getDb();
    return db.count('syncQueue');
}
