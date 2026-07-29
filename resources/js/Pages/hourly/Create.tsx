import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dayjs } from '@/lib/date';
import type { Site } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, DatePicker, Form, Select, Space } from 'antd';

interface ShiftOption {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
}

interface HourlyCreateProps {
    sites: Site[];
    shifts: ShiftOption[];
    materials: Record<string, string>;
}

export default function Create({ sites, shifts, materials }: HourlyCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        production_date: dayjs().format('YYYY-MM-DD'),
        site_id: sites[0]?.id ?? null,
        material_type: 'limestone',
        shift_id: shifts[0]?.id ?? null,
    });

    const submit = () => {
        post(route('hourly.store'));
    };

    return (
        <AuthenticatedLayout title="Buat CCR Hourly Entry">
            <Head title="Buat CCR Hourly Entry" />
            <Card style={{ maxWidth: 480 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Tanggal" required validateStatus={errors.production_date ? 'error' : undefined}>
                        <DatePicker
                            style={{ width: '100%' }}
                            value={dayjs(data.production_date)}
                            onChange={(d) => setData('production_date', d?.format('YYYY-MM-DD') ?? '')}
                        />
                    </Form.Item>
                    <Form.Item label="Site" required validateStatus={errors.site_id ? 'error' : undefined}>
                        <Select
                            value={data.site_id}
                            onChange={(v) => setData('site_id', v)}
                            options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                        />
                    </Form.Item>
                    <Form.Item label="Material" required>
                        <Select
                            value={data.material_type}
                            onChange={(v) => setData('material_type', v)}
                            options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                        />
                    </Form.Item>
                    <Form.Item label="Shift" required>
                        <Select
                            value={data.shift_id}
                            onChange={(v) => setData('shift_id', v)}
                            options={shifts.map((s) => ({ value: s.id, label: s.name }))}
                        />
                    </Form.Item>
                    <Space>
                        <Button type="primary" htmlType="submit" loading={processing}>
                            Buat & Lanjut Input
                        </Button>
                    </Space>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
