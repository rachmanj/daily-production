import type { UtilizationData } from '@/types/dashboard';
import { Pie } from '@ant-design/charts';
import { Card, Spin } from 'antd';
import { useMemo } from 'react';

interface EquipmentStatusProps {
    data: UtilizationData;
    loading?: boolean;
}

export default function EquipmentStatus({ data, loading }: EquipmentStatusProps) {
    const chartData = useMemo(
        () => [
            { type: 'Aktif', value: data.active },
            { type: 'Standby (RFU)', value: data.standby },
            { type: 'Breakdown', value: data.breakdown },
        ],
        [data],
    );

    const config = {
        data: chartData,
        angleField: 'value',
        colorField: 'type',
        innerRadius: 0.6,
        height: 260,
        label: { text: 'type', style: { fontSize: 12 } },
        legend: { position: 'bottom' as const },
    };

    return (
        <Card title="Status Equipment" size="small">
            {loading ? <Spin /> : <Pie {...config} />}
        </Card>
    );
}
