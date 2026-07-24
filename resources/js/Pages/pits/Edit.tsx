import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Select, Switch } from 'antd';

interface EditProps {
    pit: {
        id: number;
        site_id: number;
        code: string;
        owner: string;
        is_active: boolean;
    };
    sites: { id: number; code: string; name: string }[];
    ownerOptions: Record<string, string>;
}

export default function Edit({ pit, sites, ownerOptions }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        site_id: pit.site_id,
        code: pit.code,
        owner: pit.owner,
        is_active: pit.is_active,
    });

    const submit = () => {
        put(route('pits.update', pit.id));
    };

    return (
        <AuthenticatedLayout title="Edit PIT">
            <Head title="Edit PIT" />
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
