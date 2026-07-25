import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button, Card, Col, DatePicker, Form, Row, Select, Space } from 'antd';
import { FileExcelOutlined, FilePdfOutlined, FormOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { useState } from 'react';

interface ReportsIndexProps {
    sites: Site[];
}

export default function Index({ sites }: ReportsIndexProps) {
    const [siteId, setSiteId] = useState(sites[0]?.id);
    const [date, setDate] = useState(dayjs().format('YYYY-MM-DD'));

    const dailyUrl = (format: string) =>
        `${route('reports.daily')}?site_id=${siteId}&date=${date}&format=${format}`;

    return (
        <AuthenticatedLayout title="Reports">
            <Head title="Reports" />
            <Row gutter={[16, 16]}>
                <Col xs={24} md={12}>
                    <Card title="Laporan Harian (DPR)">
                        <Form layout="vertical">
                            <Form.Item label="Site">
                                <Select
                                    value={siteId}
                                    onChange={setSiteId}
                                    options={sites.map((s) => ({
                                        value: s.id,
                                        label: `${s.code} — ${s.name}`,
                                    }))}
                                />
                            </Form.Item>
                            <Form.Item label="Tanggal">
                                <DatePicker
                                    style={{ width: '100%' }}
                                    value={dayjs(date)}
                                    onChange={(d) => setDate(d?.format('YYYY-MM-DD') ?? date)}
                                />
                            </Form.Item>
                            <Space>
                                <Button type="primary" icon={<FilePdfOutlined />} href={dailyUrl('pdf')}>
                                    Download PDF
                                </Button>
                                <Button icon={<FileExcelOutlined />} href={dailyUrl('excel')}>
                                    Download Excel
                                </Button>
                            </Space>
                        </Form>
                    </Card>
                </Col>
                <Col xs={24} md={12}>
                    <Card title="Laporan Custom">
                        <p>Generate laporan dengan rentang tanggal dan filter PIT.</p>
                        <Link href={route('reports.custom')}>
                            <Button type="primary" icon={<FormOutlined />}>
                                Buat Laporan Custom
                            </Button>
                        </Link>
                    </Card>
                </Col>
                <Col xs={24} md={12}>
                    <Card title="Laporan Konsolidasi">
                        <p>Ringkasan multi-site dan multi-periode: produksi, fuel, deployment, dan info site.</p>
                        <Link href={route('reports.consolidated')}>
                            <Button type="primary" icon={<FormOutlined />}>
                                Buka Laporan Konsolidasi
                            </Button>
                        </Link>
                    </Card>
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
