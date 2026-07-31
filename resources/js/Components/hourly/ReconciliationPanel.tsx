import { Card, Col, Row, Statistic, Tag, Typography } from 'antd';
import type { ReconciliationData } from '@/types/hourly';

const { Text } = Typography;

interface ReconciliationPanelProps {
    data: ReconciliationData | null;
    productionSource: string;
    loading?: boolean;
}

function delta(trip: number, manual: number): { value: number; color: string } {
    const diff = Math.abs(trip - manual);
    if (diff < 0.01) {
        return { value: diff, color: 'green' };
    }
    if (diff / Math.max(manual, 1) < 0.05) {
        return { value: diff, color: 'gold' };
    }

    return { value: diff, color: 'red' };
}

export default function ReconciliationPanel({ data, productionSource, loading }: ReconciliationPanelProps) {
    if (!data) {
        return (
            <Card title="Rekonsiliasi Trip vs Manual" size="small" loading={loading}>
                <Text type="secondary">Tidak ada daily entry untuk tanggal ini</Text>
            </Card>
        );
    }

    const obDelta = delta(data.trip_ob, data.manual_ob);
    const coalDelta = delta(data.trip_coal, data.manual_coal);

    return (
        <Card
            title="Rekonsiliasi Trip vs Manual"
            size="small"
            loading={loading}
            extra={<Tag color={productionSource === 'trip_derived' ? 'green' : 'blue'}>{productionSource}</Tag>}
        >
            <Row gutter={16}>
                <Col span={12}>
                    <Statistic title="Σ Trip OB (BCM)" value={data.trip_ob} precision={2} />
                    <Statistic title="Manual OB (BCM)" value={data.manual_ob} precision={2} />
                    <Tag color={obDelta.color}>Δ {obDelta.value.toFixed(2)}</Tag>
                </Col>
                <Col span={12}>
                    <Statistic title="Σ Trip Coal (Mton)" value={data.trip_coal} precision={2} />
                    <Statistic title="Manual Coal (Mton)" value={data.manual_coal} precision={2} />
                    <Tag color={coalDelta.color}>Δ {coalDelta.value.toFixed(2)}</Tag>
                </Col>
            </Row>
        </Card>
    );
}
