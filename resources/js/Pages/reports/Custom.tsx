import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import type { Pit } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button, Card, DatePicker, Form, Select } from 'antd';
import { FileExcelOutlined, FilePdfOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { useState } from 'react';

interface CustomReportProps {
    sites: Site[];
    pits: Pit[];
}

export default function Custom({ sites, pits }: CustomReportProps) {
    const [siteId, setSiteId] = useState(sites[0]?.id);
    const [pitId, setPitId] = useState<number | undefined>();
    const [dateFrom, setDateFrom] = useState(dayjs().startOf('month').format('YYYY-MM-DD'));
    const [dateTo, setDateTo] = useState(dayjs().format('YYYY-MM-DD'));
    const [submitting, setSubmitting] = useState(false);

    const filteredPits = pits.filter((p) => p.site_id === siteId);

    const generate = (format: 'pdf' | 'excel') => {
        setSubmitting(true);
        router.post(
            route('reports.customGenerate'),
            {
                site_id: siteId,
                pit_id: pitId,
                date_from: dateFrom,
                date_to: dateTo,
                format,
            },
            { onFinish: () => setSubmitting(false) },
        );
    };

    return (
        <AuthenticatedLayout title="Laporan Custom">
            <Head title="Laporan Custom" />
            <Card style={{ maxWidth: 560 }}>
                <Form layout="vertical">
                    <Form.Item label="Site">
                        <Select
                            value={siteId}
                            onChange={(v) => {
                                setSiteId(v);
                                setPitId(undefined);
                            }}
                            options={sites.map((s) => ({
                                value: s.id,
                                label: `${s.code} — ${s.name}`,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="PIT (opsional)">
                        <Select
                            allowClear
                            value={pitId}
                            onChange={setPitId}
                            options={filteredPits.map((p) => ({ value: p.id, label: p.code }))}
                        />
                    </Form.Item>
                    <Form.Item label="Dari Tanggal">
                        <DatePicker
                            style={{ width: '100%' }}
                            value={dayjs(dateFrom)}
                            onChange={(d) => setDateFrom(d?.format('YYYY-MM-DD') ?? dateFrom)}
                        />
                    </Form.Item>
                    <Form.Item label="Sampai Tanggal">
                        <DatePicker
                            style={{ width: '100%' }}
                            value={dayjs(dateTo)}
                            onChange={(d) => setDateTo(d?.format('YYYY-MM-DD') ?? dateTo)}
                        />
                    </Form.Item>
                    <Form.Item>
                        <Button
                            type="primary"
                            loading={submitting}
                            icon={<FilePdfOutlined />}
                            onClick={() => generate('pdf')}
                            style={{ marginRight: 8 }}
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
                    </Form.Item>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
