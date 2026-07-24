import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface SiteRow {
    id: number;
    code: string;
    name: string;
    location?: string;
    is_active: boolean;
}

interface SitesIndexProps {
    sites: SiteRow[];
}

export default function Index({ sites }: SitesIndexProps) {
    const columns: ProColumns<SiteRow>[] = [
        { title: 'Kode', dataIndex: 'code', key: 'code' },
        { title: 'Nama', dataIndex: 'name', key: 'name' },
        { title: 'Lokasi', dataIndex: 'location', key: 'location', ellipsis: true },
        {
            title: 'Status',
            dataIndex: 'is_active',
            key: 'is_active',
            render: (_, record) => (
                <Tag color={record.is_active ? 'green' : 'default'}>
                    {record.is_active ? 'Aktif' : 'Nonaktif'}
                </Tag>
            ),
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('sites.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus site ini?"
                        onConfirm={() => router.delete(route('sites.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Master Site">
            <Head title="Sites" />
            <DataTable<SiteRow>
                headerTitle="Daftar Site"
                dataSource={sites}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('sites.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah Site
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
