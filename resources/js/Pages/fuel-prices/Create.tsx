import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, DatePicker, Form, InputNumber, Select } from 'antd';
import dayjs from 'dayjs';

interface CreateProps {
    fuelTypes: { id: number; name: string }[];
}

export default function Create({ fuelTypes }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        fuel_type_id: fuelTypes[0]?.id ?? null,
        price_per_liter: 0,
        effective_date: dayjs().format('YYYY-MM-DD'),
    });

    const submit = () => {
        post(route('fuel-prices.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah Harga BBM">
            <Head title="Tambah Harga BBM" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Jenis BBM" required validateStatus={errors.fuel_type_id ? 'error' : ''} help={errors.fuel_type_id}>
                        <Select
                            value={data.fuel_type_id}
                            onChange={(v) => setData('fuel_type_id', v)}
                            options={fuelTypes.map((ft) => ({ value: ft.id, label: ft.name }))}
                        />
                    </Form.Item>
                    <Form.Item label="Harga per Liter (IDR)" required validateStatus={errors.price_per_liter ? 'error' : ''} help={errors.price_per_liter}>
                        <InputNumber
                            value={data.price_per_liter}
                            onChange={(v) => setData('price_per_liter', v ?? 0)}
                            style={{ width: '100%' }}
                            formatter={(v) => `${v}`.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}
                            parser={(v) => Number(v?.replace(/\./g, '') ?? 0)}
                        />
                    </Form.Item>
                    <Form.Item label="Tanggal Efektif" required validateStatus={errors.effective_date ? 'error' : ''} help={errors.effective_date}>
                        <DatePicker
                            value={dayjs(data.effective_date)}
                            onChange={(v) => setData('effective_date', v?.format('YYYY-MM-DD') ?? '')}
                            style={{ width: '100%' }}
                            format="DD/MM/YYYY"
                        />
                    </Form.Item>
                    <Button type="primary" htmlType="submit" loading={processing}>
                        Simpan
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
