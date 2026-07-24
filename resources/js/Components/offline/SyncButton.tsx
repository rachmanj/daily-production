import { flushSyncQueue, pendingCount } from '@/lib/offline/syncQueue';
import { useOnlineStatus } from '@/lib/offline/useOnlineStatus';
import { SyncOutlined } from '@ant-design/icons';
import { Button, message } from 'antd';
import { useEffect, useState } from 'react';

export default function SyncButton() {
    const online = useOnlineStatus();
    const [pending, setPending] = useState(0);
    const [syncing, setSyncing] = useState(false);

    const refreshCount = async () => {
        setPending(await pendingCount());
    };

    useEffect(() => {
        refreshCount();
    }, []);

    const handleSync = async () => {
        if (!online) {
            message.warning('Tidak ada koneksi internet.');
            return;
        }

        setSyncing(true);
        try {
            const result = await flushSyncQueue();
            await refreshCount();
            if (result.synced > 0) {
                message.success(`${result.synced} entri berhasil disinkronkan.`);
            } else if (result.failed > 0) {
                message.error('Sinkronisasi gagal. Coba lagi nanti.');
            } else {
                message.info('Tidak ada data yang perlu disinkronkan.');
            }
        } finally {
            setSyncing(false);
        }
    };

    if (pending === 0) {
        return null;
    }

    return (
        <Button
            type="default"
            icon={<SyncOutlined spin={syncing} />}
            loading={syncing}
            disabled={!online}
            onClick={handleSync}
        >
            Sync ({pending})
        </Button>
    );
}
