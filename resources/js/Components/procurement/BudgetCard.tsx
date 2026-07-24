import { Card, Statistic, Typography } from 'antd';

const { Text } = Typography;

interface BudgetCardProps {
    budgetAmount: number;
    actualAmount: number;
    utilizationPct: number;
    type?: string;
    loading?: boolean;
}

export default function BudgetCard({
    budgetAmount,
    actualAmount,
    utilizationPct,
    type = 'regular',
    loading,
}: BudgetCardProps) {
    return (
        <Card title={`Budget ${type === 'capex' ? 'CAPEX' : 'Regular'}`} size="small" loading={loading}>
            <Statistic
                title="Budget"
                value={budgetAmount}
                precision={0}
                prefix="Rp"
                valueStyle={{ fontSize: 20 }}
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
