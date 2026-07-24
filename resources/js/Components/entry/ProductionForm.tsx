import type { PitOption, ProductionRecord, ShiftOption } from '@/types/daily-entry';
import { Card, InputNumber, Select, Table, Typography } from 'antd';
import { useMemo } from 'react';

const { Text } = Typography;

export interface ProductionRow {
    pit_id: number;
    shift_id: number;
    ob_removal_bcm: number | null;
    coal_getting_ton: number | null;
    coal_hauling_ton: number | null;
    activity: string | null;
    truck_count: number | null;
}

interface ProductionFormProps {
    pits: PitOption[];
    shifts: ShiftOption[];
    records: ProductionRecord[];
    productionActivities: Record<string, string>;
    onChange: (records: ProductionRow[]) => void;
    disabled?: boolean;
}

function buildRows(
    pits: PitOption[],
    shifts: ShiftOption[],
    records: ProductionRecord[],
): ProductionRow[] {
    const rows: ProductionRow[] = [];
    for (const pit of pits) {
        for (const shift of shifts) {
            const existing = records.find((r) => r.pit_id === pit.id && r.shift_id === shift.id);
            rows.push({
                pit_id: pit.id,
                shift_id: shift.id,
                ob_removal_bcm: existing?.ob_removal_bcm ?? null,
                coal_getting_ton: existing?.coal_getting_ton ?? null,
                coal_hauling_ton: existing?.coal_hauling_ton ?? null,
                activity: existing?.activity ?? null,
                truck_count: existing?.truck_count ?? null,
            });
        }
    }
    return rows;
}

export default function ProductionForm({
    pits,
    shifts,
    records,
    productionActivities,
    onChange,
    disabled,
}: ProductionFormProps) {
    const rows = useMemo(() => buildRows(pits, shifts, records), [pits, shifts, records]);

    const totals = useMemo(
        () => ({
            ob: rows.reduce((sum, r) => sum + (r.ob_removal_bcm ?? 0), 0),
            coal: rows.reduce((sum, r) => sum + (r.coal_getting_ton ?? 0), 0),
            hauling: rows.reduce((sum, r) => sum + (r.coal_hauling_ton ?? 0), 0),
            trucks: rows.reduce((sum, r) => sum + (r.truck_count ?? 0), 0),
        }),
        [rows],
    );

    const updateRow = (index: number, field: keyof ProductionRow, value: number | string | null) => {
        const next = [...rows];
        next[index] = { ...next[index], [field]: value };
        onChange(next);
    };

    const pitMap = Object.fromEntries(pits.map((p) => [p.id, p.code]));
    const shiftMap = Object.fromEntries(shifts.map((s) => [s.id, s.name]));

    return (
        <Card title="Data Produksi" size="small">
            <Table
                dataSource={rows.map((row, index) => ({ ...row, key: `${row.pit_id}-${row.shift_id}`, index }))}
                pagination={false}
                size="small"
                scroll={{ x: 900 }}
                columns={[
                    {
                        title: 'PIT',
                        key: 'pit',
                        width: 80,
                        render: (_, record) => pitMap[record.pit_id],
                    },
                    {
                        title: 'Shift',
                        key: 'shift',
                        width: 90,
                        render: (_, record) => shiftMap[record.shift_id],
                    },
                    {
                        title: 'OB (Bcm)',
                        key: 'ob',
                        width: 120,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.ob_removal_bcm}
                                onChange={(v) => updateRow(record.index, 'ob_removal_bcm', v)}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Coal (Ton)',
                        key: 'coal',
                        width: 120,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.coal_getting_ton}
                                onChange={(v) => updateRow(record.index, 'coal_getting_ton', v)}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Hauling (Ton)',
                        key: 'hauling',
                        width: 120,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.coal_hauling_ton}
                                onChange={(v) => updateRow(record.index, 'coal_hauling_ton', v)}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                    {
                        title: 'Aktivitas',
                        key: 'activity',
                        width: 140,
                        render: (_, record) => (
                            <Select
                                allowClear
                                disabled={disabled}
                                value={record.activity}
                                onChange={(v) => updateRow(record.index, 'activity', v)}
                                style={{ width: '100%' }}
                                options={Object.entries(productionActivities).map(([value, label]) => ({
                                    value,
                                    label,
                                }))}
                            />
                        ),
                    },
                    {
                        title: 'Truck',
                        key: 'truck',
                        width: 90,
                        render: (_, record) => (
                            <InputNumber
                                min={0}
                                disabled={disabled}
                                value={record.truck_count}
                                onChange={(v) => updateRow(record.index, 'truck_count', v)}
                                style={{ width: '100%' }}
                            />
                        ),
                    },
                ]}
                summary={() => (
                    <Table.Summary fixed>
                        <Table.Summary.Row>
                            <Table.Summary.Cell index={0} colSpan={2}>
                                <Text strong>Total Harian</Text>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={2}>
                                <Text strong>{totals.ob.toLocaleString('id-ID')}</Text>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={3}>
                                <Text strong>{totals.coal.toLocaleString('id-ID')}</Text>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={4}>
                                <Text strong>{totals.hauling.toLocaleString('id-ID')}</Text>
                            </Table.Summary.Cell>
                            <Table.Summary.Cell index={5} />
                            <Table.Summary.Cell index={6}>
                                <Text strong>{totals.trucks}</Text>
                            </Table.Summary.Cell>
                        </Table.Summary.Row>
                    </Table.Summary>
                )}
            />
        </Card>
    );
}
