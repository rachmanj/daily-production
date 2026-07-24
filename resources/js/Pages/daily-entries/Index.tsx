import DataTable from '@/Components/DataTable';
import StatusBadge from '@/Components/entry/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Button, DatePicker, Popconfirm, Select, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, EyeOutlined, PlusOutlined, UploadOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';

interface EntryRow {
    id: number;
    production_date: string;
    status: string;
    site?: { id: number; code: string; name: string };
    creator?: { id: number; name: string };
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
    const columns: ProColumns<EntryRow>[] = [
        {
            title: 'Tanggal',
            dataIndex: 'production_date',
            key: 'production_date',
            render: (_, record) => dayjs(record.production_date).format('DD MMM YYYY'),
        },
        {
            title: 'Site',
            key: 'site',
            render: (_, record) => record.site ? `${record.site.code} — ${record.site.name}` : '—',
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            render: (_, record) => <StatusBadge status={record.status} />,
        },
        {
            title: 'Dibuat Oleh',
            key: 'creator',
            render: (_, record) => record.creator?.name ?? '—',
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('daily-entries.show', record.id)}>
                        <Button type="link" icon={<EyeOutlined />} />
                    </Link>
                    <Link href={route('daily-entries.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus entry ini?"
                        onConfirm={() => router.delete(route('daily-entries.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    const applyFilters = (patch: Record<string, string | number | undefined>) => {
        router.get(route('daily-entries.index'), { ...filters, ...patch }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Daily Entry">
            <Head title="Daily Entry" />
            <Space wrap style={{ marginBottom: 16 }}>
                <Select
                    allowClear
                    placeholder="Site"
                    style={{ width: 200 }}
                    value={filters.site_id}
                    onChange={(v) => applyFilters({ site_id: v })}
                    options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                />
                <Select
                    allowClear
                    placeholder="Status"
                    style={{ width: 160 }}
                    value={filters.status}
                    onChange={(v) => applyFilters({ status: v })}
                    options={Object.entries(statuses).map(([value, label]) => ({ value, label }))}
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
            </Space>
            <DataTable<EntryRow>
                headerTitle="Daftar Entry Harian"
                dataSource={entries.data}
                columns={columns}
                pagination={{
                    current: entries.current_page,
                    total: entries.total,
                    pageSize: entries.per_page,
                    onChange: (page) => router.get(route('daily-entries.index'), { ...filters, page }),
                }}
                toolBarRender={() => [
                    <Link key="import" href={route('excel-imports.create')}>
                        <Button icon={<UploadOutlined />}>Import Excel</Button>
                    </Link>,
                    <Link key="create" href={route('daily-entries.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Entry Baru
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
