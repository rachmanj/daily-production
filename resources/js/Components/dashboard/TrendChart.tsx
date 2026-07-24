import type { TrendPoint } from '@/types/dashboard';
import { Line } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface TrendChartProps {
    data: TrendPoint[];
    loading?: boolean;
}

export default function TrendChart({ data, loading }: TrendChartProps) {
    const chartData = useMemo(() => {
        const points: { date: string; value: number; type: string }[] = [];
        for (const row of data) {
            points.push({ date: row.date, value: row.ob, type: 'OB (Bcm)' });
            points.push({ date: row.date, value: row.coal, type: 'Coal (Ton)' });
            if (row.sr !== null) {
                points.push({ date: row.date, value: row.sr, type: 'SR' });
            }
        }
        return points;
    }, [data]);

    const config = {
        data: chartData,
        xField: 'date',
        yField: 'value',
        colorField: 'type',
        smooth: true,
        height: 300,
        legend: { position: 'top' as const },
        axis: { x: { labelAutoRotate: true } },
    };

    return (
        <Card title="Trend 30 Hari — OB / Coal / SR" size="small">
            {loading ? <Spin /> : <Line {...config} />}
        </Card>
    );
}
