import KpiCard from '@/Components/dashboard/KpiCard';
import SiteComparisonChart from '@/Components/dashboard/SiteComparisonChart';
import TrendChart from '@/Components/dashboard/TrendChart';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import type { ConsolidatedSummary } from '@/types/dashboard';
import { Head, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Button, Card, Col, DatePicker, Form, Row, Select, Space, Table } from 'antd';
import { FileExcelOutlined, FilePdfOutlined } from '@ant-design/icons';
import axios from 'axios';
import dayjs from 'dayjs';
import { useMemo, useState } from 'react';

const { RangePicker } = DatePicker;

interface ConsolidatedReportProps {
    sites: Site[];
}

async function fetchConsolidated(
    siteIds: number[],
    dateFrom: string,
    dateTo: string,
): Promise<ConsolidatedSummary> {
    const { data } = await axios.get('/api/dashboard/consolidated', {
        params: {
            site_ids: siteIds,
            date_from: dateFrom,
            date_to: dateTo,
        },
        withCredentials: true,
    });
    return data;
}

export default function Consolidated({ sites }: ConsolidatedReportProps) {
    const [siteIds, setSiteIds] = useState<number[]>(sites.map((s) => s.id));
    const [dateFrom, setDateFrom] = useState(dayjs().startOf('month').format('YYYY-MM-DD'));
    const [dateTo, setDateTo] = useState(dayjs().format('YYYY-MM-DD'));
    const [submitting, setSubmitting] = useState(false);

    const { data, isLoading, refetch } = useQuery<ConsolidatedSummary>({
        queryKey: ['consolidated-report', siteIds, dateFrom, dateTo],
        queryFn: () => fetchConsolidated(siteIds, dateFrom, dateTo),
        enabled: sites.length > 0,
    });

    const siteOptions = useMemo(
        () => sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` })),
        [sites],
    );

    const selectAllSites = () => setSiteIds(sites.map((s) => s.id));

    const applyFilters = () => {
        refetch();
    };

    const generate = (format: 'pdf' | 'excel') => {
        setSubmitting(true);
        router.post(
            route('reports.consolidatedGenerate'),
            {
                site_ids: siteIds,
                date_from: dateFrom,
                date_to: dateTo,
                format,
            },
            { onFinish: () => setSubmitting(false) },
        );
    };

    const columns = [
        { title: 'Site', dataIndex: 'site_code', key: 'site_code' },
        { title: 'OB (Bcm)', dataIndex: 'ob', key: 'ob', render: (v: number) => v.toLocaleString() },
        { title: 'Coal (Ton)', dataIndex: 'coal', key: 'coal', render: (v: number) => v.toLocaleString() },
        { title: 'Hauling (Ton)', dataIndex: 'hauling', key: 'hauling', render: (v: number) => v.toLocaleString() },
        { title: 'Fuel (L)', dataIndex: 'fuel_liters', key: 'fuel_liters', render: (v: number) => v.toLocaleString() },
        { title: 'SR', dataIndex: 'sr', key: 'sr', render: (v: number | null) => (v != null ? v.toFixed(2) : '-') },
    ];

    return (
        <AuthenticatedLayout title="Laporan Konsolidasi">
            <Head title="Laporan Konsolidasi" />
            <Card style={{ marginBottom: 16 }}>
                <Form layout="vertical">
                    <Row gutter={16}>
                        <Col xs={24} md={12}>
                            <Form.Item label="Site">
                                <Space direction="vertical" style={{ width: '100%' }}>
                                    <Select
                                        mode="multiple"
                                        allowClear
                                        style={{ width: '100%' }}
                                        value={siteIds}
                                        onChange={setSiteIds}
                                        options={siteOptions}
                                        placeholder="Pilih site (kosong = semua site)"
                                    />
                                    <Button size="small" onClick={selectAllSites}>
                                        Semua Site
                                    </Button>
                                </Space>
                            </Form.Item>
                        </Col>
                        <Col xs={24} md={12}>
                            <Form.Item label="Periode">
                                <RangePicker
                                    style={{ width: '100%' }}
                                    value={[dayjs(dateFrom), dayjs(dateTo)]}
                                    onChange={(dates) => {
                                        if (dates?.[0]) {
                                            setDateFrom(dates[0].format('YYYY-MM-DD'));
                                        }
                                        if (dates?.[1]) {
                                            setDateTo(dates[1].format('YYYY-MM-DD'));
                                        }
                                    }}
                                />
                            </Form.Item>
                        </Col>
                    </Row>
                    <Space wrap>
                        <Button type="primary" onClick={applyFilters} loading={isLoading}>
                            Terapkan
                        </Button>
                        <Button
                            type="primary"
                            loading={submitting}
                            icon={<FilePdfOutlined />}
                            onClick={() => generate('pdf')}
                        >
                            Generate PDF
                        </Button>
                        <Button
                            loading={submitting}
                            icon={<FileExcelOutlined />}
                            onClick={() => generate('excel')}
                        >
                            Generate Excel
                        </Button>
                    </Space>
                </Form>
            </Card>

            <Row gutter={[16, 16]}>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard title="Total OB" value={data?.totals.ob ?? 0} unit="Bcm" precision={0} />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard title="Total Coal" value={data?.totals.coal ?? 0} unit="Ton" precision={0} />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard title="Stripping Ratio" value={data?.totals.sr ?? 0} precision={2} />
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <KpiCard title="Total Fuel" value={data?.totals.fuel_liters ?? 0} unit="L" precision={0} />
                </Col>
            </Row>

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24} lg={14}>
                    <TrendChart data={data?.trend ?? []} loading={isLoading} />
                </Col>
                <Col xs={24} lg={10}>
                    <SiteComparisonChart data={data?.sites ?? []} loading={isLoading} />
                </Col>
            </Row>

            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
                <Col xs={24}>
                    <Card title="Ringkasan per Site" size="small">
                        <Table
                            rowKey="site_id"
                            loading={isLoading}
                            dataSource={data?.sites ?? []}
                            columns={columns}
                            pagination={false}
                            size="small"
                        />
                    </Card>
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
