import axios from 'axios';
import {
    deleteDraft,
    deleteDraftHourly,
    enqueueSync,
    getPendingSyncCount,
    getSyncQueue,
    removeSyncItem,
    type DraftEntry,
    type DraftHourly,
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
        type: 'daily',
        created_at: new Date().toISOString(),
        attempts: 0,
    };
    await enqueueSync(item);
    return id;
}

export async function queueHourlyDraftForSync(entry: DraftHourly): Promise<string> {
    const id = generateId();
    const item: SyncQueueItem = {
        id,
        uuid: entry.uuid,
        payload: entry,
        type: 'hourly',
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

    const dailyEntries = queue
        .filter((item) => item.type === 'daily')
        .map((item) => {
            const payload = item.payload as DraftEntry;
            return {
                uuid: payload.uuid,
                production_date: payload.production_date,
                site_id: payload.site_id,
                production: payload.production,
                fuel: payload.fuel,
                deployments: payload.deployments,
                site_info: payload.site_info,
            };
        });

    const hourlyEntries = queue
        .filter((item) => item.type === 'hourly')
        .map((item) => {
            const payload = item.payload as DraftHourly;
            return {
                uuid: payload.uuid,
                production_date: payload.production_date,
                site_id: payload.site_id,
                hourly: {
                    material_type: payload.material_type,
                    shift_id: payload.shift_id,
                    records: payload.records,
                },
            };
        });

    const allEntries = [...dailyEntries, ...hourlyEntries];

    try {
        const response = await axios.post(
            '/api/sync/daily-entries',
            { entries: allEntries },
            { withCredentials: true },
        );

        const results: { uuid: string; synced: boolean }[] = response.data.results ?? [];

        for (const item of queue) {
            const result = results.find((r) => r.uuid === item.uuid);
            if (result?.synced) {
                await removeSyncItem(item.id);
                if (item.type === 'hourly') {
                    await deleteDraftHourly(item.uuid);
                } else {
                    await deleteDraft(item.uuid);
                }
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
