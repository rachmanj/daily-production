import NotificationBell from '@/Components/NotificationBell';
import OfflineIndicator from '@/Components/offline/OfflineIndicator';
import SyncButton from '@/Components/offline/SyncButton';
import SiteSelector from '@/Components/SiteSelector';
import {
    AimOutlined,
    DashboardOutlined,
    FileTextOutlined,
    FireOutlined,
    FormOutlined,
    LineChartOutlined,
    LogoutOutlined,
    SettingOutlined,
    ShoppingCartOutlined,
    ToolOutlined,
    UserOutlined,
} from '@ant-design/icons';
import { Link, router, usePage } from '@inertiajs/react';
import type { MenuProps } from 'antd';
import { Avatar, Dropdown, Layout, Menu, Space, Typography, message } from 'antd';
import { PropsWithChildren, ReactNode, useEffect, useMemo } from 'react';
import { PageProps } from '@/types';

const { Header, Sider, Content } = Layout;
const { Text } = Typography;

type AuthenticatedLayoutProps = PropsWithChildren<{
    title?: string;
    header?: ReactNode;
}>;

export default function AuthenticatedLayout({
    children,
    title,
    header,
}: AuthenticatedLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const user = auth.user;

    useEffect(() => {
        if (flash?.success) {
            message.success(flash.success);
        }
        if (flash?.error) {
            message.error(flash.error);
        }
    }, [flash]);

    const menuItems: MenuProps['items'] = useMemo(
        () => [
            {
                key: 'dashboard',
                icon: <DashboardOutlined />,
                label: <Link href={route('dashboard')}>Dashboard</Link>,
            },
            {
                key: 'data-entry',
                icon: <FormOutlined />,
                label: <Link href={route('daily-entries.index')}>Daily Entry</Link>,
            },
            {
                key: 'produksi',
                icon: <LineChartOutlined />,
                label: <Link href={route('dashboard')}>Produksi</Link>,
            },
            {
                key: 'fuel',
                icon: <FireOutlined />,
                label: <Link href={route('dashboard.fuel')}>Fuel</Link>,
            },
            {
                key: 'equipment',
                icon: <ToolOutlined />,
                label: <Link href={route('equipment-assignments.index')}>Equipment</Link>,
            },
            {
                key: 'plan',
                icon: <AimOutlined />,
                label: 'Plan',
                children: [
                    {
                        key: 'monthly-plans',
                        label: <Link href={route('monthly-plans.index')}>Monthly Plan</Link>,
                    },
                    {
                        key: 'variance',
                        label: <Link href={route('variance.index')}>Variance</Link>,
                    },
                ],
            },
            {
                key: 'procurement',
                icon: <ShoppingCartOutlined />,
                label: <Link href={route('procurement.index')}>Procurement</Link>,
            },
            {
                key: 'reports',
                icon: <FileTextOutlined />,
                label: <Link href={route('reports.index')}>Reports</Link>,
            },
            {
                key: 'notifications',
                icon: <FormOutlined />,
                label: <Link href={route('notifications.index')}>Notifikasi</Link>,
            },
            {
                key: 'master',
                icon: <SettingOutlined />,
                label: 'Master Data',
                children: [
                    {
                        key: 'master-sites',
                        label: <Link href={route('sites.index')}>Sites & PITs</Link>,
                    },
                    {
                        key: 'master-pits',
                        label: <Link href={route('pits.index')}>PITs</Link>,
                    },
                    {
                        key: 'master-shifts',
                        label: <Link href={route('shifts.index')}>Shifts</Link>,
                    },
                    {
                        key: 'master-fuel-types',
                        label: <Link href={route('fuel-types.index')}>Jenis BBM</Link>,
                    },
                    {
                        key: 'master-fuel-prices',
                        label: <Link href={route('fuel-prices.index')}>Harga BBM</Link>,
                    },
                    {
                        key: 'master-users',
                        label: <Link href={route('users.index')}>Pengguna</Link>,
                    },
                    {
                        key: 'master-roles',
                        label: <Link href={route('roles.index')}>Roles</Link>,
                    },
                    {
                        key: 'master-equipment',
                        label: (
                            <Link href={route('equipment-assignments.index')}>
                                Equipment Assignment
                            </Link>
                        ),
                    },
                ],
            },
        ],
        [],
    );

    const userMenuItems: MenuProps['items'] = [
        {
            key: 'profile',
            label: <Link href={route('profile.edit')}>Profil</Link>,
        },
        {
            type: 'divider',
        },
        {
            key: 'logout',
            icon: <LogoutOutlined />,
            label: 'Keluar',
            onClick: () => router.post(route('logout')),
        },
    ];

    return (
        <Layout style={{ minHeight: '100vh' }}>
            <Sider breakpoint="lg" collapsedWidth="0" theme="dark">
                <div
                    style={{
                        height: 64,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: '#fff',
                        fontWeight: 600,
                        fontSize: 16,
                    }}
                >
                    ARKA MineOps
                </div>
                <Menu theme="dark" mode="inline" defaultSelectedKeys={['dashboard']} items={menuItems} />
            </Sider>
            <Layout>
                <OfflineIndicator />
                <Header
                    style={{
                        background: '#fff',
                        padding: '0 24px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                    }}
                >
                    <Text strong style={{ fontSize: 18 }}>
                        {title ?? (typeof header === 'string' ? header : 'Dashboard')}
                    </Text>
                    <Space size="middle">
                        <SyncButton />
                        <SiteSelector />
                        <NotificationBell />
                        <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
                            <Space style={{ cursor: 'pointer' }}>
                                <Avatar icon={<UserOutlined />} />
                                <Text>{user.name}</Text>
                            </Space>
                        </Dropdown>
                    </Space>
                </Header>
                <Content style={{ margin: 24 }}>{children}</Content>
            </Layout>
        </Layout>
    );
}
