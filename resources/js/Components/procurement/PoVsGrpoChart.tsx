import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';

interface PoVsGrpoChartProps {
    poAmount: number;
    grpoAmount: number;
    loading?: boolean;
}

export default function PoVsGrpoChart({ poAmount, grpoAmount, loading }: PoVsGrpoChartProps) {
    const data = [
        { type: 'PO Sent', value: poAmount },
        { type: 'GRPO', value: grpoAmount },
    ];

    const config = {
        data,
        xField: 'type',
        yField: 'value',
        height: 240,
        label: {
            text: (d: { value: number }) => `Rp ${(d.value / 1_000_000).toFixed(0)}jt`,
        },
    };

    return (
        <Card title="PO Sent vs GRPO" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
