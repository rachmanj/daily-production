import type { EntryStatus } from '@/types/daily-entry';
import { Tag } from 'antd';

const STATUS_CONFIG: Record<EntryStatus, { color: string; label: string }> = {
    draft: { color: 'default', label: 'Draf' },
    submitted: { color: 'processing', label: 'Disubmit' },
    approved: { color: 'success', label: 'Disetujui' },
};

interface StatusBadgeProps {
    status: EntryStatus | string;
}

export default function StatusBadge({ status }: StatusBadgeProps) {
    const config = STATUS_CONFIG[status as EntryStatus] ?? {
        color: 'default',
        label: status,
    };

    return <Tag color={config.color}>{config.label}</Tag>;
}
