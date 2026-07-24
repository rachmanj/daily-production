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
import { Avatar, Dropdown, Layout, Menu, Select, Space, Typography } from 'antd';
import { PropsWithChildren, ReactNode, useMemo } from 'react';
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
    const { auth, sites, activeSite } = usePage<PageProps>().props;
    const user = auth.user;

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
                label: 'Data Entry',
            },
            {
                key: 'produksi',
                icon: <LineChartOutlined />,
                label: 'Produksi',
            },
            {
                key: 'fuel',
                icon: <FireOutlined />,
                label: 'Fuel',
            },
            {
                key: 'equipment',
                icon: <ToolOutlined />,
                label: 'Equipment',
            },
            {
                key: 'plan',
                icon: <AimOutlined />,
                label: 'Plan',
            },
            {
                key: 'procurement',
                icon: <ShoppingCartOutlined />,
                label: 'Procurement',
            },
            {
                key: 'reports',
                icon: <FileTextOutlined />,
                label: 'Reports',
            },
            {
                key: 'master',
                icon: <SettingOutlined />,
                label: 'Master Data',
                children: [
                    { key: 'master-sites', label: 'Sites & PITs' },
                    { key: 'master-shifts', label: 'Shifts' },
                    { key: 'master-fuel', label: 'Fuel Types/Prices' },
                    { key: 'master-users', label: 'Users & Roles' },
                    { key: 'master-equipment', label: 'Equipment Assignment' },
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

    const handleSiteChange = (siteId: number) => {
        router.post(
            route('active-site.update'),
            { site_id: siteId },
            { preserveScroll: true },
        );
    };

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
                <Menu
                    theme="dark"
                    mode="inline"
                    defaultSelectedKeys={['dashboard']}
                    items={menuItems}
                />
            </Sider>
            <Layout>
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
                        <Select
                            value={activeSite?.id}
                            onChange={handleSiteChange}
                            style={{ minWidth: 220 }}
                            options={sites.map((site) => ({
                                value: site.id,
                                label: `${site.code} — ${site.name}`,
                            }))}
                        />
                        <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
                            <Space style={{ cursor: 'pointer' }}>
                                <Avatar icon={<UserOutlined />} />
                                <Text>{user.name}</Text>
                            </Space>
                        </Dropdown>
                    </Space>
                </Header>
                <Content style={{ margin: 24 }}>
                    {children}
                </Content>
            </Layout>
        </Layout>
    );
}
