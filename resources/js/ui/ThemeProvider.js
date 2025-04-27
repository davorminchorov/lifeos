import { jsx as _jsx } from "react/jsx-runtime";
import React, { createContext, useContext } from 'react';
// Default theme colors based on Material Design 3 (teal-based palette from app.css)
const defaultTheme = {
    primary: '#0F766E',
    primaryContainer: '#99F6E4',
    onPrimary: '#FFFFFF',
    onPrimaryContainer: '#022C26',
    secondary: '#475569',
    secondaryContainer: '#E2E8F0',
    onSecondary: '#FFFFFF',
    onSecondaryContainer: '#1E293B',
    tertiary: '#6D28D9',
    tertiaryContainer: '#DDD6FE',
    onTertiary: '#FFFFFF',
    onTertiaryContainer: '#2E1065',
    surface: '#FFFFFF',
    surfaceVariant: '#F8FAFC',
    onSurface: '#1E293B',
    onSurfaceVariant: '#475569',
    error: '#EF4444',
    errorContainer: '#FECACA',
    onError: '#FFFFFF',
    onErrorContainer: '#7F1D1D',
    background: '#F8FAFC',
    onBackground: '#1E293B',
    outline: '#CBD5E1',
    shadow: '#000000',
};
const ThemeContext = createContext(undefined);
// Custom hook for using theme
export const useTheme = () => {
    const context = useContext(ThemeContext);
    if (context === undefined) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }
    return context;
};
// Theme provider component
export const ThemeProvider = ({ children }) => {
    // For now, just implement light theme, but could add dark theme toggle later
    const [isDark, setIsDark] = React.useState(false);
    const [colors, setColors] = React.useState(defaultTheme);
    const toggleTheme = () => {
        setIsDark(!isDark);
        if (!isDark) {
            // Switch to dark theme
            setColors({
                primary: '#14B8A6',
                primaryContainer: '#134E48',
                onPrimary: '#FFFFFF',
                onPrimaryContainer: '#99F6E4',
                secondary: '#94A3B8',
                secondaryContainer: '#334155',
                onSecondary: '#FFFFFF',
                onSecondaryContainer: '#E2E8F0',
                tertiary: '#A78BFA',
                tertiaryContainer: '#4C1D95',
                onTertiary: '#FFFFFF',
                onTertiaryContainer: '#DDD6FE',
                surface: '#121212',
                surfaceVariant: '#1E293B',
                onSurface: '#F8FAFC',
                onSurfaceVariant: '#E2E8F0',
                error: '#F87171',
                errorContainer: '#991B1B',
                onError: '#FFFFFF',
                onErrorContainer: '#FCA5A5',
                background: '#0F172A',
                onBackground: '#F8FAFC',
                outline: '#475569',
                shadow: '#000000',
            });
        }
        else {
            // Switch back to light theme
            setColors(defaultTheme);
        }
    };
    // Export CSS variables to be used with Tailwind - moved outside of useEffect for immediate application
    const applyTheme = () => {
        const root = document.documentElement;
        // Set these names directly to match the style from app.css and tailwind.config.js
        root.style.setProperty('--md-sys-color-primary', colors.primary);
        root.style.setProperty('--md-sys-color-primary-container', colors.primaryContainer);
        root.style.setProperty('--md-sys-color-on-primary', colors.onPrimary);
        root.style.setProperty('--md-sys-color-on-primary-container', colors.onPrimaryContainer);
        root.style.setProperty('--md-sys-color-secondary', colors.secondary);
        root.style.setProperty('--md-sys-color-secondary-container', colors.secondaryContainer);
        root.style.setProperty('--md-sys-color-on-secondary', colors.onSecondary);
        root.style.setProperty('--md-sys-color-on-secondary-container', colors.onSecondaryContainer);
        root.style.setProperty('--md-sys-color-tertiary', colors.tertiary);
        root.style.setProperty('--md-sys-color-tertiary-container', colors.tertiaryContainer);
        root.style.setProperty('--md-sys-color-on-tertiary', colors.onTertiary);
        root.style.setProperty('--md-sys-color-on-tertiary-container', colors.onTertiaryContainer);
        root.style.setProperty('--md-sys-color-error', colors.error);
        root.style.setProperty('--md-sys-color-error-container', colors.errorContainer);
        root.style.setProperty('--md-sys-color-on-error', colors.onError);
        root.style.setProperty('--md-sys-color-on-error-container', colors.onErrorContainer);
        root.style.setProperty('--md-sys-color-background', colors.background);
        root.style.setProperty('--md-sys-color-on-background', colors.onBackground);
        root.style.setProperty('--md-sys-color-surface', colors.surface);
        root.style.setProperty('--md-sys-color-on-surface', colors.onSurface);
        root.style.setProperty('--md-sys-color-surface-variant', colors.surfaceVariant);
        root.style.setProperty('--md-sys-color-on-surface-variant', colors.onSurfaceVariant);
        root.style.setProperty('--md-sys-color-outline', colors.outline);
        if (isDark) {
            document.documentElement.classList.add('dark');
        }
        else {
            document.documentElement.classList.remove('dark');
        }
    };
    // Apply theme immediately when the component loads and when colors/isDark changes
    React.useEffect(() => {
        applyTheme();
    }, [colors, isDark]);
    // Call it once during component initialization to avoid the flash of unstyled content
    React.useLayoutEffect(() => {
        applyTheme();
    }, []);
    return (_jsx(ThemeContext.Provider, { value: { colors, isDark, toggleTheme }, children: children }));
};
export default ThemeProvider;
