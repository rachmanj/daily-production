import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Select, Switch } from 'antd';

interface CreateProps {
    roleOptions: Record<string, string>;
    sites: { id: number; code: string; name: string }[];
}

export default function Create({ roleOptions, sites }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        role: Object.keys(roleOptions)[0] ?? '',
        is_active: true,
        site_ids: [] as number[],
    });

    const submit = () => {
        post(route('users.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah Pengguna">
            <Head title="Tambah Pengguna" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Nama" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Email" required validateStatus={errors.email ? 'error' : ''} help={errors.email}>
                        <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Username" validateStatus={errors.username ? 'error' : ''} help={errors.username || 'Opsional. Hanya huruf, angka, dash, dan underscore.'}>
                        <Input value={data.username} onChange={(e) => setData('username', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Password" required validateStatus={errors.password ? 'error' : ''} help={errors.password}>
                        <Input.Password value={data.password} onChange={(e) => setData('password', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Konfirmasi Password" required>
                        <Input.Password
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </Form.Item>
                    <Form.Item label="Role" required validateStatus={errors.role ? 'error' : ''} help={errors.role}>
                        <Select
                            value={data.role}
                            onChange={(v) => setData('role', v)}
                            options={Object.entries(roleOptions).map(([value, label]) => ({
                                value,
                                label,
                            }))}
                        />
                    </Form.Item>
                    <Form.Item label="Site Akses">
                        <Select
                            mode="multiple"
                            value={data.site_ids}
                            onChange={(v) => setData('site_ids', v)}
                            options={sites.map((s) => ({
                                value: s.id,
                                label: `${s.code} — ${s.name}`,
                            }))}
                            placeholder="Kosongkan untuk akses semua site"
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
