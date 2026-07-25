import {
    createContext,
    PropsWithChildren,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';

export type ThemeMode = 'dark' | 'light';

const STORAGE_KEY = 'theme';

interface ThemeContextValue {
    mode: ThemeMode;
    toggle: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);

function getStoredTheme(): ThemeMode {
    if (typeof window === 'undefined') {
        return 'dark';
    }

    const stored = localStorage.getItem(STORAGE_KEY);

    return stored === 'light' ? 'light' : 'dark';
}

function applyThemeClass(mode: ThemeMode): void {
    document.documentElement.classList.toggle('dark', mode === 'dark');
}

export function ThemeProvider({ children }: PropsWithChildren) {
    const [mode, setMode] = useState<ThemeMode>(getStoredTheme);

    useEffect(() => {
        applyThemeClass(mode);
        localStorage.setItem(STORAGE_KEY, mode);
    }, [mode]);

    const toggle = useCallback(() => {
        setMode((current) => (current === 'dark' ? 'light' : 'dark'));
    }, []);

    const value = useMemo(() => ({ mode, toggle }), [mode, toggle]);

    return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
}

export function useTheme(): ThemeContextValue {
    const context = useContext(ThemeContext);

    if (!context) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }

    return context;
}
