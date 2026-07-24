import PlanTargetGrid, { type PlanTargetRow } from '@/Components/plan/PlanTargetGrid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Pit, Site } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Form, InputNumber, Select } from 'antd';
import { useMemo, useState } from 'react';

interface CreateProps {
    sites: Site[];
    pits: Pit[];
}

export default function Create({ sites, pits }: CreateProps) {
    const [siteId, setSiteId] = useState(sites[0]?.id);
    const [year, setYear] = useState(new Date().getFullYear());
    const [month, setMonth] = useState(new Date().getMonth() + 1);
    const [targets, setTargets] = useState<PlanTargetRow[]>([]);

    const sitePits = useMemo(() => pits.filter((p) => p.site_id === siteId), [pits, siteId]);

    const initTargets = () => {
        const rows: PlanTargetRow[] = [];
        for (const pit of sitePits) {
            rows.push({
                pit_id: pit.id,
                pit_code: pit.code,
                metric: 'ob',
                metric_label: 'OB',
                owner: pit.owner ?? 'internal',
                target_value: 0,
            });
            rows.push({
                pit_id: pit.id,
                pit_code: pit.code,
                metric: 'coal',
                metric_label: 'Coal',
                owner: pit.owner ?? 'internal',
                target_value: 0,
            });
        }
        setTargets(rows);
    };

    const submit = () => {
        router.post(route('monthly-plans.store'), {
            site_id: siteId,
            year,
            month,
            targets,
        } as never);
    };

    return (
        <AuthenticatedLayout title="Plan Baru">
            <Head title="Plan Baru" />
            <Card style={{ marginBottom: 16 }}>
                <Form layout="inline">
                    <Form.Item label="Site">
                        <Select
                            style={{ width: 200 }}
                            value={siteId}
                            onChange={setSiteId}
                            options={sites.map((s) => ({
                                value: s.id,
                                label: `${s.code} — ${s.name}`,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="Tahun">
                        <InputNumber min={2020} max={2100} value={year} onChange={(v) => setYear(v ?? year)} />
                    </Form.Item>
                    <Form.Item label="Bulan">
                        <InputNumber min={1} max={12} value={month} onChange={(v) => setMonth(v ?? month)} />
                    </Form.Item>
                    <Form.Item>
                        <Button onClick={initTargets}>Generate Target Grid</Button>
                    </Form.Item>
                </Form>
            </Card>
            {targets.length > 0 && (
                <>
                    <PlanTargetGrid targets={targets} onChange={setTargets} />
                    <Button type="primary" style={{ marginTop: 16 }} onClick={submit}>
                        Simpan Plan
                    </Button>
                </>
            )}
        </AuthenticatedLayout>
    );
}
