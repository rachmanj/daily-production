import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ConfigProvider } from 'antd';
import idID from 'antd/locale/id_ID';
import { createRoot } from 'react-dom/client';
import { registerSW } from 'virtual:pwa-register';

const appName = import.meta.env.VITE_APP_NAME || 'ARKA MineOps';

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

        const root = createRoot(el);

        root.render(
            <QueryClientProvider client={queryClient}>
                <ConfigProvider
                    locale={idID}
                    theme={{
                        token: {
                            colorPrimary: '#1677ff',
                            borderRadius: 6,
                            fontFamily:
                                'Instrument Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                        },
                    }}
                >
                    <App {...props} />
                </ConfigProvider>
            </QueryClientProvider>,
        );
    },
    progress: {
        color: '#1677ff',
    },
});
