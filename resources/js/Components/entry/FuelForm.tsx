import EquipmentPicker from '@/Components/entry/EquipmentPicker';
import type { EquipmentAssignment, FuelRecord, FuelTypeOption, ShiftOption } from '@/types/daily-entry';
import { Button, Card, InputNumber, Select, Space, Table } from 'antd';
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons';

export interface FuelRow {
    equipment_id: number;
    unit_code: string;
    shift_id: number;
    fuel_type_id: number | null;
    liters: number;
    working_hours: number | null;
    usage_category: string;
}

interface FuelFormProps {
    records: FuelRecord[];
    shifts: ShiftOption[];
    fuelTypes: FuelTypeOption[];
    fuelCategories: Record<string, string>;
    equipmentAssignments: EquipmentAssignment[];
    projectCode?: string;
    onChange: (records: FuelRow[]) => void;
    disabled?: boolean;
}

function toRows(records: FuelRecord[]): FuelRow[] {
    return records.map((r) => ({
        equipment_id: r.equipment_id,
        unit_code: r.unit_code,
        shift_id: r.shift_id,
        fuel_type_id: r.fuel_type_id ?? null,
        liters: r.liters ?? 0,
        working_hours: r.working_hours ?? null,
        usage_category: r.usage_category,
    }));
}

export default function FuelForm({
    records,
    shifts,
    fuelTypes,
    fuelCategories,
    equipmentAssignments,
    projectCode,
    onChange,
    disabled,
}: FuelFormProps) {
    const rows = toRows(records);

    const updateRow = (index: number, patch: Partial<FuelRow>) => {
        const next = [...rows];
        next[index] = { ...next[index], ...patch };
        onChange(next);
    };

    const addRow = () => {
        const firstAssignment = equipmentAssignments[0];
        onChange([
            ...rows,
            {
                equipment_id: firstAssignment?.equipment_id ?? 0,
                unit_code: firstAssignment?.unit_code ?? '',
                shift_id: shifts[0]?.id ?? 0,
                fuel_type_id: fuelTypes[0]?.id ?? null,
                liters: 0,
                working_hours: null,
                usage_category: Object.keys(fuelCategories)[0] ?? 'general',
            },
        ]);
    };

    const removeRow = (index: number) => {
        onChange(rows.filter((_, i) => i !== index));
    };

    return (
        <Card
            title="Data Fuel"
            size="small"
            extra={
                !disabled && (
                    <Button type="dashed" icon={<PlusOutlined />} onClick={addRow}>
                        Tambah Baris
                    </Button>
                )
            }
        >
            <Table
                dataSource={rows.map((row, index) => ({ ...row, key: index, index }))}
                pagination={false}
                size="small"
                scroll={{ x: 1000 }}
                columns={[
                    {
                        title: 'Equipment',
                        key: 'equipment',
                        width: 200,
                        render: (_, record) => (
                            <Select
                                showSearch
                                disabled={disabled}
                                value={record.equipment_id || undefined}
                                style={{ width: '100%' }}
                                optionFilterProp="label"
                                onChange={(id) => {
                                    const eq = equipmentAssignments.find((a) => a.equipment_id === id);
                                    updateRow(record.index, {
                                        equipment_id: id,
                                        unit_code: eq?.unit_code ?? '',
                                    });
                                }}
                                options={equipmentAssignments.map((a) => ({
                                    value: a.equipment_id,
                                    label: a.unit_code,
                                }))}
                            />
                        ),
                    },
                    {
                        title: 'ArkFleet Search',
                        key: 'search',
                        width: 200,
                        render: (_, record) => (
                            <EquipmentPicker
                                projectCode={projectCode}
                                disabled={disabled}
                                value={record.equipment_id || undefined}
                                onChange={(id, item) => {
                                    if (id && item) {
                                        updateRow(record.index, {
                                            equipment_id: id,
                                            unit_code: item.unit_code,
                                        });
                                    }
                                }}
                            />
                        ),
                    },
                    {
                        title: 'Shift',
                        key: 'shift',
                        width: 120,
                        render: (_, record) => (
                            <Select
                                disabled={disabled}
                                value={record.shift_id}
                                onChange={(v) => updateRow(record.index, { shift_id: v })}
                                style={{ width: '100%' }}
                                options={shifts.map((s) => ({ value: s.id, label: s.name }))}
                            />
                        ),
                    },
                    {
                        title: 'Jenis BBM',
                        key: 'fuel_type',
                        width: 130,
                        render: (_, record) => (
                            <Select
                                allowClear
                                disabled={disabled}
                                value={record.fuel_type_id}
                                onChange={(v) => updateRow(record.index, { fuel_type_id: v })}
                                style={{ width: '100%' }}
                                options={fuelTypes.map((f) => ({ value: f.id, label: f.name }))}
                            />
                        ),
                    },
                    {
                        title: 'Liter',
                        key: 'liters',
                        width: 110,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.liters}
                                onChange={(v) => updateRow(record.index, { liters: v ?? 0 })}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Jam Kerja',
                        key: 'hours',
                        width: 110,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.working_hours}
                                onChange={(v) => updateRow(record.index, { working_hours: v })}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Kategori',
                        key: 'category',
                        width: 150,
                        render: (_, record) => (
                            <Select
                                disabled={disabled}
                                value={record.usage_category}
                                onChange={(v) => updateRow(record.index, { usage_category: v })}
                                style={{ width: '100%' }}
                                options={Object.entries(fuelCategories).map(([value, label]) => ({
                                    value,
                                    label,
                                }))}
                            />
                        ),
                    },
                    {
                        title: '',
                        key: 'actions',
                        width: 50,
                        render: (_, record) =>
                            !disabled && (
                                <Button
                                    type="text"
                                    danger
                                    icon={<DeleteOutlined />}
                                    onClick={() => removeRow(record.index)}
                                />
                            ),
                    },
                ]}
            />
            {rows.length > 0 && (
                <Space style={{ marginTop: 12 }}>
                    <strong>Total Liter:</strong>{' '}
                    {rows.reduce((sum, r) => sum + (r.liters ?? 0), 0).toLocaleString('id-ID')}
                </Space>
            )}
        </Card>
    );
}
