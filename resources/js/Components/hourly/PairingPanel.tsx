import { Card, Collapse, Space, Tag, Typography } from 'antd';
import type { PairingData } from '@/types/hourly';

const { Text } = Typography;

interface PairingPanelProps {
    data: PairingData[] | null;
    loading?: boolean;
}

export default function PairingPanel({ data, loading }: PairingPanelProps) {
    const pairs = data ?? [];

    return (
        <Card title="Pairing Excavator × Hauler" size="small" loading={loading}>
            {pairs.length === 0 ? (
                <Text type="secondary">Belum ada data trip</Text>
            ) : (
                <Collapse
                    size="small"
                    items={pairs.map((exc) => ({
                        key: exc.excavator_code ?? String(exc.excavator_id),
                        label: (
                            <Space>
                                <Text strong>{exc.excavator_code}</Text>
                                <Tag>{exc.total_trips} rit</Tag>
                                <Text type="secondary">{exc.total_volume.toLocaleString('id-ID')} BCM</Text>
                            </Space>
                        ),
                        children: (
                            <Space direction="vertical" style={{ width: '100%' }}>
                                {exc.haulers.map((h) => (
                                    <div key={h.hauler_code ?? String(h.hauler_id)}>
                                        <Text>{h.hauler_code}</Text>
                                        {' — '}
                                        <Text type="secondary">
                                            {h.trip_count} rit · {h.total_volume.toLocaleString('id-ID')} BCM · load{' '}
                                            {h.avg_load_percent}%
                                        </Text>
                                    </div>
                                ))}
                            </Space>
                        ),
                    }))}
                />
            )}
        </Card>
    );
}
