import { Card, Statistic, Typography } from 'antd';

const { Text } = Typography;

interface CapexCardProps {
    budgetAmount: number;
    actualAmount: number;
    utilizationPct: number;
    poAmount?: number;
    loading?: boolean;
}

export default function CapexCard({
    budgetAmount,
    actualAmount,
    utilizationPct,
    poAmount,
    loading,
}: CapexCardProps) {
    return (
        <Card title="CAPEX" size="small" loading={loading}>
            {poAmount !== undefined && (
                <Statistic title="PO Sent (CAPEX)" value={poAmount} precision={0} prefix="Rp" />
            )}
            <Statistic
                title="Budget CAPEX"
                value={budgetAmount}
                precision={0}
                prefix="Rp"
                valueStyle={{ fontSize: 20, marginTop: 8 }}
            />
            <Statistic
                title="Actual"
                value={actualAmount}
                precision={0}
                prefix="Rp"
                valueStyle={{ fontSize: 18, marginTop: 8 }}
            />
            <Text
                type={utilizationPct > 90 ? 'danger' : utilizationPct > 70 ? 'warning' : 'success'}
                style={{ marginTop: 8, display: 'block' }}
            >
                Utilisasi: {utilizationPct.toFixed(1)}%
            </Text>
        </Card>
    );
}
