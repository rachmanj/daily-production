import { formatDateTime } from '@/lib/date';
import { SyncOutlined } from '@ant-design/icons';
import { Tag, Tooltip } from 'antd';

interface LastSyncedBadgeProps {
    lastSyncedAt?: string | null;
}

export default function LastSyncedBadge({ lastSyncedAt }: LastSyncedBadgeProps) {
    if (!lastSyncedAt) {
        return <Tag color="default">Belum disinkronkan</Tag>;
    }

    const formatted = formatDateTime(lastSyncedAt);

    return (
        <Tooltip title={`Terakhir sync: ${formatted} WITA`}>
            <Tag icon={<SyncOutlined />} color="blue">
                Sync: {formatted}
            </Tag>
        </Tooltip>
    );
}
