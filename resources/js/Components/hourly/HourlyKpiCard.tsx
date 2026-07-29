import { getAchievementColor } from '@/types/hourly';
import { Card, Col, Row, Statistic, Typography } from 'antd';

const { Text } = Typography;

interface HourlyKpiCardProps {
    materialLabel: string;
    dtd: { actual: number; plan: number | null; achievement: number | null };
    mtd: { actual: number; plan: number | null; achievement: number | null };
    currentHour: {
        hour_slot: number;
        tonnage: number;
        target: number | null;
        achievement: number | null;
    } | null;
    loading?: boolean;
}

function formatHour(slot: number): string {
    const next = (slot + 1) % 24;
    return `${String(slot).padStart(2, '0')}:00–${String(next).padStart(2, '0')}:00`;
}

export default function HourlyKpiCard({
    materialLabel,
    dtd,
    mtd,
    currentHour,
    loading,
}: HourlyKpiCardProps) {
    return (
        <Card title={materialLabel} size="small" loading={loading}>
            <Row gutter={[12, 12]}>
                <Col xs={24} sm={8}>
                    <Statistic
                        title="DTD"
                        value={dtd.actual}
                        precision={0}
                        suffix="Mton"
                    />
                    <Text type="secondary">
                        / {(dtd.plan ?? 0).toLocaleString('id-ID')} plan
                    </Text>
                    {dtd.achievement !== null && (
                        <div>
                            <Text type={getAchievementColor(dtd.achievement)}>
                                {dtd.achievement.toFixed(1)}%
                            </Text>
                        </div>
                    )}
                </Col>
                <Col xs={24} sm={8}>
                    <Statistic
                        title="MTD"
                        value={mtd.actual}
                        precision={0}
                        suffix="Mton"
                    />
                    <Text type="secondary">
                        / {(mtd.plan ?? 0).toLocaleString('id-ID')} plan
                    </Text>
                    {mtd.achievement !== null && (
                        <div>
                            <Text type={getAchievementColor(mtd.achievement)}>
                                {mtd.achievement.toFixed(1)}%
                            </Text>
                        </div>
                    )}
                </Col>
                <Col xs={24} sm={8}>
                    <Statistic
                        title="Jam Ini"
                        value={currentHour?.tonnage ?? 0}
                        precision={0}
                        suffix="Mton"
                    />
                    {currentHour && (
                        <Text type="secondary">
                            {formatHour(currentHour.hour_slot)} / {(currentHour.target ?? 0).toLocaleString('id-ID')} tgt
                        </Text>
                    )}
                    {currentHour?.achievement !== null && currentHour?.achievement !== undefined && (
                        <div>
                            <Text type={getAchievementColor(currentHour.achievement)}>
                                {currentHour.achievement.toFixed(1)}%
                            </Text>
                        </div>
                    )}
                </Col>
            </Row>
        </Card>
    );
}

export function HourlyDashboardCard(props: HourlyKpiCardProps) {
    return <HourlyKpiCard {...props} />;
}
