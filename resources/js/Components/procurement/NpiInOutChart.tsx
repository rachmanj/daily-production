import { Column } from '@ant-design/charts';
import { Card, Spin } from 'antd';

interface NpiInOutChartProps {
    incomingQty: number;
    outgoingQty: number;
    loading?: boolean;
}

export default function NpiInOutChart({ incomingQty, outgoingQty, loading }: NpiInOutChartProps) {
    const data = [
        { type: 'Incoming', value: incomingQty },
        { type: 'Outgoing', value: outgoingQty },
    ];

    const config = {
        data,
        xField: 'type',
        yField: 'value',
        height: 240,
        colorField: 'type',
    };

    return (
        <Card title="NPI Incoming vs Outgoing" size="small">
            {loading ? <Spin /> : <Column {...config} />}
        </Card>
    );
}
