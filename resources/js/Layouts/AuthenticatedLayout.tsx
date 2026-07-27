import NotificationBell from '@/Components/NotificationBell';
import OfflineIndicator from '@/Components/offline/OfflineIndicator';
import SyncButton from '@/Components/offline/SyncButton';
import SiteSelector from '@/Components/SiteSelector';
import ThemeToggle from '@/Components/ThemeToggle';
import {
    AimOutlined,
    DashboardOutlined,
    EllipsisOutlined,
    FileTextOutlined,
    FireOutlined,
    FormOutlined,
    LineChartOutlined,
    LogoutOutlined,
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    SettingOutlined,
    ShoppingCartOutlined,
    ToolOutlined,
    UserOutlined,
} from '@ant-design/icons';
import { Link, router, usePage } from '@inertiajs/react';
import type { MenuProps } from 'antd';
import { Avatar, Button, Dropdown, Drawer, Grid, Layout, Menu, Space, Typography, message, theme } from 'antd';
import { PropsWithChildren, ReactNode, useEffect, useMemo, useState } from 'react';
import { PageProps } from '@/types';
import { resolveSidebarMenu } from '@/lib/navigation';

const { Header, Content } = Layout;
const { Text } = Typography;
const { useBreakpoint } = Grid;

type AuthenticatedLayoutProps = PropsWithChildren<{
    title?: string;
    header?: ReactNode;
}>;

