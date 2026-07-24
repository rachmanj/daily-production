import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Button, Card, DatePicker, Form, Select } from 'antd';
import dayjs from 'dayjs';

interface CreateProps {
    sites: Site[];
}

export default function Create({ sites }: CreateProps) {
    const { data, setData, processing, errors } = useForm({
        production_date: dayjs().format('YYYY-MM-DD'),
        site_id: sites[0]?.id ?? null as number | null,
    });

    const submit = () => {
        router.post(route('daily-entries.store'), data);
    };

    return (
        <AuthenticatedLayout title="Entry Baru">
            <Head title="Entry Baru" />
            <Card style={{ maxWidth: 480 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item
                        label="Tanggal Produksi"
                        validateStatus={errors.production_date ? 'error' : ''}
                        help={errors.production_date}
                    >
                        <DatePicker
                            style={{ width: '100%' }}
                            value={dayjs(data.production_date)}
                            onChange={(d) => setData('production_date', d?.format('YYYY-MM-DD') ?? '')}
                        />
                    </Form.Item>
                    <Form.Item
                        label="Site"
                        validateStatus={errors.site_id ? 'error' : ''}
                        help={errors.site_id}
                    >
                        <Select
                            value={data.site_id}
                            onChange={(v) => setData('site_id', v)}
                            options={sites.map((s) => ({
                                value: s.id,
                                label: `${s.code} — ${s.name}`,
                            }))}
                        />
                    </Form.Item>
                    <Button type="primary" htmlType="submit" loading={processing} block>
                        Buat Entry
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
