import type { EquipmentAssignment, EquipmentDeployment, PitOption, ShiftOption } from '@/types/daily-entry';
import { Button, Card, Input, InputNumber, Select, Table } from 'antd';
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons';

export interface DeploymentRow {
    equipment_id: number;
    unit_code: string;
    pit_id: number | null;
    shift_id: number;
    prod_ob_bcm: number | null;
    prod_coal_ton: number | null;
    operator_name: string | null;
}

interface DeploymentFormProps {
    records: EquipmentDeployment[];
    pits: PitOption[];
    shifts: ShiftOption[];
    equipmentAssignments: EquipmentAssignment[];
    onChange: (records: DeploymentRow[]) => void;
    disabled?: boolean;
}

function toRows(records: EquipmentDeployment[]): DeploymentRow[] {
    return records.map((r) => ({
        equipment_id: r.equipment_id,
        unit_code: r.unit_code,
        pit_id: r.pit_id ?? null,
        shift_id: r.shift_id,
        prod_ob_bcm: r.prod_ob_bcm ?? null,
        prod_coal_ton: r.prod_coal_ton ?? null,
        operator_name: r.operator_name ?? null,
    }));
}

export default function DeploymentForm({
    records,
    pits,
    shifts,
    equipmentAssignments,
    onChange,
    disabled,
}: DeploymentFormProps) {
    const rows = toRows(records);

    const updateRow = (index: number, patch: Partial<DeploymentRow>) => {
        const next = [...rows];
        next[index] = { ...next[index], ...patch };
        onChange(next);
    };

    const addRow = () => {
        const first = equipmentAssignments[0];
        onChange([
            ...rows,
            {
                equipment_id: first?.equipment_id ?? 0,
                unit_code: first?.unit_code ?? '',
                pit_id: pits[0]?.id ?? null,
                shift_id: shifts[0]?.id ?? 0,
                prod_ob_bcm: null,
                prod_coal_ton: null,
                operator_name: null,
            },
        ]);
    };

    const removeRow = (index: number) => {
        onChange(rows.filter((_, i) => i !== index));
    };

    return (
        <Card
            title="Deployment Equipment"
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
                scroll={{ x: 900 }}
                columns={[
                    {
                        title: 'Equipment',
                        key: 'equipment',
                        width: 160,
                        render: (_, record) => (
                            <Select
                                disabled={disabled}
                                value={record.equipment_id || undefined}
                                style={{ width: '100%' }}
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
                        title: 'PIT',
                        key: 'pit',
                        width: 100,
                        render: (_, record) => (
                            <Select
                                allowClear
                                disabled={disabled}
                                value={record.pit_id}
                                onChange={(v) => updateRow(record.index, { pit_id: v })}
                                style={{ width: '100%' }}
                                options={pits.map((p) => ({ value: p.id, label: p.code }))}
                            />
                        ),
                    },
                    {
                        title: 'Shift',
                        key: 'shift',
                        width: 110,
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
                        title: 'OB (Bcm)',
                        key: 'ob',
                        width: 110,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.prod_ob_bcm}
                                onChange={(v) => updateRow(record.index, { prod_ob_bcm: v })}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Coal (Ton)',
                        key: 'coal',
                        width: 110,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.prod_coal_ton}
                                onChange={(v) => updateRow(record.index, { prod_coal_ton: v })}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Operator',
                        key: 'operator',
                        width: 140,
                        render: (_, record) => (
                            <Input
                                disabled={disabled}
                                value={record.operator_name ?? ''}
                                onChange={(e) =>
                                    updateRow(record.index, { operator_name: e.target.value || null })
                                }
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
        </Card>
    );
}
