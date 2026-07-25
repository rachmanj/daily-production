import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Popconfirm, Select, Space, Tag } from 'antd';
import type { ProColumns } from '@ant-design/pro-components';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';

interface UserRow {
    id: number;
    name: string;
    email: string;
    username: string | null;
    is_active: boolean;
    roles: string[];
    sites: { id: number; code: string; name: string }[];
}

interface UsersIndexProps {
    users: UserRow[];
    roleOptions: Record<string, string>;
    filters: { role?: string };
}

export default function Index({ users, roleOptions, filters }: UsersIndexProps) {
    const columns: ProColumns<UserRow>[] = [
        { title: 'Nama', dataIndex: 'name', key: 'name' },
        { title: 'Email', dataIndex: 'email', key: 'email' },
        {
            title: 'Username',
            dataIndex: 'username',
            key: 'username',
            render: (_, record) => record.username ?? '—',
        },
        {
            title: 'Role',
            dataIndex: 'roles',
            key: 'roles',
            render: (_, record) =>
                record.roles.map((role) => (
                    <Tag key={role} color="blue">
                        {roleOptions[role] ?? role}
                    </Tag>
                )),
        },
        {
            title: 'Status',
            dataIndex: 'is_active',
            key: 'is_active',
            render: (_, record) => (
                <Tag color={record.is_active ? 'green' : 'default'}>
                    {record.is_active ? 'Aktif' : 'Nonaktif'}
                </Tag>
            ),
        },
        {
            title: 'Aksi',
            key: 'actions',
            valueType: 'option',
            render: (_, record) => (
                <Space>
                    <Link href={route('users.edit', record.id)}>
                        <Button type="link" icon={<EditOutlined />} />
                    </Link>
                    <Popconfirm
                        title="Hapus pengguna ini?"
                        onConfirm={() => router.delete(route('users.destroy', record.id))}
                    >
                        <Button type="link" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    const handleRoleFilter = (role: string | undefined) => {
        router.get(
            route('users.index'),
            role ? { role } : {},
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout title="Pengguna">
            <Head title="Users" />
            <DataTable<UserRow>
                headerTitle="Daftar Pengguna"
                dataSource={users}
                columns={columns}
                toolBarRender={() => [
                    <Select
                        key="role-filter"
                        allowClear
                        placeholder="Filter Role"
                        style={{ width: 160 }}
                        value={filters.role}
                        onChange={handleRoleFilter}
                        options={Object.entries(roleOptions).map(([value, label]) => ({
                            value,
                            label,
                        }))}
                    />,
                    <Link key="create" href={route('users.create')}>
                        <Button type="primary" icon={<PlusOutlined />}>
                            Tambah Pengguna
                        </Button>
                    </Link>,
                ]}
            />
        </AuthenticatedLayout>
    );
}
