import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Button, List, Space, Typography } from 'antd';
import dayjs from 'dayjs';

interface NotificationItem {
    id: string;
    type: string;
    data: { message?: string; title?: string };
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    current_page: number;
    total: number;
}

interface NotificationsIndexProps {
    notifications: PaginatedNotifications;
}

const { Text } = Typography;

export default function Index({ notifications }: NotificationsIndexProps) {
    const markRead = (id: string) => {
        router.post(route('notifications.read', id), {}, { preserveScroll: true });
    };

    const markAllRead = () => {
        router.post(route('notifications.readAll'));
    };

    return (
        <AuthenticatedLayout title="Notifikasi">
            <Head title="Notifikasi" />
            <Space style={{ marginBottom: 16 }}>
                <Button onClick={markAllRead}>Tandai Semua Dibaca</Button>
            </Space>
            <List
                itemLayout="horizontal"
                dataSource={notifications.data}
                renderItem={(item) => (
                    <List.Item
                        style={{
                            background: item.read_at ? undefined : '#f6ffed',
                            padding: '12px 16px',
                            marginBottom: 8,
                            borderRadius: 6,
                        }}
                        actions={
                            !item.read_at
                                ? [
                                      <Button key="read" type="link" onClick={() => markRead(item.id)}>
                                          Tandai dibaca
                                      </Button>,
                                  ]
                                : []
                        }
                    >
                        <List.Item.Meta
                            title={item.data.title ?? item.type}
                            description={
                                <Space direction="vertical" size={0}>
                                    <Text>{item.data.message ?? '—'}</Text>
                                    <Text type="secondary" style={{ fontSize: 12 }}>
                                        {dayjs(item.created_at).format('DD MMM YYYY HH:mm')}
                                    </Text>
                                </Space>
                            }
                        />
                    </List.Item>
                )}
            />
        </AuthenticatedLayout>
    );
}
