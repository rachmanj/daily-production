import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Select, TimePicker } from 'antd';
import dayjs from 'dayjs';

interface EditProps {
    shift: {
        id: number;
        name: string;
        start_time: string;
        end_time: string;
    };
    nameOptions: Record<string, string>;
}

export default function Edit({ shift, nameOptions }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: shift.name,
        start_time: shift.start_time,
        end_time: shift.end_time,
    });

    const submit = () => {
        put(route('shifts.update', shift.id));
    };

    return (
        <AuthenticatedLayout title="Edit Shift">
            <Head title="Edit Shift" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Nama Shift" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Select
                            value={data.name}
                            onChange={(v) => setData('name', v)}
                            options={Object.entries(nameOptions).map(([value, label]) => ({
                                value,
                                label,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="Jam Mulai" required validateStatus={errors.start_time ? 'error' : ''} help={errors.start_time}>
                        <TimePicker
                            format="HH:mm"
                            value={dayjs(data.start_time, 'HH:mm')}
                            onChange={(v) => setData('start_time', v?.format('HH:mm') ?? '')}
                            style={{ width: '100%' }}
                        />
                    </Form.Item>
                    <Form.Item label="Jam Selesai" required validateStatus={errors.end_time ? 'error' : ''} help={errors.end_time}>
                        <TimePicker
                            format="HH:mm"
                            value={dayjs(data.end_time, 'HH:mm')}
                            onChange={(v) => setData('end_time', v?.format('HH:mm') ?? '')}
                            style={{ width: '100%' }}
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
