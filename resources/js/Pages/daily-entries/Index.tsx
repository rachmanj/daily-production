import DataTable from '@/Components/DataTable';
import StatusBadge from '@/Components/entry/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dayjs, formatDate } from '@/lib/date';
import type { Site } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button, DatePicker, Dropdown, Grid, Popconfirm, Select, Space, Typography } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import {
    DeleteOutlined,
    EditOutlined,
    EyeOutlined,
    FilterOutlined,
    MoreOutlined,
    PlusOutlined,
    UploadOutlined,
} from '@ant-design/icons';
import { useState } from 'react';

const { useBreakpoint } = Grid;
const { Text } = Typography;

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

interface DailyEntriesIndexProps {
    entries: PaginatedEntries;
    filters: {
        site_id?: number;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
    sites: Site[];
    statuses: Record<string, string>;
}

export default function Index({ entries, filters, sites, statuses }: DailyEntriesIndexProps) {
    const screens = useBreakpoint();
    const isMobile = !screens.md;
    const [filterOpen, setFilterOpen] = useState(false);

    const applyFilters = (patch: Record<string, string | number | undefined>) => {
        router.get(route('daily-entries.index'), { ...filters, ...patch }, { preserveState: true });
    };

    const hasFilters = filters.site_id || filters.status || filters.date_from || filters.date_to;

    const columns: ProColumns<EntryRow>[] = [
        {
            title: 'Tanggal',
            dataIndex: 'production_date',
            key: 'production_date',
            width: isMobile ? 100 : undefined,
            render: (_, record) => formatDate(record.production_date),
        },
        {
            title: 'Site',
            key: 'site',
            responsive: ['md'] as any,
            render: (_, record) => record.site ? `${record.site.code}` : '—',
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            width: 90,
            render: (_, record) => <StatusBadge status={record.status} locale="en" />,
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            width: isMobile ? 50 : 120,
            render: (_, record) =>
                isMobile ? (
                    <Dropdown
                        menu={{
                            items: [
                                {
                                    key: 'view',
                                    icon: <EyeOutlined />,
                                    label: 'Lihat',
                                    onClick: () => router.get(route('daily-entries.show', record.id)),
                                },
                                {
                                    key: 'edit',
                                    icon: <EditOutlined />,
                                    label: 'Edit',
                                    onClick: () => router.get(route('daily-entries.edit', record.id)),
                                },
                                { type: 'divider' },
                                {
                                    key: 'delete',
                                    icon: <DeleteOutlined />,
                                    label: 'Hapus',
                                    danger: true,
                                    onClick: () => {
                                        if (confirm('Hapus entry ini?')) {
                                            router.delete(route('daily-entries.destroy', record.id));
                                        }
                                    },
                                },
                            ],
                        }}
                        trigger={['click']}
                    >
                        <Button type="text" size="small" icon={<MoreOutlined />} />
                    </Dropdown>
                ) : (
                    <Space size="small">
                        <Link href={route('daily-entries.show', record.id)}>
                            <Button type="link" size="small" icon={<EyeOutlined />} />
                        </Link>
                        <Link href={route('daily-entries.edit', record.id)}>
                            <Button type="link" size="small" icon={<EditOutlined />} />
                        </Link>
                        <Popconfirm
                            title="Hapus entry ini?"
                            onConfirm={() => router.delete(route('daily-entries.destroy', record.id))}
                        >
                            <Button type="link" size="small" danger icon={<DeleteOutlined />} />
                        </Popconfirm>
                    </Space>
                ),
        },
    ];

    const filterBar = (
        <Space wrap size="small" style={{ width: '100%', marginBottom: 16 }}>
            <Select
                allowClear
                placeholder="Site"
                style={{ width: isMobile ? '100%' : 200 }}
                value={filters.site_id}
                onChange={(v) => applyFilters({ site_id: v })}
                options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
            />
            <Select
                allowClear
                placeholder="Status"
                style={{ width: isMobile ? '100%' : 160 }}
                value={filters.status}
                onChange={(v) => applyFilters({ status: v })}
                options={Object.keys(statuses).map((value) => ({
                    value,
                    label: STATUS_LABELS_EN[value] ?? value,
                }))}
            />
            <DatePicker
                placeholder="Dari"
                style={{ width: isMobile ? '100%' : undefined }}
                value={filters.date_from ? dayjs(filters.date_from) : null}
                onChange={(d) => applyFilters({ date_from: d?.format('YYYY-MM-DD') })}
            />
            <DatePicker
                placeholder="Sampai"
                style={{ width: isMobile ? '100%' : undefined }}
                value={filters.date_to ? dayjs(filters.date_to) : null}
                onChange={(d) => applyFilters({ date_to: d?.format('YYYY-MM-DD') })}
            />
        </Space>
    );

    return (
        <AuthenticatedLayout title="Daily Entry">
            <Head title="Daily Entry" />

            {isMobile ? (
                <>
                    <Button
                        icon={<FilterOutlined />}
                        type={hasFilters ? 'primary' : 'default'}
                        onClick={() => setFilterOpen(!filterOpen)}
                        block
                        style={{ marginBottom: 12 }}
                    >
                        Filter {hasFilters ? '•' : ''}
                    </Button>
                    {filterOpen && filterBar}
                </>
            ) : (
                filterBar
            )}

            <DataTable<EntryRow>
                headerTitle="Entry Harian"
                dataSource={entries.data}
                columns={columns}
                pagination={
                    entries.total > entries.per_page
                        ? {
                              current: entries.current_page,
                              total: entries.total,
                              pageSize: entries.per_page,
                              size: isMobile ? 'small' : 'default',
                              onChange: (page) =>
                                  router.get(route('daily-entries.index'), { ...filters, page }),
                          }
                        : false
                }
                toolBarRender={() => [
                    <Link key="import" href={route('excel-imports.create')}>
                        <Button icon={<UploadOutlined />} size={isMobile ? 'middle' : undefined}>
                            {isMobile ? '' : 'Import Excel'}
                        </Button>
                    </Link>,
                    <Link key="create" href={route('daily-entries.create')}>
                        <Button type="primary" icon={<PlusOutlined />} size={isMobile ? 'middle' : undefined}>
                            {isMobile ? 'Baru' : 'Entry Baru'}
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
