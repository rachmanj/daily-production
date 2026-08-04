import DataTable from '@/Components/DataTable';
import StatusBadge from '@/Components/entry/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDate } from '@/lib/date';
import type { Site } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button, DatePicker, Select, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { EditOutlined, PlusOutlined } from '@ant-design/icons';
import { dayjs } from '@/lib/date';

const STATUS_LABELS_EN: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Submitted',
    approved: 'Approved',
};

interface EntryRow {
    id: number;
    production_date: string;
    status: string;
    site?: { id: number; code: string; name: string } | null;
    creator?: { id: number; name: string } | null;
}

interface PaginatedEntries {
    data: EntryRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface HourlyIndexProps {
    entries: PaginatedEntries;
    filters: {
        site_id?: number;
        status?: string;
        material_type?: string;
        date_from?: string;
        date_to?: string;
    };
    sites: Site[];
    statuses: Record<string, string>;
    materials: Record<string, string>;
}

export default function Index({ entries, filters, sites, statuses, materials }: HourlyIndexProps) {
    const applyFilters = (patch: Record<string, string | number | undefined>) => {
        router.get(route('hourly.index'), { ...filters, ...patch }, { preserveState: true });
    };

    const columns: ProColumns<EntryRow>[] = [
        {
            title: 'Tanggal',
            dataIndex: 'production_date',
            render: (_, record) => formatDate(record.production_date),
        },
        {
            title: 'Site',
            key: 'site',
            render: (_, record) => record.site?.code ?? '—',
        },
        {
            title: 'Status',
            dataIndex: 'status',
            render: (_, record) => <StatusBadge status={record.status} locale="en" />,
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Link href={route('hourly.edit', { dailyEntry: record.id, material_type: 'limestone', shift_id: 1 })}>
                    <Button type="link" icon={<EditOutlined />} size="small">
                        Edit
                    </Button>
                </Link>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="CCR Hourly Entry">
            <Head title="CCR Hourly" />
            <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <Space wrap>
                    <Select
                        allowClear
                        placeholder="Site"
                        style={{ width: 140 }}
                        value={filters.site_id}
                        onChange={(v) => applyFilters({ site_id: v })}
                        options={sites.map((s) => ({ value: s.id, label: s.code }))}
                    />
                    <Select
                        allowClear
                        placeholder="Material"
                        style={{ width: 160 }}
                        value={filters.material_type}
                        onChange={(v) => applyFilters({ material_type: v })}
                        options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                    />
                    <Select
                        allowClear
                        placeholder="Status"
                        style={{ width: 140 }}
                        value={filters.status}
                        onChange={(v) => applyFilters({ status: v })}
                        options={Object.keys(statuses).map((value) => ({
                            value,
                            label: STATUS_LABELS_EN[value] ?? value,
                        }))}
                    />
                    <DatePicker
                        placeholder="Dari"
                        value={filters.date_from ? dayjs(filters.date_from) : null}
                        onChange={(d) => applyFilters({ date_from: d?.format('YYYY-MM-DD') })}
                    />
                    <DatePicker
                        placeholder="Sampai"
                        value={filters.date_to ? dayjs(filters.date_to) : null}
                        onChange={(d) => applyFilters({ date_to: d?.format('YYYY-MM-DD') })}
                    />
                    <Link href={route('hourly.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Entry Baru
                        </Button>
                    </Link>
                </Space>
                <DataTable
                    columns={columns}
                    dataSource={entries.data}
                    rowKey="id"
                    pagination={{
                        current: entries.current_page,
                        total: entries.total,
                        pageSize: entries.per_page,
                        onChange: (page) => router.get(route('hourly.index'), { ...filters, page }, { preserveState: true }),
                    }}
                />
            </Space>
        </AuthenticatedLayout>
    );
}
