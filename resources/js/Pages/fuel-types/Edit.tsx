import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Switch } from 'antd';

interface EditProps {
    fuelType: {
        id: number;
        name: string;
        is_active: boolean;
    };
}

export default function Edit({ fuelType }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: fuelType.name,
        is_active: fuelType.is_active,
    });

    const submit = () => {
        put(route('fuel-types.update', fuelType.id));
    };

    return (
        <AuthenticatedLayout title="Edit Jenis BBM">
            <Head title="Edit Jenis BBM" />
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
