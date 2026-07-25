import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Form, Input, Select, Switch } from 'antd';

interface EditProps {
    user: {
        id: number;
        name: string;
        email: string;
        username: string | null;
        is_active: boolean;
        role: string;
        site_ids: number[];
    };
    roleOptions: Record<string, string>;
    sites: { id: number; code: string; name: string }[];
}

export default function Edit({ user, roleOptions, sites }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        username: user.username ?? '',
        password: '',
        password_confirmation: '',
        role: user.role,
        is_active: user.is_active,
        site_ids: user.site_ids,
    });

    const submit = () => {
        put(route('users.update', user.id));
    };

    return (
        <AuthenticatedLayout title="Edit Pengguna">
            <Head title="Edit Pengguna" />
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
                    <Form.Item label="Password Baru" validateStatus={errors.password ? 'error' : ''} help={errors.password || 'Kosongkan jika tidak diubah'}>
                        <Input.Password value={data.password} onChange={(e) => setData('password', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Konfirmasi Password">
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
