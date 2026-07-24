import { Card, Statistic, Tag } from 'antd';

interface NpiCardProps {
    incomingQty: number;
    outgoingQty: number;
    npiIndex: number;
    status: string;
    loading?: boolean;
}

const STATUS_COLOR: Record<string, string> = {
    good: 'success',
    warning: 'warning',
    critical: 'error',
};

export default function NpiCard({
    incomingQty,
    outgoingQty,
    npiIndex,
    status,
    loading,
}: NpiCardProps) {
    return (
        <Card title="NPI Index" size="small" loading={loading}>
            <Statistic title="Incoming" value={incomingQty} />
            <Statistic title="Outgoing" value={outgoingQty} valueStyle={{ fontSize: 18, marginTop: 8 }} />
            <Statistic
                title="NPI Index"
                value={npiIndex}
                precision={2}
                valueStyle={{ fontSize: 22, marginTop: 8 }}
            />
            <Tag color={STATUS_COLOR[status] ?? 'default'} style={{ marginTop: 8 }}>
                {status}
            </Tag>
        </Card>
    );
}
