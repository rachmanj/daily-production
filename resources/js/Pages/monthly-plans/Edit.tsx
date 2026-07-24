import PlanTargetGrid, { type PlanTargetRow } from '@/Components/plan/PlanTargetGrid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Pit } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Descriptions } from 'antd';
import { useMemo, useState } from 'react';

interface PlanTarget {
    id: number;
    pit_id: number;
    metric: string;
    owner: string;
    target_value: number;
    pit?: { id: number; code: string };
}

interface MonthlyPlan {
    id: number;
    year: number;
    month: number;
    site_id: number;
    site?: { id: number; code: string; name: string };
    plan_targets: PlanTarget[];
}

interface EditProps {
    plan: MonthlyPlan;
    pits: Pit[];
}

const MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

export default function Edit({ plan }: EditProps) {
    const initialTargets: PlanTargetRow[] = useMemo(
        () =>
            plan.plan_targets.map((t) => ({
                pit_id: t.pit_id,
                pit_code: t.pit?.code,
                metric: t.metric,
                metric_label: t.metric === 'ob' ? 'OB' : 'Coal',
                owner: t.owner,
                target_value: t.target_value,
            })),
        [plan.plan_targets],
    );

    const [targets, setTargets] = useState<PlanTargetRow[]>(initialTargets);
    const [saving, setSaving] = useState(false);

    const saveTargets = () => {
        setSaving(true);
        router.put(
            route('plan-targets.update', plan.id),
            { targets } as never,
            { onFinish: () => setSaving(false) },
        );
    };

    return (
        <AuthenticatedLayout title={`Edit Plan — ${MONTHS[plan.month - 1]} ${plan.year}`}>
            <Head title="Edit Plan" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions size="small">
                    <Descriptions.Item label="Site">
                        {plan.site?.code} — {plan.site?.name}
                    </Descriptions.Item>
                    <Descriptions.Item label="Periode">
                        {MONTHS[plan.month - 1]} {plan.year}
                    </Descriptions.Item>
                </Descriptions>
            </Card>
            <PlanTargetGrid targets={targets} onChange={setTargets} />
            <Button type="primary" loading={saving} style={{ marginTop: 16 }} onClick={saveTargets}>
                Simpan Target
            </Button>
        </AuthenticatedLayout>
    );
}
