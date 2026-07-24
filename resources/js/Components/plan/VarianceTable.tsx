import DataTable from '@/Components/DataTable';
import type { ProColumns } from '@ant-design/pro-components';
import { Tag } from 'antd';

export interface VarianceRow {
    pit_id: number;
    pit_code: string;
    metric: string;
    metric_label: string;
    owner: string;
    plan: number;
    actual: number;
    variance: number;
    variance_pct: number | null;
    achievement: number | null;
}

interface VarianceTableProps {
    data: VarianceRow[];
    loading?: boolean;
}

export default function VarianceTable({ data, loading }: VarianceTableProps) {
    const columns: ProColumns<VarianceRow>[] = [
        { title: 'PIT', dataIndex: 'pit_code', key: 'pit_code' },
        { title: 'Metrik', dataIndex: 'metric_label', key: 'metric_label' },
        { title: 'Owner', dataIndex: 'owner', key: 'owner' },
        {
            title: 'Plan',
            dataIndex: 'plan',
            key: 'plan',
            render: (_, r) => r.plan.toLocaleString('id-ID'),
        },
        {
            title: 'Actual',
            dataIndex: 'actual',
            key: 'actual',
            render: (_, r) => r.actual.toLocaleString('id-ID'),
        },
        {
            title: 'Variance',
            dataIndex: 'variance',
            key: 'variance',
            render: (_, r) => (
                <Tag color={r.variance <= 0 ? 'success' : 'error'}>
                    {r.variance.toLocaleString('id-ID')}
                </Tag>
            ),
        },
        {
            title: 'Var %',
            dataIndex: 'variance_pct',
            key: 'variance_pct',
            render: (_, r) => (r.variance_pct !== null ? `${r.variance_pct}%` : '—'),
        },
        {
            title: 'Achievement',
            dataIndex: 'achievement',
            key: 'achievement',
            render: (_, r) =>
                r.achievement !== null ? `${r.achievement.toFixed(1)}%` : '—',
        },
    ];

    return (
        <DataTable<VarianceRow>
            headerTitle="Analisis Variance"
            dataSource={data}
            columns={columns}
            loading={loading}
            search={false}
            pagination={false}
        />
    );
}
