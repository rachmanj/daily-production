import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Space } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface ShiftRow {
    id: number;
    name: string;
    name_label: string;
    start_time: string;
    end_time: string;
}

interface ShiftsIndexProps {
    shifts: ShiftRow[];
}

export default function Index({ shifts }: ShiftsIndexProps) {
    const columns: ProColumns<ShiftRow>[] = [
        { title: 'Nama Shift', dataIndex: 'name_label', key: 'name_label' },
        { title: 'Mulai', dataIndex: 'start_time', key: 'start_time' },
        { title: 'Selesai', dataIndex: 'end_time', key: 'end_time' },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('shifts.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus shift ini?"
                        onConfirm={() => router.delete(route('shifts.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Master Shift">
            <Head title="Shifts" />
            <DataTable<ShiftRow>
                headerTitle="Daftar Shift"
                dataSource={shifts}
                columns={columns}
                toolBarRender={() => [
                    <Link key="create" href={route('shifts.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah Shift
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
