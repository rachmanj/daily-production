import { pendingCount } from '@/lib/offline/syncQueue';
import { useOnlineStatus } from '@/lib/offline/useOnlineStatus';
import { DisconnectOutlined } from '@ant-design/icons';
import { Alert } from 'antd';
import { useEffect, useState } from 'react';

export default function OfflineIndicator() {
    const online = useOnlineStatus();
    const [pending, setPending] = useState(0);

    useEffect(() => {
        const load = async () => {
            setPending(await pendingCount());
        };
        load();
        const interval = setInterval(load, 10000);
        return () => clearInterval(interval);
    }, [online]);

    if (online && pending === 0) {
        return null;
    }

    return (
        <Alert
            type={online ? 'warning' : 'error'}
            showIcon
            icon={<DisconnectOutlined />}
            message={
                online
                    ? `Offline sync: ${pending} entri menunggu sinkronisasi`
                    : `Mode offline — ${pending} entri dalam antrian`
            }
            style={{ marginBottom: 0, borderRadius: 0 }}
        />
    );
}
