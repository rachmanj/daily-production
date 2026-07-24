import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Card, Typography } from 'antd';

const { Paragraph, Title } = Typography;

export default function Dashboard() {
    const { auth, activeSite } = usePage<PageProps>().props;

    return (
        <AuthenticatedLayout title="Dashboard">
            <Head title="Dashboard" />

            <Card>
                <Title level={3}>Selamat datang, {auth.user.name}</Title>
                <Paragraph>
                    Anda sedang mengakses site:{' '}
                    <strong>
                        {activeSite.code} — {activeSite.name}
                    </strong>
                </Paragraph>
                <Paragraph type="secondary">
                    ARKA MineOps — Integrated Mining Operations Dashboard
                </Paragraph>
            </Card>
        </AuthenticatedLayout>
    );
}
