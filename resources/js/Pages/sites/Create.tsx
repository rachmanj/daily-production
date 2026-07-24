import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Switch } from 'antd';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        location: '',
        is_active: true,
    });

    const submit = () => {
        post(route('sites.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah Site">
            <Head title="Tambah Site" />
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
