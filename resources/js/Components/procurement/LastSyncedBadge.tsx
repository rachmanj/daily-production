import { SyncOutlined } from '@ant-design/icons';
import { Tag, Tooltip } from 'antd';
import dayjs from 'dayjs';

interface LastSyncedBadgeProps {
    lastSyncedAt?: string | null;
}

export default function LastSyncedBadge({ lastSyncedAt }: LastSyncedBadgeProps) {
    if (!lastSyncedAt) {
        return <Tag color="default">Belum disinkronkan</Tag>;
    }

    const formatted = dayjs(lastSyncedAt).format('DD MMM YYYY HH:mm');

    return (
        <Tooltip title={`Terakhir sync: ${formatted} WITA`}>
            <Tag icon={<SyncOutlined />} color="blue">
                Sync: {formatted}
            </Tag>
        </Tooltip>
    );
}
