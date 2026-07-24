import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface PitRow {
    id: number;
    site_id: number;
    code: string;
    owner: string;
    owner_label: string;
    is_active: boolean;
    site?: { code: string; name: string };
}

interface PitsIndexProps {
    pits: PitRow[];
}

export default function Index({ pits }: PitsIndexProps) {
    const columns: ProColumns<PitRow>[] = [
        { title: 'Kode PIT', dataIndex: 'code', key: 'code' },
        { title: 'Owner', dataIndex: 'owner_label', key: 'owner_label' },
        {
            title: 'Site',
            key: 'site',
            render: (_, record) =>
                record.site ? `${record.site.code} — ${record.site.name}` : '-',
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('pits.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus PIT ini?"
                        onConfirm={() => router.delete(route('pits.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Master PIT">
            <Head title="PITs" />
            <DataTable<PitRow>
                headerTitle="Daftar PIT (Site Aktif)"
                dataSource={pits}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('pits.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah PIT
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
