import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Select, Switch } from 'antd';

interface CreateProps {
    sites: { id: number; code: string; name: string }[];
    ownerOptions: Record<string, string>;
}

export default function Create({ sites, ownerOptions }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        site_id: sites[0]?.id ?? null,
        code: '',
        owner: Object.keys(ownerOptions)[0] ?? '',
        is_active: true,
    });

    const submit = () => {
        post(route('pits.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah PIT">
            <Head title="Tambah PIT" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Site" required validateStatus={errors.site_id ? 'error' : ''} help={errors.site_id}>
                        <Select
                            value={data.site_id}
                            onChange={(v) => setData('site_id', v)}
                            options={sites.map((s) => ({
                                value: s.id,
                                label: `${s.code} — ${s.name}`,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="Kode PIT" required validateStatus={errors.code ? 'error' : ''} help={errors.code}>
                        <Input value={data.code} onChange={(e) => setData('code', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Owner" required validateStatus={errors.owner ? 'error' : ''} help={errors.owner}>
                        <Select
                            value={data.owner}
                            onChange={(v) => setData('owner', v)}
                            options={Object.entries(ownerOptions).map(([value, label]) => ({
                                value,
                                label,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="Aktif">
                        <Switch checked={data.is_active} onChange={(v) => setData('is_active', v)} />
                    </Form.Item>
                    <Button type="primary" htmlType="submit" loading={processing}>
                        Simpan
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
