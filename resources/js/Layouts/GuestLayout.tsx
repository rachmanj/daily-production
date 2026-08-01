import ThemeToggle from '@/Components/ThemeToggle';
import { Grid, theme } from 'antd';
import { PropsWithChildren, useEffect } from 'react';

const { useBreakpoint } = Grid;

export default function GuestLayout({ children }: PropsWithChildren) {
    const { token } = theme.useToken();
    const screens = useBreakpoint();
    const isMobile = !screens.md;

    useEffect(() => {
        const html = document.documentElement;
        const body = document.body;
        const app = document.getElementById('app');

        const previous = {
            htmlOverflow: html.style.overflow,
            bodyOverflow: body.style.overflow,
            htmlHeight: html.style.height,
            bodyHeight: body.style.height,
            appHeight: app?.style.height ?? '',
            appOverflow: app?.style.overflow ?? '',
        };

        html.style.height = '100%';
        body.style.height = '100%';
        html.style.overflow = 'hidden';
        body.style.overflow = 'hidden';

        if (app) {
            app.style.height = '100%';
            app.style.overflow = 'hidden';
        }

        return () => {
            html.style.overflow = previous.htmlOverflow;
            body.style.overflow = previous.bodyOverflow;
            html.style.height = previous.htmlHeight;
            body.style.height = previous.bodyHeight;

            if (app) {
                app.style.height = previous.appHeight;
                app.style.overflow = previous.appOverflow;
            }
        };
    }, []);

    return (
        <div
            style={{
                position: 'relative',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                boxSizing: 'border-box',
                width: '100%',
                height: '100dvh',
                maxHeight: '100dvh',
                overflow: 'hidden',
                padding: isMobile ? '16px 20px' : '24px',
                background: `linear-gradient(160deg, ${token.colorBgLayout} 0%, ${token.colorBgElevated} 45%, ${token.colorBgLayout} 100%)`,
            }}
        >
            <div
                style={{
                    position: 'absolute',
                    top: isMobile ? 16 : 24,
                    right: isMobile ? 16 : 24,
                    zIndex: 10,
                }}
            >
                <ThemeToggle />
            </div>

            <div
                style={{
                    width: '100%',
                    maxWidth: isMobile ? 'min(90vw, 420px)' : 420,
                    margin: '0 auto',
                }}
            >
                {children}
            </div>
        </div>
    );
}
