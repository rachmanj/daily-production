import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface PlanRow {
    id: number;
    year: number;
    month: number;
    site?: { id: number; code: string; name: string };
}

interface PaginatedPlans {
    data: PlanRow[];
    current_page: number;
    total: number;
    per_page: number;
}

interface MonthlyPlansIndexProps {
    plans: PaginatedPlans;
}

const MONTHS = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
];

export default function Index({ plans }: MonthlyPlansIndexProps) {
    const columns: ProColumns<PlanRow>[] = [
        {
            title: 'Periode',
            key: 'period',
            render: (_, record) => `${MONTHS[record.month - 1]} ${record.year}`,
        },
        {
            title: 'Site',
            key: 'site',
            render: (_, record) =>
                record.site ? `${record.site.code} — ${record.site.name}` : '—',
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('monthly-plans.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus plan ini?"
                        onConfirm={() => router.delete(route('monthly-plans.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Monthly Plan">
            <Head title="Monthly Plan" />
            <DataTable<PlanRow>
                headerTitle="Daftar Plan Bulanan"
                dataSource={plans.data}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('monthly-plans.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Plan Baru
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
