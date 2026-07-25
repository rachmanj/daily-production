import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Checkbox, Form, Input, Typography, theme } from 'antd';

const { Title, Paragraph } = Typography;

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { token } = theme.useToken();
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
                className="relative flex min-h-screen flex-col items-center justify-center p-6 md:p-12"
                style={{ background: token.colorBgLayout }}
            >
                <div className="absolute right-4 top-4 md:right-8 md:top-8">
                    <ThemeToggle />
                </div>

                <div className="w-full max-w-md">
                    <div className="mb-8 text-center">
                        <ApplicationLogo
                            className="mx-auto mb-4 h-16 w-16 fill-current"
                            style={{ color: token.colorPrimary }}
                        />
                        <Title level={3} style={{ margin: 0 }}>
                            ARKA MineOps
                        </Title>
                        <Paragraph type="secondary" style={{ marginBottom: 0, marginTop: 8 }}>
                            Sistem Manajemen Operasional Tambang
                        </Paragraph>
                    </div>

                    <Card
                        style={{
                            borderRadius: token.borderRadiusLG,
                            boxShadow: token.boxShadowSecondary,
                        }}
                    >
                        <Title level={4} style={{ marginBottom: 4 }}>
                            Masuk
                        </Title>
                        <Paragraph type="secondary" style={{ marginBottom: 24 }}>
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

                            <div className="mb-6 flex items-center justify-between">
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
