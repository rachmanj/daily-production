import FleetStatus from '@/Components/hourly/FleetStatus';
import HourlyHeatmap from '@/Components/hourly/HourlyHeatmap';
import HourlyKpiCard from '@/Components/hourly/HourlyKpiCard';
import HourlyTrendChart from '@/Components/hourly/HourlyTrendChart';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dayjs } from '@/lib/date';
import type { HeatmapData, HourlyKpiData } from '@/types/hourly';
import type { Site } from '@/types';
import { Head } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Col, DatePicker, Row, Select, Space } from 'antd';
import axios from 'axios';
import { useState } from 'react';

interface DashboardProps {
    sites: Site[];
    filters: {
        site_id: number;
        date: string;
        material: string;
    };
    materials: Record<string, string>;
}

export default function Dashboard({ sites, filters: initialFilters, materials }: DashboardProps) {
    const [filters, setFilters] = useState(initialFilters);

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

    return (
        <AuthenticatedLayout title="CCR Dashboard">
            <Head title="CCR Dashboard" />
            <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <Space wrap>
                    <Select
                        value={filters.site_id}
                        onChange={(v) => setFilters((f) => ({ ...f, site_id: v }))}
                        options={sites.map((s) => ({ value: s.id, label: s.code }))}
                        style={{ width: 140 }}
                    />
                    <DatePicker
                        value={dayjs(filters.date)}
                        onChange={(d) => setFilters((f) => ({ ...f, date: d?.format('YYYY-MM-DD') ?? f.date }))}
                    />
                    <Select
                        value={filters.material}
                        onChange={(v) => setFilters((f) => ({ ...f, material: v }))}
                        options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                        style={{ width: 180 }}
                    />
                    <Button onClick={() => exportReport('excel')}>Export Excel</Button>
                    <Button onClick={() => exportReport('pdf')}>Export PDF</Button>
                </Space>

                <HourlyKpiCard
                    materialLabel={materials[filters.material] ?? filters.material}
                    dtd={kpi?.dtd ?? defaultKpi.dtd}
                    mtd={kpi?.mtd ?? defaultKpi.mtd}
                    currentHour={kpi?.current_hour ?? null}
                    loading={kpiLoading}
                />

                <HourlyHeatmap data={heatmap ?? null} loading={heatmapLoading} />

                <Row gutter={[16, 16]}>
                    <Col xs={24} lg={14}>
                        <HourlyTrendChart data={trend ?? []} loading={trendLoading} />
                    </Col>
                    <Col xs={24} lg={10}>
                        <FleetStatus fleet={fleet ?? {}} loading={fleetLoading} />
                    </Col>
                </Row>
            </Space>
        </AuthenticatedLayout>
    );
}
