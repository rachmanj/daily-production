import DrilldownDrawer from '@/Components/dashboard/DrilldownDrawer';
import EquipmentStatus from '@/Components/dashboard/EquipmentStatus';
import KpiCard from '@/Components/dashboard/KpiCard';
import PerPitChart from '@/Components/dashboard/PerPitChart';
import TrendChart from '@/Components/dashboard/TrendChart';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import type { KpiData, PerPitPoint, TrendPoint, UtilizationData } from '@/types/dashboard';
import { Head, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Col, DatePicker, Row, Select, Space } from 'antd';
import axios from 'axios';
import dayjs from 'dayjs';
import { useState } from 'react';

interface DashboardIndexProps {
    sites: Site[];
    filters: { site_id?: number; date: string };
}

async function fetchDashboard<T>(endpoint: string, siteId: number, date: string): Promise<T> {
    const { data } = await axios.get(`/api/dashboard/${endpoint}`, {
        params: { site_id: siteId, date },
        withCredentials: true,
    });
    return data;
}

export default function Index({ sites, filters }: DashboardIndexProps) {
    const [siteId, setSiteId] = useState(filters.site_id ?? sites[0]?.id);
    const [date, setDate] = useState(filters.date);
    const [drilldownOpen, setDrilldownOpen] = useState(false);

    const queryParams = { siteId: siteId!, date };

    const { data: kpi, isLoading: kpiLoading } = useQuery<KpiData>({
        queryKey: ['dashboard-kpi', queryParams],
        queryFn: () => fetchDashboard('kpi', siteId!, date),
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    const { data: trend = [], isLoading: trendLoading } = useQuery<TrendPoint[]>({
        queryKey: ['dashboard-trend', queryParams],
        queryFn: () => fetchDashboard('trend', siteId!, date),
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    const { data: perPit = [], isLoading: perPitLoading } = useQuery<PerPitPoint[]>({
        queryKey: ['dashboard-per-pit', queryParams],
        queryFn: () => fetchDashboard('perPit', siteId!, date),
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    const { data: utilization, isLoading: utilLoading } = useQuery<UtilizationData>({
        queryKey: ['dashboard-utilization', siteId],
        queryFn: () => fetchDashboard('utilization', siteId!, date),
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    const applyFilters = () => {
        router.get(route('dashboard'), { site_id: siteId, date }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Executive Dashboard">
            <Head title="Dashboard" />
            <Space wrap style={{ marginBottom: 16 }}>
                <Select
                    style={{ width: 220 }}
                    value={siteId}
                    onChange={setSiteId}
                    options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                />
                <DatePicker value={dayjs(date)} onChange={(d) => setDate(d?.format('YYYY-MM-DD') ?? date)} />
                <Button type="primary" onClick={applyFilters}>
                    Terapkan
                </Button>
                <Button onClick={() => setDrilldownOpen(true)}>Drill-down</Button>
            </Space>

            <Row gutter={[16, 16]}>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard
                        title="OB Removal"
                        value={kpi?.ob.today ?? 0}
                        unit="Bcm"
                        mtd={kpi?.ob.mtd}
                        achievement={kpi?.ob.achievement}
                        precision={0}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard
                        title="Coal Getting"
                        value={kpi?.coal.today ?? 0}
                        unit="Ton"
                        mtd={kpi?.coal.mtd}
                        achievement={kpi?.coal.achievement}
                        precision={0}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard
                        title="Stripping Ratio (MTD)"
                        value={kpi?.stripping_ratio.mtd ?? 0}
                        precision={2}
                    />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard
                        title="Fuel Today"
                        value={kpi?.fuel.today_liters ?? 0}
                        unit="L"
                        mtd={kpi?.fuel.mtd_liters}
                        precision={0}
                    />
                </Col>
            </Row>

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24} lg={14}>
                    <TrendChart data={trend} loading={trendLoading || kpiLoading} />
                </Col>
                <Col xs={24} lg={10}>
                    <EquipmentStatus
                        data={utilization ?? { active: 0, standby: 0, breakdown: 0, total: 0 }}
                        loading={utilLoading}
                    />
                </Col>
            </Row>

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24}>
                    <PerPitChart data={perPit} loading={perPitLoading} />
                </Col>
            </Row>

            <DrilldownDrawer
                open={drilldownOpen}
                onClose={() => setDrilldownOpen(false)}
                data={perPit}
                loading={perPitLoading}
            />
        </AuthenticatedLayout>
    );
}
