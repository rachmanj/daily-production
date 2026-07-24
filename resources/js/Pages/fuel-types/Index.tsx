import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface FuelTypeRow {
    id: number;
    name: string;
    is_active: boolean;
}

interface FuelTypesIndexProps {
    fuelTypes: FuelTypeRow[];
}

export default function Index({ fuelTypes }: FuelTypesIndexProps) {
    const columns: ProColumns<FuelTypeRow>[] = [
        { title: 'Nama', dataIndex: 'name', key: 'name' },
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
                    <Link href={route('fuel-types.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus jenis BBM ini?"
                        onConfirm={() => router.delete(route('fuel-types.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Jenis Bahan Bakar">
            <Head title="Fuel Types" />
            <DataTable<FuelTypeRow>
                headerTitle="Daftar Jenis Bahan Bakar"
                dataSource={fuelTypes}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('fuel-types.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah Jenis BBM
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
