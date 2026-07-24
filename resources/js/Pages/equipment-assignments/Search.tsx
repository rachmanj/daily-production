import { router } from '@inertiajs/react';
import { Button, Drawer, Input, Select, Space, Table, message } from 'antd';
import axios from 'axios';
import { useCallback, useEffect, useState } from 'react';

interface EquipmentResult {
    id: number;
    unit_code: string;
    description?: string;
    plant_type_name?: string;
    project_code: string;
}

interface EquipmentSearchDrawerProps {
    open: boolean;
    onClose: () => void;
    pits: { id: number; code: string; site_id: number }[];
    plantTypes: string[];
    siteId: number;
    projectCode: string;
}

export default function EquipmentSearchDrawer({
    open,
    onClose,
    pits,
    plantTypes,
    siteId,
    projectCode,
}: EquipmentSearchDrawerProps) {
    const [search, setSearch] = useState('');
    const [plantType, setPlantType] = useState<string | undefined>();
    const [results, setResults] = useState<EquipmentResult[]>([]);
    const [loading, setLoading] = useState(false);
    const [selected, setSelected] = useState<EquipmentResult[]>([]);
    const [pitId, setPitId] = useState<number | undefined>(pits[0]?.id);
    const [submitting, setSubmitting] = useState(false);

    const fetchResults = useCallback(async () => {
        setLoading(true);
        try {
            const response = await axios.get(route('equipment-assignments.search'), {
                params: {
                    search: search || undefined,
                    plant_type: plantType,
                    project_code: projectCode,
                    is_active: 1,
                },
            });
            setResults(response.data.data ?? []);
        } catch {
            message.error('Gagal mencari equipment dari API.');
            setResults([]);
        } finally {
            setLoading(false);
        }
    }, [search, plantType, projectCode]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const timer = setTimeout(() => {
            fetchResults();
        }, 400);

        return () => clearTimeout(timer);
    }, [open, fetchResults]);

    useEffect(() => {
        if (open) {
            setPitId(pits[0]?.id);
            setSelected([]);
        }
    }, [open, pits]);

    const handleAssign = () => {
        if (!pitId || selected.length === 0) {
            message.warning('Pilih equipment dan PIT terlebih dahulu.');
            return;
        }

        setSubmitting(true);
        router.post(
            route('equipment-assignments.store'),
            {
                site_id: siteId,
                pit_id: pitId,
                equipment: selected.map((item) => ({
                    equipment_id: item.id,
                    unit_code: item.unit_code,
                    description: item.description,
                    plant_type_name: item.plant_type_name,
                    project_code: item.project_code,
                })),
            },
            {
                onSuccess: () => {
                    message.success('Equipment berhasil di-assign.');
                    onClose();
                },
                onFinish: () => setSubmitting(false),
            },
        );
    };

    return (
        <Drawer
            title="Assign Equipment dari ArkFleet"
            width={720}
            open={open}
            onClose={onClose}
            footer={
                <Space style={{ float: 'right' }}>
                    <Button onClick={onClose}>Batal</Button>
                    <Button type="primary" loading={submitting} onClick={handleAssign}>
                        Assign Selected ({selected.length})
                    </Button>
                </Space>
            }
        >
            <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <Space wrap>
                    <Input.Search
                        placeholder="Cari kode unit..."
                        allowClear
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        style={{ width: 240 }}
                    />
                    <Select
                        allowClear
                        placeholder="Tipe Plant"
                        style={{ width: 160 }}
                        value={plantType}
                        onChange={setPlantType}
                        options={plantTypes.map((t) => ({ value: t, label: t }))}
                    />
                    <Select
                        placeholder="Pilih PIT"
                        style={{ width: 160 }}
                        value={pitId}
                        onChange={setPitId}
                        options={pits.map((p) => ({ value: p.id, label: p.code }))}
                    />
                </Space>
                <Table<EquipmentResult>
                    rowKey="id"
                    loading={loading}
                    dataSource={results}
                    rowSelection={{
                        selectedRowKeys: selected.map((s) => s.id),
                        onChange: (_, rows) => setSelected(rows),
                    }}
                    columns={[
                        { title: 'Kode', dataIndex: 'unit_code', key: 'unit_code' },
                        { title: 'Deskripsi', dataIndex: 'description', key: 'description', ellipsis: true },
                        { title: 'Tipe', dataIndex: 'plant_type_name', key: 'plant_type_name' },
                        { title: 'Project', dataIndex: 'project_code', key: 'project_code' },
                    ]}
                    size="small"
                    pagination={{ pageSize: 10 }}
                />
            </Space>
        </Drawer>
    );
}
