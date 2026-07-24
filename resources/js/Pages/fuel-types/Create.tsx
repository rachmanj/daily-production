import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Switch } from 'antd';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        is_active: true,
    });

    const submit = () => {
        post(route('fuel-types.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah Jenis BBM">
            <Head title="Tambah Jenis BBM" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Nama" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
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
