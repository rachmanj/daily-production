import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface FuelPriceRow {
    id: number;
    fuel_type_id: number;
    fuel_type_name: string;
    price_per_liter: string;
    effective_date: string;
}

interface FuelPricesIndexProps {
    fuelPrices: FuelPriceRow[];
}

export default function Index({ fuelPrices }: FuelPricesIndexProps) {
    const columns: ProColumns<FuelPriceRow>[] = [
        { title: 'Jenis BBM', dataIndex: 'fuel_type_name', key: 'fuel_type_name' },
        {
            title: 'Harga/Liter (IDR)',
            dataIndex: 'price_per_liter',
            key: 'price_per_liter',
            render: (_, record) =>
                Number(record.price_per_liter).toLocaleString('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                }),
        },
        { title: 'Tanggal Efektif', dataIndex: 'effective_date', key: 'effective_date', valueType: 'date' },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('fuel-prices.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus harga ini?"
                        onConfirm={() => router.delete(route('fuel-prices.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Harga Bahan Bakar">
            <Head title="Fuel Prices" />
            <DataTable<FuelPriceRow>
                headerTitle="Daftar Harga Bahan Bakar"
                dataSource={fuelPrices}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('fuel-prices.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah Harga
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
