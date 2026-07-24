import { Card, Statistic, Typography } from 'antd';

const { Text } = Typography;

interface KpiCardProps {
    title: string;
    value: number;
    unit?: string;
    mtd?: number;
    achievement?: number | null;
    precision?: number;
}

export default function KpiCard({
    title,
    value,
    unit,
    mtd,
    achievement,
    precision = 0,
}: KpiCardProps) {
    return (
        <Card size="small" hoverable>
            <Statistic
                title={title}
                value={value}
                precision={precision}
                suffix={unit}
                valueStyle={{ fontSize: 24 }}
            />
            <div style={{ marginTop: 8 }}>
                {mtd !== undefined && (
                    <Text type="secondary">
                        MTD: {mtd.toLocaleString('id-ID', { maximumFractionDigits: precision })}{' '}
                        {unit}
                    </Text>
                )}
                {achievement !== undefined && achievement !== null && (
                    <div>
                        <Text
                            type={achievement >= 100 ? 'success' : achievement >= 80 ? 'warning' : 'danger'}
                        >
                            Achievement: {achievement.toFixed(1)}%
                        </Text>
                    </div>
                )}
            </div>
        </Card>
    );
}
