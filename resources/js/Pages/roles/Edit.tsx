import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Checkbox, Form, Space } from 'antd';

interface EditProps {
    role: {
        id: number;
        name: string;
        permissions: string[];
    };
    allPermissions: string[];
}

export default function Edit({ role, allPermissions }: EditProps) {
    const { data, setData, put, processing } = useForm({
        permissions: role.permissions,
    });

    const submit = () => {
        put(route('roles.update', role.id));
    };

    const togglePermission = (permission: string, checked: boolean) => {
        if (checked) {
            setData('permissions', [...data.permissions, permission]);
        } else {
            setData(
                'permissions',
                data.permissions.filter((p) => p !== permission),
            );
        }
    };

    return (
        <AuthenticatedLayout title={`Edit Role: ${role.name}`}>
            <Head title="Edit Role" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Permissions">
                        <Space direction="vertical">
                            {allPermissions.map((permission) => (
                                <Checkbox
                                    key={permission}
                                    checked={data.permissions.includes(permission)}
                                    onChange={(e) => togglePermission(permission, e.target.checked)}
                                >
                                    {permission}
                                </Checkbox>
                            ))}
                        </Space>
                    </Form.Item>
                    <Button type="primary" htmlType="submit" loading={processing}>
                        Simpan
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
