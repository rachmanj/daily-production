import ThemeToggle from '@/Components/ThemeToggle';
import { Grid, theme } from 'antd';
import { PropsWithChildren } from 'react';

const { useBreakpoint } = Grid;

export default function GuestLayout({ children }: PropsWithChildren) {
    const { token } = theme.useToken();
    const screens = useBreakpoint();
    const isMobile = !screens.md;

    return (
        <div
            style={{
                position: 'relative',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                minHeight: '100dvh',
                overflowY: 'auto',
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
