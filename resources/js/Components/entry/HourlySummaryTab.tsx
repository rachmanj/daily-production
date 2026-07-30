import type { HourlyTotal } from '@/types/daily-entry';
import { getAchievementColor } from '@/types/hourly';
import { Link } from '@inertiajs/react';
import { Button, Empty, Table, Tag } from 'antd';
import { EditOutlined } from '@ant-design/icons';

interface HourlySummaryTabProps {
    totals: HourlyTotal[];
    entryId: number;
}

export default function HourlySummaryTab({ totals, entryId }: HourlySummaryTabProps) {
    if (totals.length === 0) {
        return (
            <Empty
                description="Belum ada data hourly untuk entry ini"
                image={Empty.PRESENTED_IMAGE_SIMPLE}
            >
                <Link href={route('hourly.edit', entryId)}>
                    <Button type="primary" icon={<EditOutlined />}>
                        Buka Hourly Entry
                    </Button>
                </Link>
            </Empty>
        );
    }

    const columns = [
        {
            title: 'Material',
            dataIndex: 'material_label',
            key: 'material_label',
        },
        {
            title: 'Total (Mton)',
            dataIndex: 'total_tonnage',
            key: 'total_tonnage',
            align: 'right' as const,
            render: (value: number) => value.toLocaleString('id-ID', { maximumFractionDigits: 2 }),
        },
        {
            title: 'Jam Terisi',
            key: 'hours_filled',
            align: 'center' as const,
            render: (_: unknown, record: HourlyTotal) => `${record.hours_filled}/24`,
        },
        {
            title: 'Plan DTD',
            dataIndex: 'daily_plan',
            key: 'daily_plan',
            align: 'right' as const,
            render: (value: number | null) =>
                value !== null ? value.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '—',
        },
        {
            title: 'Achievement',
            dataIndex: 'achievement',
            key: 'achievement',
            align: 'center' as const,
            render: (value: number | null) =>
                value !== null ? (
                    <Tag color={getAchievementColor(value)}>{value.toFixed(1)}%</Tag>
                ) : (
                    '—'
                ),
        },
    ];

    return (
        <div>
            <Table
                dataSource={totals}
                columns={columns}
                rowKey="material_type"
                pagination={false}
                size="small"
                style={{ marginBottom: 16 }}
            />
            <Link href={route('hourly.edit', entryId)}>
                <Button icon={<EditOutlined />}>Buka Hourly Entry</Button>
            </Link>
        </div>
    );
}
