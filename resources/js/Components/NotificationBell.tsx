import { BellOutlined } from '@ant-design/icons';
import { Link, usePage } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { Badge, Button } from 'antd';
import axios from 'axios';

interface NotificationItem {
    id: string;
    read_at: string | null;
    data: { message?: string };
}

export default function NotificationBell() {
    const { version } = usePage();

    const { data: unreadCount = 0 } = useQuery({
        queryKey: ['notifications-unread', version],
        queryFn: async () => {
            const response = await axios.get(route('notifications.index'), {
                headers: {
                    'X-Inertia': 'true',
                    'X-Inertia-Version': version,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                withCredentials: true,
            });
            const notifications: { data: NotificationItem[] } =
                response.data?.props?.notifications ?? { data: [] };
            return notifications.data.filter((n) => !n.read_at).length;
        },
        refetchInterval: 60_000,
        retry: false,
    });

    return (
        <Link href={route('notifications.index')}>
            <Badge count={unreadCount} size="small">
                <Button type="text" icon={<BellOutlined style={{ fontSize: 18 }} />} />
            </Badge>
        </Link>
    );
}
