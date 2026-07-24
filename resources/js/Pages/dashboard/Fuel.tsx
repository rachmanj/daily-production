import FcrTrendChart from '@/Components/dashboard/FcrTrendChart';
import KpiCard from '@/Components/dashboard/KpiCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import type { FuelByEquipmentRow, KpiData } from '@/types/dashboard';
import { Head, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Col, DatePicker, Row, Select, Space, Table } from 'antd';
import axios from 'axios';
import dayjs from 'dayjs';
import { useState } from 'react';

interface FuelDashboardProps {
    sites: Site[];
    filters: { site_id?: number; date: string };
}

export default function Fuel({ sites, filters }: FuelDashboardProps) {
    const [siteId, setSiteId] = useState(filters.site_id ?? sites[0]?.id);
    const [date, setDate] = useState(filters.date);

    const { data: kpi } = useQuery<KpiData>({
        queryKey: ['dashboard-kpi-fuel', siteId, date],
        queryFn: async () => {
            const { data } = await axios.get('/api/dashboard/kpi', {
                params: { site_id: siteId, date },
                withCredentials: true,
            });
            return data;
        },
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    const { data: fuelByEquipment = [], isLoading } = useQuery<FuelByEquipmentRow[]>({
        queryKey: ['dashboard-fuel-equipment', siteId, date],
        queryFn: async () => {
            const { data } = await axios.get('/api/dashboard/fuel-by-equipment', {
                params: { site_id: siteId, date },
                withCredentials: true,
            });
            return data;
        },
        enabled: !!siteId,
        refetchInterval: 60_000,
    });

    return (
        <AuthenticatedLayout title="Dashboard Fuel">
            <Head title="Dashboard Fuel" />
            <Space wrap style={{ marginBottom: 16 }}>
                <Select
                    style={{ width: 220 }}
                    value={siteId}
                    onChange={setSiteId}
                    options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                />
                <DatePicker value={dayjs(date)} onChange={(d) => setDate(d?.format('YYYY-MM-DD') ?? date)} />
                <Button
                    type="primary"
                    onClick={() => router.get(route('dashboard.fuel'), { site_id: siteId, date })}
                >
                    Terapkan
                </Button>
            </Space>

            <Row gutter={[16, 16]}>
                <Col xs={24} sm={12}>
                    <KpiCard
                        title="Fuel Hari Ini"
                        value={kpi?.fuel.today_liters ?? 0}
                        unit="L"
                        mtd={kpi?.fuel.mtd_liters}
                        precision={0}
                    />
                </Col>
                <Col xs={24} sm={12}>
                    <FcrTrendChart data={fuelByEquipment} loading={isLoading} />
                </Col>
            </Row>

            <Table
                style={{ marginTop: 16 }}
                loading={isLoading}
                dataSource={fuelByEquipment}
                rowKey="equipment_id"
                size="small"
                columns={[
                    { title: 'Unit', dataIndex: 'unit_code', key: 'unit_code' },
                    {
                        title: 'Liter',
                        dataIndex: 'liters',
                        key: 'liters',
                        render: (v: number) => v.toLocaleString('id-ID'),
                    },
                    {
                        title: 'Jam Kerja',
                        dataIndex: 'hours',
                        key: 'hours',
                        render: (v: number) => v.toLocaleString('id-ID'),
                    },
                    {
                        title: 'FCR',
                        dataIndex: 'fcr',
                        key: 'fcr',
                        render: (v: number | null) => (v !== null ? v.toFixed(2) : '—'),
                    },
                ]}
            />
        </AuthenticatedLayout>
    );
}
