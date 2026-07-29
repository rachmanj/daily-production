import { getCellColor, type EquipmentColumn, type HourlyRecord } from '@/types/hourly';
import { InputNumber, Table, Typography } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import { useMemo } from 'react';

const { Text } = Typography;

interface HourlyGridProps {
    equipment: EquipmentColumn[];
    records: HourlyRecord[];
    shiftId: number;
    hourlyTarget?: number | null;
    readOnly?: boolean;
    onChange: (records: HourlyRecord[]) => void;
}

interface GridRow {
    key: number;
    hour_slot: number;
    [equipmentId: string]: number | string;
}

function buildValueMap(records: HourlyRecord[]): Map<string, number> {
    const map = new Map<string, number>();
    for (const r of records) {
        map.set(`${r.hour_slot}-${r.equipment_id}`, r.tonnage);
    }
    return map;
}

function getShiftHours(shiftId: number): number[] {
    if (shiftId === 2) {
        return Array.from({ length: 12 }, (_, i) => i + 12).concat(
            Array.from({ length: 6 }, (_, i) => i),
        );
    }
    return Array.from({ length: 20 }, (_, i) => i + 6);
}

export default function HourlyGrid({
    equipment,
    records,
    shiftId,
    hourlyTarget,
    readOnly = false,
    onChange,
}: HourlyGridProps) {
    const valueMap = useMemo(() => buildValueMap(records), [records]);
    const hours = useMemo(() => getShiftHours(shiftId), [shiftId]);

    const updateCell = (hourSlot: number, equipmentId: number, tonnage: number, unitCode: string) => {
        const filtered = records.filter(
            (r) => !(r.hour_slot === hourSlot && r.equipment_id === equipmentId),
        );
        if (tonnage > 0) {
            filtered.push({ hour_slot: hourSlot, equipment_id: equipmentId, tonnage, unit_code: unitCode, shift_id: shiftId });
        }
        onChange(filtered);
    };

    const dataSource: GridRow[] = hours.map((hour) => {
        const row: GridRow = { key: hour, hour_slot: hour };
        let rowTotal = 0;
        for (const eq of equipment) {
            const val = valueMap.get(`${hour}-${eq.equipment_id}`) ?? 0;
            row[`eq_${eq.equipment_id}`] = val;
            rowTotal += val;
        }
        row.row_total = rowTotal;
        return row;
    });

    const columnTotals: Record<number, number> = {};
    let grandTotal = 0;
    for (const eq of equipment) {
        let total = 0;
        for (const hour of hours) {
            total += valueMap.get(`${hour}-${eq.equipment_id}`) ?? 0;
        }
        columnTotals[eq.equipment_id] = total;
        grandTotal += total;
    }

    const columns: ColumnsType<GridRow> = [
        {
            title: 'Jam',
            dataIndex: 'hour_slot',
            key: 'hour',
            fixed: 'left',
            width: 110,
            render: (slot: number) => {
                const next = (slot + 1) % 24;
                return `${String(slot).padStart(2, '0')}–${String(next).padStart(2, '0')}`;
            },
        },
        ...equipment.map((eq) => ({
            title: eq.unit_code,
            dataIndex: `eq_${eq.equipment_id}`,
            key: `eq_${eq.equipment_id}`,
            width: 90,
            align: 'center' as const,
            render: (_: unknown, row: GridRow) => {
                const val = valueMap.get(`${row.hour_slot}-${eq.equipment_id}`) ?? 0;
                const bg = getCellColor(val, hourlyTarget ?? null);
                if (readOnly) {
                    return (
                        <div style={{ background: bg, padding: '4px 8px', borderRadius: 4 }}>
                            {val > 0 ? val.toLocaleString('id-ID') : '—'}
                        </div>
                    );
                }
                return (
                    <InputNumber
                        size="small"
                        min={0}
                        value={val || undefined}
                        style={{ width: '100%', background: bg }}
                        onChange={(v) => updateCell(row.hour_slot, eq.equipment_id, v ?? 0, eq.unit_code)}
                    />
                );
            },
        })),
        {
            title: 'Σ Jam',
            dataIndex: 'row_total',
            key: 'row_total',
            width: 80,
            align: 'right' as const,
            fixed: 'right',
            render: (val: number) => <Text strong>{val.toLocaleString('id-ID')}</Text>,
        },
    ];

    const summaryRow = (
        <Table.Summary fixed>
            <Table.Summary.Row>
                <Table.Summary.Cell index={0}>
                    <Text strong>Σ Alat</Text>
                </Table.Summary.Cell>
                {equipment.map((eq, i) => (
                    <Table.Summary.Cell key={eq.equipment_id} index={i + 1} align="center">
                        <Text strong>{columnTotals[eq.equipment_id].toLocaleString('id-ID')}</Text>
                    </Table.Summary.Cell>
                ))}
                <Table.Summary.Cell index={equipment.length + 1} align="right">
                    <Text strong>{grandTotal.toLocaleString('id-ID')}</Text>
                </Table.Summary.Cell>
            </Table.Summary.Row>
        </Table.Summary>
    );

    return (
        <Table
            columns={columns}
            dataSource={dataSource}
            pagination={false}
            size="small"
            scroll={{ x: equipment.length * 90 + 200 }}
            summary={() => summaryRow}
        />
    );
}
