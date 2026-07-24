import EquipmentSearchDrawer from '@/Pages/equipment-assignments/Search';
import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Button, Popconfirm, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons';
import { useState } from 'react';

interface AssignmentRow {
    id: number;
    equipment_id: number;
    unit_code: string;
    description?: string;
    plant_type_name?: string;
    project_code: string;
    pit_code?: string;
    pit_id?: number;
    is_active_for_tracking: boolean;
}

interface EquipmentAssignmentsIndexProps {
    assignments: AssignmentRow[];
    pits: { id: number; code: string; site_id: number }[];
    plantTypes: string[];
}

export default function Index({ assignments, pits, plantTypes }: EquipmentAssignmentsIndexProps) {
    const { activeSite } = usePage<PageProps>().props;
    const [drawerOpen, setDrawerOpen] = useState(false);

    const columns: ProColumns<AssignmentRow>[] = [
        { title: 'Kode Unit', dataIndex: 'unit_code', key: 'unit_code' },
        { title: 'Deskripsi', dataIndex: 'description', key: 'description', ellipsis: true },
        { title: 'Tipe Plant', dataIndex: 'plant_type_name', key: 'plant_type_name' },
        { title: 'Project', dataIndex: 'project_code', key: 'project_code' },
        { title: 'PIT', dataIndex: 'pit_code', key: 'pit_code' },
        {
            title: 'Tracking',
            dataIndex: 'is_active_for_tracking',
            key: 'is_active_for_tracking',
            render: (_, record) => (
                <Tag color={record.is_active_for_tracking ? 'green' : 'default'}>
                    {record.is_active_for_tracking ? 'Aktif' : 'Nonaktif'}
                </Tag>
            ),
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Popconfirm
                    title="Hapus assignment ini?"
                    onConfirm={() => router.delete(route('equipment-assignments.destroy', record.id))}
                >
                    <Button type="link" danger icon={<DeleteOutlined />} />
                </Popconfirm>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Equipment Assignment">
            <Head title="Equipment Assignments" />
            <DataTable<AssignmentRow>
                headerTitle={`Equipment Ter-assign — ${activeSite?.code ?? ''}`}
                dataSource={assignments}
                columns={columns}
                toolBarRender={() => [
                    <Button
                        key="assign"
                        type="primary"
                        icon={<PlusOutlined />}
                        onClick={() => setDrawerOpen(true)}
                    >
                        Assign Equipment
                    </Button>,
                ]}
            />
            <EquipmentSearchDrawer
                open={drawerOpen}
                onClose={() => setDrawerOpen(false)}
                pits={pits}
                plantTypes={plantTypes}
                siteId={activeSite?.id ?? 0}
                projectCode={activeSite?.code ?? '022C'}
            />
        </AuthenticatedLayout>
    );
}
