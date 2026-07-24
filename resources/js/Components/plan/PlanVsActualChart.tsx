import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface PlanVsActualItem {
    pit_code: string;
    metric_label: string;
    plan: number;
    actual: number;
}

interface PlanVsActualChartProps {
    data: PlanVsActualItem[];
    loading?: boolean;
}

export default function PlanVsActualChart({ data, loading }: PlanVsActualChartProps) {
    const chartData = useMemo(() => {
        const points: { label: string; value: number; type: string }[] = [];
        for (const row of data) {
            const label = `${row.pit_code} — ${row.metric_label}`;
            points.push({ label, value: row.plan, type: 'Plan' });
            points.push({ label, value: row.actual, type: 'Actual' });
        }
        return points;
    }, [data]);

    const config = {
        data: chartData,
        xField: 'label',
        yField: 'value',
        colorField: 'type',
        group: true,
        height: 320,
        axis: { x: { labelAutoRotate: true } },
    };

    return (
        <Card title="Plan vs Actual" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
