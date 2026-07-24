import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Switch } from 'antd';

interface Site {
    id: number;
    code: string;
    name: string;
    location?: string;
    is_active: boolean;
}

interface EditProps {
    site: Site;
}

export default function Edit({ site }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        code: site.code,
        name: site.name,
        location: site.location ?? '',
        is_active: site.is_active,
    });

    const submit = () => {
        put(route('sites.update', site.id));
    };

    return (
        <AuthenticatedLayout title="Edit Site">
            <Head title="Edit Site" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Kode" required validateStatus={errors.code ? 'error' : ''} help={errors.code}>
                        <Input value={data.code} onChange={(e) => setData('code', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Nama" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Lokasi" validateStatus={errors.location ? 'error' : ''} help={errors.location}>
                        <Input value={data.location} onChange={(e) => setData('location', e.target.value)} />
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
