import type { EntryStatus } from '@/types/daily-entry';
import { Tag } from 'antd';

const STATUS_CONFIG_ID: Record<EntryStatus, { color: string; label: string }> = {
    draft: { color: 'default', label: 'Draf' },
    submitted: { color: 'processing', label: 'Disubmit' },
    approved: { color: 'success', label: 'Disetujui' },
};

const STATUS_CONFIG_EN: Record<EntryStatus, { color: string; label: string }> = {
    draft: { color: 'default', label: 'Draft' },
    submitted: { color: 'processing', label: 'Submitted' },
    approved: { color: 'success', label: 'Approved' },
};

interface StatusBadgeProps {
    status: EntryStatus | string;
    locale?: 'id' | 'en';
}

export default function StatusBadge({ status, locale = 'id' }: StatusBadgeProps) {
    const configs = locale === 'en' ? STATUS_CONFIG_EN : STATUS_CONFIG_ID;
    const config = configs[status as EntryStatus] ?? {
        color: 'default',
        label: status,
    };

    return <Tag color={config.color}>{config.label}</Tag>;
}
