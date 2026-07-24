import type { FuelByEquipmentRow } from '@/types/dashboard';
import { Line } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface FcrTrendChartProps {
    data: FuelByEquipmentRow[];
    loading?: boolean;
}

export default function FcrTrendChart({ data, loading }: FcrTrendChartProps) {
    const chartData = useMemo(
        () =>
            data
                .filter((row) => row.fcr !== null)
                .map((row) => ({
                    unit: row.unit_code,
                    fcr: row.fcr as number,
                })),
        [data],
    );

    const config = {
        data: chartData,
        xField: 'unit',
        yField: 'fcr',
        height: 280,
        label: { text: 'fcr', style: { fontSize: 10 } },
        axis: { x: { labelAutoRotate: true } },
    };

    return (
        <Card title="FCR per Equipment" size="small">
            {loading ? <Spin /> : <Line {...config} />}
        </Card>
    );
}
