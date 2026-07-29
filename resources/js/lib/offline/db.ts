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

export interface DraftHourly {
    uuid: string;
    daily_entry_id?: number;
    site_id: number;
    production_date: string;
    material_type: string;
    shift_id: number;
    records: Array<{
        equipment_id: number;
        hour_slot: number;
        tonnage: number;
        unit_code?: string;
    }>;
    updated_at: string;
}

export interface SyncQueueItem {
    id: string;
    uuid: string;
    payload: DraftEntry | DraftHourly;
    type: 'daily' | 'hourly';
    created_at: string;
    attempts: number;
}

interface MineOpsDB extends DBSchema {
    draftEntries: {
        key: string;
        value: DraftEntry;
        indexes: { 'by-date': string };
    };
    draftHourly: {
        key: string;
        value: DraftHourly;
        indexes: { 'by-date': string };
    };
    syncQueue: {
        key: string;
        value: SyncQueueItem;
        indexes: { 'by-created': string };
    };
}

const DB_NAME = 'arka-mineops';
const DB_VERSION = 2;

let dbPromise: Promise<IDBPDatabase<MineOpsDB>> | null = null;

function getDb(): Promise<IDBPDatabase<MineOpsDB>> {
    if (!dbPromise) {
        dbPromise = openDB<MineOpsDB>(DB_NAME, DB_VERSION, {
            upgrade(db, oldVersion) {
                if (oldVersion < 1) {
                    const drafts = db.createObjectStore('draftEntries', { keyPath: 'uuid' });
                    drafts.createIndex('by-date', 'production_date');

                    const queue = db.createObjectStore('syncQueue', { keyPath: 'id' });
                    queue.createIndex('by-created', 'created_at');
                }

                if (oldVersion < 2) {
                    const hourly = db.createObjectStore('draftHourly', { keyPath: 'uuid' });
                    hourly.createIndex('by-date', 'production_date');
                }
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

export async function saveDraftHourly(entry: DraftHourly): Promise<void> {
    const db = await getDb();
    await db.put('draftHourly', { ...entry, updated_at: new Date().toISOString() });
}

export async function getDraftHourly(uuid: string): Promise<DraftHourly | undefined> {
    const db = await getDb();
    return db.get('draftHourly', uuid);
}

export async function getAllDraftHourly(): Promise<DraftHourly[]> {
    const db = await getDb();
    return db.getAll('draftHourly');
}

export async function deleteDraftHourly(uuid: string): Promise<void> {
    const db = await getDb();
    await db.delete('draftHourly', uuid);
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
