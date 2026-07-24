import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Space, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { EditOutlined } from '@ant-design/icons';

interface RoleRow {
    id: number;
    name: string;
    permissions: string[];
}

interface RolesIndexProps {
    roles: RoleRow[];
}

export default function Index({ roles }: RolesIndexProps) {
    const columns: ProColumns<RoleRow>[] = [
        { title: 'Role', dataIndex: 'name', key: 'name' },
        {
            title: 'Permissions',
            dataIndex: 'permissions',
            key: 'permissions',
            render: (_, record) => (
                <Space wrap>
                    {record.permissions.map((p) => (
                        <Tag key={p}>{p}</Tag>
                    ))}
                </Space>
            ),
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Link href={route('roles.edit', record.id)}>
                    <Button type="link" icon={<EditOutlined />}>
                        Edit
                    </Button>
                </Link>
            ),
        },
    ];

    return (
        <AuthenticatedLayout title="Roles & Permissions">
            <Head title="Roles" />
            <DataTable<RoleRow>
                headerTitle="Daftar Role"
                dataSource={roles}
                columns={columns}
            />
        </AuthenticatedLayout>
    );
}
