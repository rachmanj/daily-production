import FleetStatus from '@/Components/hourly/FleetStatus';
import HourlyHeatmap from '@/Components/hourly/HourlyHeatmap';
import HourlyKpiCard from '@/Components/hourly/HourlyKpiCard';
import HourlyTrendChart from '@/Components/hourly/HourlyTrendChart';
import PairingPanel from '@/Components/hourly/PairingPanel';
import ReconciliationPanel from '@/Components/hourly/ReconciliationPanel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dayjs } from '@/lib/date';
import type { HeatmapData, HourlyKpiData, PairingData, ReconciliationData } from '@/types/hourly';
import type { Site } from '@/types';
import { Head } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Col, DatePicker, Row, Select, Space } from 'antd';
import axios from 'axios';
import { useEffect, useMemo, useState } from 'react';

interface DashboardProps {
    sites: Site[];
    filters: {
        site_id: number;
        date: string;
        material: string;
    };
    materials: Record<string, string>;
    allMaterials: Record<string, string>;
    siteCode: string;
    isTripSite: boolean;
}

export default function Dashboard({
    sites,
    filters: initialFilters,
    materials: initialMaterials,
    siteCode: initialSiteCode,
    isTripSite: initialIsTripSite,
}: DashboardProps) {
    const [filters, setFilters] = useState(initialFilters);

    const selectedSite = sites.find((s) => s.id === filters.site_id);
    const siteCode = selectedSite?.code ?? initialSiteCode;
    const isTripSite = ['022C', '017C'].includes(siteCode);

    const materials = useMemo(() => {
        const tripMaterials: Record<string, string> = {
            ob: 'Overburden (OB)',
            coal: 'Coal',
            top_soil: 'Top Soil',
        };
        const cementMaterials: Record<string, string> = {
            limestone: 'Limestone (LS)',
            shalestone: 'Shalestone (SH)',
        };

        if (siteCode === '022C') {
            return tripMaterials;
        }
        if (siteCode === '017C') {
            return { ob: tripMaterials.ob, coal: tripMaterials.coal };
        }

        return initialMaterials;
    }, [siteCode, initialMaterials]);

    useEffect(() => {
        if (!materials[filters.material]) {
            const first = Object.keys(materials)[0];
            if (first) {
                setFilters((f) => ({ ...f, material: first }));
            }
        }
    }, [materials, filters.material]);

    const queryParams = {
        site_id: filters.site_id,
        date: filters.date,
        material: filters.material,
    };

    const { data: kpi, isLoading: kpiLoading } = useQuery({
        queryKey: ['hourly-kpi', queryParams],
        queryFn: async () => {
            const { data } = await axios.get<HourlyKpiData>('/api/hourly-dashboard/kpi', { params: queryParams });
            return data;
        },
        refetchInterval: 60_000,
    });

    const { data: heatmap, isLoading: heatmapLoading } = useQuery({
        queryKey: ['hourly-heatmap', queryParams],
        queryFn: async () => {
            const { data } = await axios.get<HeatmapData>('/api/hourly-dashboard/heatmap', { params: queryParams });
            return data;
        },
        refetchInterval: 60_000,
    });

    const { data: fleet, isLoading: fleetLoading } = useQuery({
        queryKey: ['hourly-fleet', { site_id: filters.site_id, material: filters.material }],
        queryFn: async () => {
            const { data } = await axios.get<{ fleet: Record<string, number> }>('/api/hourly-dashboard/fleet', {
                params: { site_id: filters.site_id, material: filters.material },
            });
            return data.fleet;
        },
    });

    const { data: trend, isLoading: trendLoading } = useQuery({
        queryKey: ['hourly-trend', queryParams],
        queryFn: async () => {
            const { data } = await axios.get<{ trend: Array<{ hour_slot: number; total: number }> }>(
                '/api/hourly-dashboard/trend',
                { params: queryParams },
            );
            return data.trend;
        },
        refetchInterval: 60_000,
    });

    const { data: reconciliationData, isLoading: reconciliationLoading } = useQuery({
        queryKey: ['ccr-reconciliation', { site_id: filters.site_id, date: filters.date }],
        queryFn: async () => {
            const { data } = await axios.get<{
                reconciliation: ReconciliationData | null;
                production_source: string;
                daily_entry_id?: number;
            }>('/api/hourly-dashboard/reconciliation', {
                params: { site_id: filters.site_id, date: filters.date },
            });
            return data;
        },
        enabled: isTripSite,
    });

    const { data: pairing, isLoading: pairingLoading } = useQuery({
        queryKey: ['ccr-pairing', reconciliationData?.daily_entry_id],
        queryFn: async () => {
            const { data } = await axios.get<{ pairs: PairingData[] }>('/api/ccr/pairing', {
                params: { daily_entry_id: reconciliationData?.daily_entry_id },
            });
            return data.pairs;
        },
        enabled: isTripSite && !!reconciliationData?.daily_entry_id,
    });

    const exportReport = (format: 'excel' | 'pdf') => {
        const params = new URLSearchParams({
            site_id: String(filters.site_id),
            date: filters.date,
            material: filters.material,
            format,
        });
        window.open(`/hourly/report/export?${params.toString()}`, '_blank');
    };

    const defaultKpi: HourlyKpiData = {
        dtd: { actual: 0, plan: null, achievement: null },
        mtd: { actual: 0, plan: null, achievement: null },
        current_hour: null,
        hourly_target: null,
    };

    const tripKpiMaterials = isTripSite ? Object.keys(materials) : [filters.material];

    return (
        <AuthenticatedLayout title="CCR Dashboard">
            <Head title="CCR Dashboard" />
            <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <Space wrap>
                    <Select
                        value={filters.site_id}
                        onChange={(v) => {
                            setFilters((f) => ({ ...f, site_id: v }));
                        }}
                        options={sites.map((s) => ({ value: s.id, label: s.code }))}
                        style={{ width: 140 }}
                    />
                    <DatePicker
                        value={dayjs(filters.date)}
                        onChange={(d) => setFilters((f) => ({ ...f, date: d?.format('YYYY-MM-DD') ?? f.date }))}
                    />
                    {!isTripSite && (
                        <Select
                            value={filters.material}
                            onChange={(v) => setFilters((f) => ({ ...f, material: v }))}
                            options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                            style={{ width: 180 }}
                        />
                    )}
                    {isTripSite && (
                        <Select
                            value={filters.material}
                            onChange={(v) => setFilters((f) => ({ ...f, material: v }))}
                            options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                            style={{ width: 180 }}
                        />
                    )}
                    <Button onClick={() => exportReport('excel')}>Export Excel</Button>
                    <Button onClick={() => exportReport('pdf')}>Export PDF</Button>
                </Space>

                {isTripSite ? (
                    <Row gutter={[16, 16]}>
                        {tripKpiMaterials.map((mat) => (
                            <Col key={mat} xs={24} md={8}>
                                <TripKpiFetcher
                                    siteId={filters.site_id}
                                    date={filters.date}
                                    material={mat}
                                    label={materials[mat] ?? mat}
                                />
                            </Col>
                        ))}
                    </Row>
                ) : (
                    <HourlyKpiCard
                        materialLabel={materials[filters.material] ?? filters.material}
                        dtd={kpi?.dtd ?? defaultKpi.dtd}
                        mtd={kpi?.mtd ?? defaultKpi.mtd}
                        currentHour={kpi?.current_hour ?? null}
                        loading={kpiLoading}
                    />
                )}

                <HourlyHeatmap data={heatmap ?? null} loading={heatmapLoading} />

                <Row gutter={[16, 16]}>
                    <Col xs={24} lg={isTripSite ? 10 : 14}>
                        <HourlyTrendChart data={trend ?? []} loading={trendLoading} />
                    </Col>
                    {isTripSite && (
                        <Col xs={24} lg={7}>
                            <PairingPanel data={pairing ?? null} loading={pairingLoading} />
                        </Col>
                    )}
                    <Col xs={24} lg={isTripSite ? 7 : 10}>
                        <FleetStatus fleet={fleet ?? {}} loading={fleetLoading} />
                    </Col>
                </Row>

                {isTripSite && (
                    <ReconciliationPanel
                        data={reconciliationData?.reconciliation ?? null}
                        productionSource={reconciliationData?.production_source ?? 'parallel'}
                        loading={reconciliationLoading}
                    />
                )}
            </Space>
        </AuthenticatedLayout>
    );
}

function TripKpiFetcher({
    siteId,
    date,
    material,
    label,
}: {
    siteId: number;
    date: string;
    material: string;
    label: string;
}) {
    const { data, isLoading } = useQuery({
        queryKey: ['hourly-kpi', { site_id: siteId, date, material }],
        queryFn: async () => {
            const { data: kpi } = await axios.get<HourlyKpiData>('/api/hourly-dashboard/kpi', {
                params: { site_id: siteId, date, material },
            });
            return kpi;
        },
        refetchInterval: 60_000,
    });

    const defaultKpi: HourlyKpiData = {
        dtd: { actual: 0, plan: null, achievement: null },
        mtd: { actual: 0, plan: null, achievement: null },
        current_hour: null,
        hourly_target: null,
    };

    return (
        <HourlyKpiCard
            materialLabel={label}
            dtd={data?.dtd ?? defaultKpi.dtd}
            mtd={data?.mtd ?? defaultKpi.mtd}
            currentHour={data?.current_hour ?? null}
            loading={isLoading}
        />
    );
}
