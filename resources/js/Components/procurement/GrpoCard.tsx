import { Card, Progress, Statistic, Tag, Typography } from 'antd';

const { Text } = Typography;

interface GrpoCardProps {
    poAmount: number;
    grpoAmount: number;
    completionPct: number;
    status: string;
    loading?: boolean;
}

const STATUS_COLOR: Record<string, string> = {
    good: 'success',
    warning: 'warning',
    critical: 'error',
};

export default function GrpoCard({
    poAmount,
    grpoAmount,
    completionPct,
    status,
    loading,
}: GrpoCardProps) {
    return (
        <Card title="GRPO vs PO Sent" size="small" loading={loading}>
            <Statistic title="PO Sent" value={poAmount} precision={0} prefix="Rp" />
            <Statistic
                title="GRPO"
                value={grpoAmount}
                precision={0}
                prefix="Rp"
                valueStyle={{ fontSize: 18, marginTop: 8 }}
            />
            <Progress percent={completionPct} size="small" style={{ marginTop: 12 }} />
            <Tag color={STATUS_COLOR[status] ?? 'default'} style={{ marginTop: 8 }}>
                {status}
            </Tag>
            <Text type="secondary" style={{ display: 'block', marginTop: 4 }}>
                Completion: {completionPct.toFixed(1)}%
            </Text>
        </Card>
    );
}
