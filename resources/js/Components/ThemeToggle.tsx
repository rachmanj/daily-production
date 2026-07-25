import { useTheme } from '@/Contexts/ThemeContext';
import { MoonOutlined, SunOutlined } from '@ant-design/icons';
import { Button, Tooltip } from 'antd';

export default function ThemeToggle() {
    const { mode, toggle } = useTheme();

    return (
        <Tooltip title={mode === 'dark' ? 'Mode terang' : 'Mode gelap'}>
            <Button
                type="text"
                icon={mode === 'dark' ? <SunOutlined /> : <MoonOutlined />}
                onClick={toggle}
                aria-label={mode === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
            />
        </Tooltip>
    );
}
