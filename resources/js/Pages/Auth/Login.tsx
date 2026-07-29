import ApplicationLogo from '@/Components/ApplicationLogo';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    Alert,
    Button,
    Card,
    Checkbox,
    Divider,
    Form,
    Grid,
    Input,
    Space,
    Typography,
    theme,
} from 'antd';

const { Title, Paragraph } = Typography;
const { useBreakpoint } = Grid;

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { token } = theme.useToken();
    const screens = useBreakpoint();
    const isMobile = !screens.md;

    const { data, setData, post, processing, errors, reset } = useForm({
        login: '',
        password: '',
        remember: false as boolean,
    });

    const submit = () => {
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <Card
                style={{
                    borderRadius: 12,
                    boxShadow: token.boxShadowSecondary,
                    background: token.colorBgContainer,
                }}
                styles={{
                    body: {
                        padding: isMobile ? '20px 16px' : '32px 28px',
                    },
                }}
            >
                <Space
                    direction="vertical"
                    size={isMobile ? 8 : 12}
                    style={{ width: '100%', textAlign: 'center', marginBottom: isMobile ? 16 : 20 }}
                >
                    <ApplicationLogo
                        style={{
                            color: token.colorPrimary,
                            width: isMobile ? 48 : 56,
                            height: isMobile ? 48 : 56,
                            display: 'block',
                            margin: '0 auto',
                        }}
                    />
                    <Title level={isMobile ? 4 : 3} style={{ margin: 0 }}>
                        ARKA MineOps
                    </Title>
                    <Paragraph
                        type="secondary"
                        style={{
                            margin: 0,
                            fontSize: isMobile ? 13 : 14,
                        }}
                    >
                        Sistem Manajemen Operasional Tambang
                    </Paragraph>
                </Space>

                <Divider style={{ margin: isMobile ? '16px 0 20px' : '20px 0 24px' }} />

                <Title level={isMobile ? 5 : 4} style={{ marginBottom: 4 }}>
                    Masuk
                </Title>
                <Paragraph
                    type="secondary"
                    style={{ marginBottom: isMobile ? 20 : 24, fontSize: isMobile ? 13 : 14 }}
                >
                    Gunakan email atau username untuk masuk ke akun Anda.
                </Paragraph>

                {status && (
                    <Alert
                        type="success"
                        message={status}
                        showIcon
                        style={{ marginBottom: 24 }}
                    />
                )}

                <Form layout="vertical" onFinish={submit} requiredMark={false}>
                    <Form.Item
                        label="Email atau Username"
                        validateStatus={errors.login ? 'error' : ''}
                        help={errors.login}
                        required
                    >
                        <Input
                            size="large"
                            value={data.login}
                            autoComplete="username"
                            autoFocus
                            onChange={(e) => setData('login', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Password"
                        validateStatus={errors.password ? 'error' : ''}
                        help={errors.password}
                        required
                    >
                        <Input.Password
                            size="large"
                            value={data.password}
                            autoComplete="current-password"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                    </Form.Item>

                    <div
                        style={{
                            display: 'flex',
                            flexDirection: isMobile ? 'column' : 'row',
                            alignItems: isMobile ? 'flex-start' : 'center',
                            justifyContent: 'space-between',
                            gap: isMobile ? 12 : 0,
                            marginBottom: 24,
                        }}
                    >
                        <Checkbox
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        >
                            Ingat saya
                        </Checkbox>

                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                style={{ color: token.colorPrimary, fontSize: 14 }}
                            >
                                Lupa password?
                            </Link>
                        )}
                    </div>

                    <Button
                        type="primary"
                        htmlType="submit"
                        size="large"
                        block
                        loading={processing}
                    >
                        Masuk
                    </Button>
                </Form>
            </Card>
        </GuestLayout>
    );
}
