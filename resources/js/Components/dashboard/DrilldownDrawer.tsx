import type { PerPitPoint } from '@/types/dashboard';
import { Drawer, Table } from 'antd';

interface DrilldownDrawerProps {
    open: boolean;
    onClose: () => void;
    title?: string;
    data: PerPitPoint[];
    loading?: boolean;
}

export default function DrilldownDrawer({
    open,
    onClose,
    title = 'Drill-down Produksi',
    data,
    loading,
}: DrilldownDrawerProps) {
    return (
        <Drawer title={title} open={open} onClose={onClose} width={520}>
            <Table
                loading={loading}
                dataSource={data}
                rowKey="pit_id"
                size="small"
                pagination={false}
                columns={[
                    { title: 'PIT', dataIndex: 'pit_code', key: 'pit_code' },
                    {
                        title: 'OB (Bcm)',
                        dataIndex: 'ob',
                        key: 'ob',
                        render: (v: number) => v.toLocaleString('id-ID'),
                    },
                    {
                        title: 'Coal (Ton)',
                        dataIndex: 'coal',
                        key: 'coal',
                        render: (v: number) => v.toLocaleString('id-ID'),
                    },
                    {
                        title: 'SR',
                        key: 'sr',
                        render: (_, record) =>
                            record.coal > 0 ? (record.ob / record.coal).toFixed(2) : '—',
                    },
                ]}
            />
        </Drawer>
    );
}
