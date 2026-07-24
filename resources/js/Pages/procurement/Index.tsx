import BudgetCard from '@/Components/procurement/BudgetCard';
import CapexCard from '@/Components/procurement/CapexCard';
import GrpoCard from '@/Components/procurement/GrpoCard';
import LastSyncedBadge from '@/Components/procurement/LastSyncedBadge';
import NpiCard from '@/Components/procurement/NpiCard';
import NpiInOutChart from '@/Components/procurement/NpiInOutChart';
import PoVsGrpoChart from '@/Components/procurement/PoVsGrpoChart';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Col, InputNumber, Row, Select, Space } from 'antd';
import axios from 'axios';
import { useState } from 'react';

interface ProcurementIndexProps {
    sites: Site[];
    filters: { siteId?: number; year: number; month: number };
}

interface PoSentData {
    po_amount: number;
    budget_amount: number;
    budget_pct: number;
    last_synced_at: string;
}

interface GrpoData {
    po_amount: number;
    grpo_amount: number;
    completion_pct: number;
    status: string;
    last_synced_at: string;
}

interface NpiData {
    incoming_qty: number;
    outgoing_qty: number;
    npi_index: number;
    status: string;
    last_synced_at: string;
}

interface BudgetData {
    budget_amount: number;
    actual_amount: number;
    utilization_pct: number;
    last_synced_at: string;
}

async function fetchProcurement<T>(
    endpoint: string,
    params: Record<string, string | number | undefined>,
): Promise<T> {
    const { data } = await axios.get(`/api/procurement/${endpoint}`, {
        params,
        withCredentials: true,
    });
    return data;
}

export default function Index({ sites, filters }: ProcurementIndexProps) {
    const [siteId, setSiteId] = useState(filters.siteId ?? sites[0]?.id);
    const [year, setYear] = useState(filters.year);
    const [month, setMonth] = useState(filters.month);

    const params = { site_id: siteId, year, month };

    const { data: poSent, isLoading: poLoading } = useQuery<PoSentData>({
        queryKey: ['procurement-po', params],
        queryFn: () => fetchProcurement('po-sent', params),
        enabled: !!siteId,
        refetchInterval: 300_000,
    });

    const { data: grpo, isLoading: grpoLoading } = useQuery<GrpoData>({
        queryKey: ['procurement-grpo', params],
        queryFn: () => fetchProcurement('grpo', params),
        enabled: !!siteId,
        refetchInterval: 300_000,
    });

    const { data: npi, isLoading: npiLoading } = useQuery<NpiData>({
        queryKey: ['procurement-npi', params],
        queryFn: () => fetchProcurement('npi', params),
        enabled: !!siteId,
        refetchInterval: 300_000,
    });

    const { data: budget, isLoading: budgetLoading } = useQuery<BudgetData>({
        queryKey: ['procurement-budget', params],
        queryFn: () => fetchProcurement('budget', params),
        enabled: !!siteId,
        refetchInterval: 300_000,
    });

    const { data: capexBudget } = useQuery<BudgetData>({
        queryKey: ['procurement-budget-capex', params],
        queryFn: () => fetchProcurement('budget', { ...params, type: 'capex' }),
        enabled: !!siteId,
        refetchInterval: 300_000,
    });

    const applyFilters = () => {
        router.get(route('procurement.index'), { site_id: siteId, year, month }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Procurement KPI">
            <Head title="Procurement" />
            <Space wrap style={{ marginBottom: 16 }}>
                <Select
                    style={{ width: 220 }}
                    value={siteId}
                    onChange={setSiteId}
                    options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                />
                <InputNumber min={2020} max={2100} value={year} onChange={(v) => setYear(v ?? year)} addonBefore="Tahun" />
                <InputNumber min={1} max={12} value={month} onChange={(v) => setMonth(v ?? month)} addonBefore="Bulan" />
                <Button type="primary" onClick={applyFilters}>
                    Terapkan
                </Button>
                <LastSyncedBadge lastSyncedAt={grpo?.last_synced_at} />
            </Space>

            <Row gutter={[16, 16]}>
                <Col xs={24} sm={12} lg={6}>
                    <GrpoCard
                        poAmount={grpo?.po_amount ?? 0}
                        grpoAmount={grpo?.grpo_amount ?? 0}
                        completionPct={grpo?.completion_pct ?? 0}
                        status={grpo?.status ?? 'warning'}
                        loading={grpoLoading}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <NpiCard
                        incomingQty={npi?.incoming_qty ?? 0}
                        outgoingQty={npi?.outgoing_qty ?? 0}
                        npiIndex={npi?.npi_index ?? 0}
                        status={npi?.status ?? 'warning'}
                        loading={npiLoading}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <BudgetCard
                        budgetAmount={budget?.budget_amount ?? 0}
                        actualAmount={budget?.actual_amount ?? 0}
                        utilizationPct={budget?.utilization_pct ?? 0}
                        loading={budgetLoading}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <CapexCard
                        budgetAmount={capexBudget?.budget_amount ?? 0}
                        actualAmount={capexBudget?.actual_amount ?? 0}
                        utilizationPct={capexBudget?.utilization_pct ?? 0}
                        poAmount={poSent?.po_amount}
                        loading={poLoading}
                    />
                </Col>
            </Row>

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24} md={12}>
                    <PoVsGrpoChart
                        poAmount={grpo?.po_amount ?? 0}
                        grpoAmount={grpo?.grpo_amount ?? 0}
                        loading={grpoLoading}
                    />
                </Col>
                <Col xs={24} md={12}>
                    <NpiInOutChart
                        incomingQty={npi?.incoming_qty ?? 0}
                        outgoingQty={npi?.outgoing_qty ?? 0}
                        loading={npiLoading}
                    />
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