export default function AuthenticatedLayout({
    children,
    title,
    header,
}: AuthenticatedLayoutProps) {
    const { props: { auth, flash }, url } = usePage<PageProps>();
    const user = auth.user;
    const { token } = theme.useToken();
    const screens = useBreakpoint();
    const isMobile = !screens.lg;

    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [mobileDrawerOpen, setMobileDrawerOpen] = useState(false);

    const { selectedKeys, openKeys: routeOpenKeys } = useMemo(
        () => resolveSidebarMenu(url),
        [url],
    );
    const [openKeys, setOpenKeys] = useState(routeOpenKeys);

    useEffect(() => {
        setOpenKeys(routeOpenKeys);
    }, [routeOpenKeys]);

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
                label: <Link href={route('dashboard')} onClick={() => setMobileDrawerOpen(false)}>Dashboard</Link>,
            },
            {
                key: 'data-entry',
                icon: <FormOutlined />,
                label: <Link href={route('daily-entries.index')} onClick={() => setMobileDrawerOpen(false)}>Daily Entry</Link>,
            },
            {
                key: 'fuel',
                icon: <FireOutlined />,
                label: <Link href={route('dashboard.fuel')} onClick={() => setMobileDrawerOpen(false)}>Fuel</Link>,
            },
            {
                key: 'equipment',
                icon: <ToolOutlined />,
                label: <Link href={route('equipment-assignments.index')} onClick={() => setMobileDrawerOpen(false)}>Equipment</Link>,
            },
            { type: 'divider' },
            {
                key: 'master-sites',
                icon: <SettingOutlined />,
                label: <Link href={route('sites.index')} onClick={() => setMobileDrawerOpen(false)}>Sites</Link>,
            },
            {
                key: 'master-pits',
                icon: <SettingOutlined />,
                label: <Link href={route('pits.index')} onClick={() => setMobileDrawerOpen(false)}>PITs</Link>,
            },
            {
                key: 'master-shifts',
                icon: <SettingOutlined />,
                label: <Link href={route('shifts.index')} onClick={() => setMobileDrawerOpen(false)}>Shifts</Link>,
            },
            {
                key: 'master-fuel-types',
                icon: <SettingOutlined />,
                label: <Link href={route('fuel-types.index')} onClick={() => setMobileDrawerOpen(false)}>Jenis BBM</Link>,
            },
            {
                key: 'master-fuel-prices',
                icon: <SettingOutlined />,
                label: <Link href={route('fuel-prices.index')} onClick={() => setMobileDrawerOpen(false)}>Harga BBM</Link>,
            },
            {
                key: 'master-users',
                icon: <SettingOutlined />,
                label: <Link href={route('users.index')} onClick={() => setMobileDrawerOpen(false)}>Pengguna</Link>,
            },
            { type: 'divider' },
            {
                key: 'monthly-plans',
                icon: <AimOutlined />,
                label: <Link href={route('monthly-plans.index')} onClick={() => setMobileDrawerOpen(false)}>Monthly Plan</Link>,
            },
            {
                key: 'variance',
                icon: <AimOutlined />,
                label: <Link href={route('variance.index')} onClick={() => setMobileDrawerOpen(false)}>Variance</Link>,
            },
            {
                key: 'procurement',
                icon: <ShoppingCartOutlined />,
                label: <Link href={route('procurement.index')} onClick={() => setMobileDrawerOpen(false)}>Procurement</Link>,
            },
            {
                key: 'reports',
                icon: <FileTextOutlined />,
                label: <Link href={route('reports.index')} onClick={() => setMobileDrawerOpen(false)}>Reports</Link>,
            },
            {
                key: 'notifications',
                icon: <FormOutlined />,
                label: <Link href={route('notifications.index')} onClick={() => setMobileDrawerOpen(false)}>Notifikasi</Link>,
            },
        ],
        [],
    );
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

    const sidebarMenu = (
        <Menu
            theme="dark"
            mode="inline"
            selectedKeys={selectedKeys}
            openKeys={openKeys}
            onOpenChange={setOpenKeys}
            items={menuItems}
            style={{ borderRight: 0 }}
        />
    );

    return (
        <Layout style={{ minHeight: '100vh' }}>
            {/* Desktop sidebar */}
            {!isMobile && (
                <Layout.Sider
                    width={240}
                    theme="dark"
                    style={{
                        overflow: 'auto',
                        height: '100vh',
                        position: 'fixed',
                        left: 0,
                        top: 0,
                        bottom: 0,
                        zIndex: 10,
                    }}
                >
                    <div
                        style={{
                            height: 64,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: '#fff',
                            fontWeight: 700,
                            fontSize: 16,
                            letterSpacing: 1,
                            borderBottom: '1px solid rgba(255,255,255,0.1)',
                        }}
                    >
                        ⛏️ ARKA MineOps
                    </div>
                    {sidebarMenu}
                </Layout.Sider>
            )}

            {/* Mobile drawer */}
            {isMobile && (
                <Drawer
                    placement="left"
                    open={mobileDrawerOpen}
                    onClose={() => setMobileDrawerOpen(false)}
                    width={260}
                    styles={{
                        body: { padding: 0, background: '#001529' },
                        header: { background: '#001529', borderBottom: '1px solid rgba(255,255,255,0.1)' },
                    }}
                    title={
                        <Text strong style={{ color: '#fff', fontSize: 16 }}>
                            ⛏️ ARKA MineOps
                        </Text>
                    }
                    closeIcon={<span style={{ color: '#fff' }}>✕</span>}
                >
                    {sidebarMenu}
                </Drawer>
            )}

            <Layout style={{ marginLeft: isMobile ? 0 : 240 }}>
                <OfflineIndicator />
                <Header
                    style={{
                        background: token.colorBgContainer,
                        padding: isMobile ? '0 12px' : '0 24px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        boxShadow: '0 1px 4px rgba(0,0,0,0.05)',
                        position: 'sticky',
                        top: 0,
                        zIndex: 9,
                        height: 56,
                    }}
                >
                    {/* Left side: hamburger + title */}
                    <Space size="small">
                        {isMobile && (
                            <Button
                                type="text"
                                icon={<MenuUnfoldOutlined />}
                                onClick={() => setMobileDrawerOpen(true)}
                            />
                        )}
                        <Text strong style={{ fontSize: isMobile ? 15 : 18, whiteSpace: 'nowrap' }}>
                            {title ?? (typeof header === 'string' ? header : 'Dashboard')}
                        </Text>
                    </Space>

                    {/* Right side: actions */}
                    <Space size={isMobile ? 'small' : 'middle'} wrap={false}>
                        {isMobile ? (
                            <>
                                <NotificationBell />
                                <Dropdown
                                    menu={{
                                        items: [
                                            ...userMenuItems,
                                            { type: 'divider' },
                                            { key: 'site-select', label: <div onClick={e => e.stopPropagation()}><SiteSelector /></div> },
                                            { key: 'theme', label: 'Toggle Theme' },
                                        ],
                                    }}
                                    placement="bottomRight"
                                    trigger={['click']}
                                >
                                    <Button type="text" icon={<EllipsisOutlined />} />
                                </Dropdown>
                            </>
                        ) : (
                            <>
                                <SyncButton />
                                <SiteSelector />
                                <NotificationBell />
                                <ThemeToggle />
                                <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
                                    <Space style={{ cursor: 'pointer' }}>
                                        <Avatar size="small" icon={<UserOutlined />} />
                                        <Text>{user?.name}</Text>
                                    </Space>
                                </Dropdown>
                            </>
                        )}
                    </Space>
                </Header>
                <Content style={{ margin: isMobile ? 12 : 24, minHeight: 280 }}>
                    {children}
                </Content>
            </Layout>
        </Layout>
    );
}
