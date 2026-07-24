import type { PerPitPoint } from '@/types/dashboard';
import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface PerPitChartProps {
    data: PerPitPoint[];
    loading?: boolean;
}

export default function PerPitChart({ data, loading }: PerPitChartProps) {
    const chartData = useMemo(() => {
        const points: { pit: string; value: number; type: string }[] = [];
        for (const row of data) {
            points.push({ pit: row.pit_code, value: row.ob, type: 'OB (Bcm)' });
            points.push({ pit: row.pit_code, value: row.coal, type: 'Coal (Ton)' });
        }
        return points;
    }, [data]);

    const config = {
        data: chartData,
        xField: 'pit',
        yField: 'value',
        colorField: 'type',
        group: true,
        height: 280,
        legend: { position: 'top' as const },
    };

    return (
        <Card title="Produksi per PIT" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
