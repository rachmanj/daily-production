import LossContributionChart from '@/Components/plan/LossContributionChart';
import PlanVsActualChart from '@/Components/plan/PlanVsActualChart';
import VarianceTable, { type VarianceRow } from '@/Components/plan/VarianceTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button, Col, InputNumber, Row, Select, Space } from 'antd';

interface LossContributionData {
    total_rain_hours: number;
    total_slippery_hours: number;
    rain_days: number;
    slippery_days: number;
}

interface VarianceIndexProps {
    sites: Site[];
    filters: { siteId?: number; year: number; month: number };
    variance: VarianceRow[];
    lossContribution: LossContributionData;
}

export default function Index({ sites, filters, variance, lossContribution }: VarianceIndexProps) {
    const applyFilters = (patch: Partial<typeof filters>) => {
        router.get(route('variance.index'), { ...filters, ...patch }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Variance Analysis">
            <Head title="Variance" />
            <Space wrap style={{ marginBottom: 16 }}>
                <Select
                    style={{ width: 220 }}
                    value={filters.siteId}
                    onChange={(v) => applyFilters({ siteId: v })}
                    options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                />
                <InputNumber
                    min={2020}
                    max={2100}
                    value={filters.year}
                    onChange={(v) => applyFilters({ year: v ?? filters.year })}
                    addonBefore="Tahun"
                />
                <InputNumber
                    min={1}
                    max={12}
                    value={filters.month}
                    onChange={(v) => applyFilters({ month: v ?? filters.month })}
                    addonBefore="Bulan"
                />
                <Button type="primary" onClick={() => applyFilters({})}>
                    Refresh
                </Button>
            </Space>

            <VarianceTable data={variance} />

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24} lg={14}>
                    <PlanVsActualChart data={variance} />
                </Col>
                <Col xs={24} lg={10}>
                    <LossContributionChart data={lossContribution} />
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
