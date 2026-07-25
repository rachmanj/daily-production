import type { ConsolidatedSiteRow } from '@/types/dashboard';
import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface SiteComparisonChartProps {
    data: ConsolidatedSiteRow[];
    loading?: boolean;
}

export default function SiteComparisonChart({ data, loading }: SiteComparisonChartProps) {
    const chartData = useMemo(() => {
        const points: { site: string; value: number; type: string }[] = [];
        for (const row of data) {
            points.push({ site: row.site_code, value: row.ob, type: 'OB (Bcm)' });
            points.push({ site: row.site_code, value: row.coal, type: 'Coal (Ton)' });
        }
        return points;
    }, [data]);

    const config = {
        data: chartData,
        xField: 'site',
        yField: 'value',
        colorField: 'type',
        group: true,
        height: 280,
        legend: { position: 'top' as const },
    };

    return (
        <Card title="Perbandingan per Site" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
