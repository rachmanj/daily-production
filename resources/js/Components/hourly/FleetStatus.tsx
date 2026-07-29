import { Card, Space, Tag, Typography } from 'antd';

const { Text } = Typography;

const ROLE_LABELS: Record<string, string> = {
    loader: 'Loader',
    hauler: 'Hauler',
    grader: 'Grader',
    other: 'Lainnya',
};

interface FleetStatusProps {
    fleet: Record<string, number>;
    loading?: boolean;
}

export default function FleetStatus({ fleet, loading }: FleetStatusProps) {
    const entries = Object.entries(fleet);

    return (
        <Card title="Fleet Status" size="small" loading={loading}>
            {entries.length === 0 ? (
                <Text type="secondary">Tidak ada alat ter-assign</Text>
            ) : (
                <Space wrap>
                    {entries.map(([role, count]) => (
                        <Tag key={role} color="blue">
                            {ROLE_LABELS[role] ?? role}: {count} unit
                        </Tag>
                    ))}
                </Space>
            )}
        </Card>
    );
}
