import { InputNumber, Table } from 'antd';

export interface PlanTargetRow {
    pit_id: number;
    pit_code?: string;
    metric: string;
    metric_label?: string;
    owner: string;
    target_value: number;
}

interface PlanTargetGridProps {
    targets: PlanTargetRow[];
    onChange: (targets: PlanTargetRow[]) => void;
    disabled?: boolean;
}

export default function PlanTargetGrid({ targets, onChange, disabled }: PlanTargetGridProps) {
    const updateRow = (index: number, value: number | null) => {
        const next = [...targets];
        next[index] = { ...next[index], target_value: value ?? 0 };
        onChange(next);
    };

    return (
        <Table
            dataSource={targets.map((row, index) => ({ ...row, key: index, index }))}
            pagination={false}
            size="small"
            columns={[
                { title: 'PIT', dataIndex: 'pit_code', key: 'pit_code' },
                { title: 'Metrik', dataIndex: 'metric_label', key: 'metric_label' },
                { title: 'Owner', dataIndex: 'owner', key: 'owner' },
                {
                    title: 'Target',
                    key: 'target_value',
                    render: (_, record) => (
                        <InputNumber
                            min={0}
                            disabled={disabled}
                            value={record.target_value}
                            onChange={(v) => updateRow(record.index, v)}
                            style={{ width: 140 }}
                        />
                    ),
                },
            ]}
        />
    );
}
