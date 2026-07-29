import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Checkbox, Form, Grid, Input, Typography, theme } from 'antd';

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
        <>
            <Head title="Log in" />

            <div
                className="relative flex min-h-dvh items-center justify-center overflow-y-auto"
                style={{
                    background: token.colorBgLayout,
                    padding: isMobile ? '16px 20px' : '48px 24px',
                }}
            >
                <div
                    className="absolute z-10"
                    style={{
                        right: isMobile ? 16 : 32,
                        top: isMobile ? 16 : 32,
                    }}
                >
                    <ThemeToggle />
                </div>

                <div
                    className="mx-auto w-full"
                    style={{ maxWidth: isMobile ? 'min(90vw, 400px)' : 400 }}
                >
                    <div className="text-center" style={{ marginBottom: isMobile ? 20 : 32 }}>
                        <ApplicationLogo
                            className="mx-auto fill-current"
                            style={{
                                color: token.colorPrimary,
                                width: isMobile ? 48 : 64,
                                height: isMobile ? 48 : 64,
                                marginBottom: isMobile ? 12 : 16,
                            }}
                        />
                        <Title level={isMobile ? 4 : 3} style={{ margin: 0 }}>
                            ARKA MineOps
                        </Title>
                        <Paragraph
                            type="secondary"
                            style={{
                                marginBottom: 0,
                                marginTop: isMobile ? 4 : 8,
                                fontSize: isMobile ? 13 : 14,
                            }}
                        >
                            Sistem Manajemen Operasional Tambang
                        </Paragraph>
                    </div>

                    <Card
                        style={{
                            borderRadius: token.borderRadiusLG,
                            boxShadow: token.boxShadowSecondary,
                        }}
                        styles={{
                            body: {
                                padding: isMobile ? '20px 16px' : 24,
                            },
                        }}
                    >
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
                                className={`mb-6 flex ${isMobile ? 'flex-col items-start gap-3' : 'items-center justify-between'}`}
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
                                        className="text-sm"
                                        style={{ color: token.colorPrimary }}
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
                </div>
            </div>
        </>
    );
}
