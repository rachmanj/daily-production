import '../css/app.css';
import './bootstrap';

import { ThemeProvider, useTheme } from '@/Contexts/ThemeContext';
import { dayjs } from '@/lib/date';
import { ProConfigProvider, idIDIntl } from '@ant-design/pro-components';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ConfigProvider, theme } from 'antd';
import idID from 'antd/locale/id_ID';
import { createRoot, type Root } from 'react-dom/client';
import { ComponentType } from 'react';
import { registerSW } from 'virtual:pwa-register';

dayjs.locale('id');

const appName = import.meta.env.VITE_APP_NAME || 'ARKA MineOps';

let reactRoot: Root | null = (window as Window & { __reactRoot?: Root }).__reactRoot ?? null;

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 30_000,
            retry: 1,
        },
    },
});

if ('serviceWorker' in navigator) {
    registerSW({ immediate: true });
}

function AntThemeWrapper({
    children,
}: {
    children: React.ReactNode;
}) {
    const { mode } = useTheme();

    return (
        <ConfigProvider
            locale={idID}
            theme={{
                algorithm: mode === 'dark' ? theme.darkAlgorithm : theme.defaultAlgorithm,
                token: {
                    colorPrimary: '#1677ff',
                    borderRadius: 6,
                    fontFamily:
                        'Instrument Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                },
            }}
        >
            <ProConfigProvider intl={idIDIntl}>{children}</ProConfigProvider>
        </ConfigProvider>
    );
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        if (!el) {
            return;
        }

        const InertiaApp = App as ComponentType<typeof props>;

        if (!reactRoot) {
            reactRoot = createRoot(el);
            (window as Window & { __reactRoot?: Root }).__reactRoot = reactRoot;
        }

        reactRoot.render(
            <QueryClientProvider client={queryClient}>
                <ThemeProvider>
                    <AntThemeWrapper>
                        <InertiaApp {...props} />
                    </AntThemeWrapper>
                </ThemeProvider>
            </QueryClientProvider>,
        );
    },
    progress: {
        color: '#1677ff',
    },
});

if (import.meta.hot) {
    import.meta.hot.dispose(() => {
        reactRoot?.unmount();
        reactRoot = null;
        delete (window as Window & { __reactRoot?: Root }).__reactRoot;
    });
}
