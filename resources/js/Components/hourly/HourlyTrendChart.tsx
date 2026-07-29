import { formatHourSlot } from '@/types/hourly';
import { Column } from '@ant-design/charts';
import { Card } from 'antd';

interface TrendPoint {
    hour_slot: number;
    total: number;
}

interface HourlyTrendChartProps {
    data: TrendPoint[];
    loading?: boolean;
}

export default function HourlyTrendChart({ data, loading }: HourlyTrendChartProps) {
    const chartData = data.map((d) => ({
        hour: formatHourSlot(d.hour_slot),
        total: d.total,
    }));

    return (
        <Card title="Tren D/Shift (per jam)" size="small" loading={loading}>
            <Column
                data={chartData}
                xField="hour"
                yField="total"
                height={220}
                label={{ position: 'top', style: { fontSize: 10 } }}
                xAxis={{ label: { autoRotate: true, autoHide: true } }}
                meta={{ total: { alias: 'Mton' } }}
            />
        </Card>
    );
}
