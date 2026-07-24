import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ConfigProvider } from 'antd';
import idID from 'antd/locale/id_ID';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'ARKA MineOps';

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
            </ConfigProvider>,
        );
    },
    progress: {
        color: '#1677ff',
    },
});
