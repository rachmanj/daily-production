import ClassifyModal from '@/Pages/equipment-assignments/ClassifyModal';
import EquipmentSearchDrawer from '@/Pages/equipment-assignments/Search';
import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Button, Popconfirm, Space, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
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
    material_type?: string | null;
    equipment_role?: string | null;
    display_order?: number | null;
    is_active_for_tracking: boolean;
}

interface EquipmentAssignmentsIndexProps {
    assignments: AssignmentRow[];
    pits: { id: number; code: string; site_id: number }[];
    plantTypes: string[];
    materialOptions: Record<string, string>;
}

export default function Index({ assignments, pits, plantTypes, materialOptions }: EquipmentAssignmentsIndexProps) {
    const { activeSite } = usePage<PageProps>().props;
    const [drawerOpen, setDrawerOpen] = useState(false);
    const [classifyOpen, setClassifyOpen] = useState(false);
    const [selectedAssignment, setSelectedAssignment] = useState<AssignmentRow | null>(null);

    const openClassify = (record: AssignmentRow) => {
        setSelectedAssignment(record);
        setClassifyOpen(true);
    };

    const columns: ProColumns<AssignmentRow>[] = [
        { title: 'Kode Unit', dataIndex: 'unit_code', key: 'unit_code' },
        { title: 'Deskripsi', dataIndex: 'description', key: 'description', ellipsis: true },
        { title: 'Tipe Plant', dataIndex: 'plant_type_name', key: 'plant_type_name' },
        { title: 'Project', dataIndex: 'project_code', key: 'project_code' },
        { title: 'PIT', dataIndex: 'pit_code', key: 'pit_code' },
        {
            title: 'Material',
            dataIndex: 'material_type',
            key: 'material_type',
            render: (_, record) => (
                <Tag color={record.material_type ? 'blue' : 'default'}>
                    {record.material_type ? (materialOptions[record.material_type] ?? record.material_type) : 'Umum'}
                </Tag>
            ),
        },
        {
            title: 'Role',
            dataIndex: 'equipment_role',
            key: 'equipment_role',
            render: (_, record) => record.equipment_role ?? '—',
        },
        {
            title: 'Urutan',
            dataIndex: 'display_order',
            key: 'display_order',
            render: (_, record) => record.display_order ?? '—',
        },
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
                <Space>
                    <Button
                        type="link"
                        icon={<EditOutlined />}
                        title="Klasifikasi CCR"
                        onClick={() => openClassify(record)}
                    />
                    <Popconfirm
                        title="Hapus assignment ini?"
                        onConfirm={() => router.delete(route('equipment-assignments.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
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
            <ClassifyModal
                open={classifyOpen}
                assignment={selectedAssignment}
                materialOptions={materialOptions}
                onClose={() => {
                    setClassifyOpen(false);
                    setSelectedAssignment(null);
                }}
            />
        </AuthenticatedLayout>
    );
}
