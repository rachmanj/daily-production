import axios from 'axios';
import {
    deleteDraft,
    enqueueSync,
    getPendingSyncCount,
    getSyncQueue,
    removeSyncItem,
    type DraftEntry,
    type SyncQueueItem,
} from './db';

function generateId(): string {
    return crypto.randomUUID();
}

export async function queueDraftForSync(entry: DraftEntry): Promise<string> {
    const id = generateId();
    const item: SyncQueueItem = {
        id,
        uuid: entry.uuid,
        payload: entry,
        created_at: new Date().toISOString(),
        attempts: 0,
    };
    await enqueueSync(item);
    return id;
}

export async function flushSyncQueue(): Promise<{ synced: number; failed: number }> {
    const queue = await getSyncQueue();
    let synced = 0;
    let failed = 0;

    if (queue.length === 0) {
        return { synced, failed };
    }

    try {
        const response = await axios.post(
            '/api/sync/daily-entries',
            {
                entries: queue.map((item) => ({
                    uuid: item.payload.uuid,
                    production_date: item.payload.production_date,
                    site_id: item.payload.site_id,
                    production: item.payload.production,
                    fuel: item.payload.fuel,
                    deployments: item.payload.deployments,
                    site_info: item.payload.site_info,
                })),
            },
            { withCredentials: true },
        );

        const results: { uuid: string; synced: boolean }[] = response.data.results ?? [];

        for (const item of queue) {
            const result = results.find((r) => r.uuid === item.uuid);
            if (result?.synced) {
                await removeSyncItem(item.id);
                await deleteDraft(item.uuid);
                synced++;
            } else {
                failed++;
            }
        }
    } catch {
        failed = queue.length;
    }

    return { synced, failed };
}

export async function pendingCount(): Promise<number> {
    return getPendingSyncCount();
}

export { queueDraftForSync as enqueueDraft };
