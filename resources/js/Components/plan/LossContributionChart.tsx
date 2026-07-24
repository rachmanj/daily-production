import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';

interface LossContributionData {
    total_rain_hours: number;
    total_slippery_hours: number;
    rain_days: number;
    slippery_days: number;
}

interface LossContributionChartProps {
    data: LossContributionData;
    loading?: boolean;
}

export default function LossContributionChart({ data, loading }: LossContributionChartProps) {
    const chartData = [
        { type: 'Jam Hujan', value: data.total_rain_hours },
        { type: 'Jam Licin', value: data.total_slippery_hours },
        { type: 'Hari Hujan', value: data.rain_days },
        { type: 'Hari Licin', value: data.slippery_days },
    ];

    const config = {
        data: chartData,
        xField: 'type',
        yField: 'value',
        height: 260,
        colorField: 'type',
    };

    return (
        <Card title="Kontribusi Loss (Cuaca)" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
