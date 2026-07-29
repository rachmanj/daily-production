import { formatHourSlot, getCellColor, type EquipmentColumn, type HeatmapData } from '@/types/hourly';
import { Table, Typography } from 'antd';
import type { ColumnsType } from 'antd/es/table';

const { Text } = Typography;

interface HourlyHeatmapProps {
    data: HeatmapData | null;
    loading?: boolean;
}

interface HeatmapRow {
    key: number;
    hour_slot: number;
    [key: string]: number | string;
}

export default function HourlyHeatmap({ data, loading }: HourlyHeatmapProps) {
    if (!data) {
        return <Table loading={loading} />;
    }

    const { equipment, grid, hourly_target: target } = data;
    const hours = Object.keys(grid).map(Number).sort((a, b) => a - b);

    if (hours.length === 0) {
        hours.push(...Array.from({ length: 12 }, (_, i) => i + 6));
    }

    const dataSource: HeatmapRow[] = hours.map((hour) => {
        const row: HeatmapRow = { key: hour, hour_slot: hour };
        let rowTotal = 0;
        for (const eq of equipment) {
            const val = grid[hour]?.[eq.equipment_id] ?? 0;
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
            total += grid[hour]?.[eq.equipment_id] ?? 0;
        }
        columnTotals[eq.equipment_id] = total;
        grandTotal += total;
    }

    const columns: ColumnsType<HeatmapRow> = [
        {
            title: 'Jam',
            dataIndex: 'hour_slot',
            key: 'hour',
            fixed: 'left',
            width: 120,
            render: (slot: number) => formatHourSlot(slot),
        },
        ...equipment.map((eq: EquipmentColumn) => ({
            title: eq.unit_code,
            dataIndex: `eq_${eq.equipment_id}`,
            key: `eq_${eq.equipment_id}`,
            width: 80,
            align: 'center' as const,
            render: (_: unknown, row: HeatmapRow) => {
                const val = grid[row.hour_slot]?.[eq.equipment_id] ?? 0;
                const bg = getCellColor(val, target);
                return (
                    <div style={{ background: bg, padding: '4px 6px', borderRadius: 4, fontWeight: val > 0 ? 600 : 400 }}>
                        {val > 0 ? val.toLocaleString('id-ID') : '—'}
                    </div>
                );
            },
        })),
        {
            title: 'D/Shift',
            dataIndex: 'row_total',
            key: 'row_total',
            width: 80,
            align: 'right' as const,
            fixed: 'right',
            render: (val: number) => <Text strong>{val.toLocaleString('id-ID')}</Text>,
        },
    ];

    return (
        <>
            <Table
                columns={columns}
                dataSource={dataSource}
                loading={loading}
                pagination={false}
                size="small"
                scroll={{ x: equipment.length * 80 + 200 }}
                summary={() => (
                    <Table.Summary fixed>
                        <Table.Summary.Row>
                            <Table.Summary.Cell index={0}>
                                <Text strong>TOTAL ALAT</Text>
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
                )}
            />
            <div style={{ marginTop: 8 }}>
                <Text type="secondary">
                    Skala warna:{' '}
                    <span style={{ background: '#ffa39e', padding: '2px 8px', borderRadius: 4 }}>&lt;70%</span>{' '}
                    <span style={{ background: '#fff566', padding: '2px 8px', borderRadius: 4 }}>70–95%</span>{' '}
                    <span style={{ background: '#b7eb8f', padding: '2px 8px', borderRadius: 4 }}>≥95%</span>{' '}
                    target/jam
                </Text>
            </div>
        </>
    );
}
