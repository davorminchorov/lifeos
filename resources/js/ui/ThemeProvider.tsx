import React, { createContext, useContext, ReactNode } from 'react';

// Material Design 3 color system
export type ThemeColors = {
  primary: string;
  primaryContainer: string;
  onPrimary: string;
  onPrimaryContainer: string;
  secondary: string;
  secondaryContainer: string;
  onSecondary: string;
  onSecondaryContainer: string;
  tertiary: string;
  tertiaryContainer: string;
  onTertiary: string;
  onTertiaryContainer: string;
  error: string;
  errorContainer: string;
  onError: string;
  onErrorContainer: string;
  background: string;
  onBackground: string;
  surface: string;
  onSurface: string;
  surfaceVariant: string;
  onSurfaceVariant: string;
  outline: string;
  shadow: string;
};

// Default theme colors based on Material Design 3
const defaultTheme: ThemeColors = {
  primary: '#006D77',
  primaryContainer: '#83C5BE',
  onPrimary: '#FFFFFF',
  onPrimaryContainer: '#002021',
  secondary: '#EDF6F9',
  secondaryContainer: '#FFDDD2',
  onSecondary: '#000000',
  onSecondaryContainer: '#2A0800',
  tertiary: '#E29578',
  tertiaryContainer: '#FFB4A1',
  onTertiary: '#FFFFFF',
  onTertiaryContainer: '#2E0D00',
  error: '#B3261E',
  errorContainer: '#F9DEDC',
  onError: '#FFFFFF',
  onErrorContainer: '#410E0B',
  background: '#FAFAFA',
  onBackground: '#1C1B1F',
  surface: '#FFFFFF',
  onSurface: '#1C1B1F',
  surfaceVariant: '#E7E0EC',
  onSurfaceVariant: '#49454F',
  outline: '#79747E',
  shadow: '#000000',
};

// Create context
type ThemeContextType = {
  colors: ThemeColors;
  isDark: boolean;
  toggleTheme: () => void;
};

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

// Theme provider props
interface ThemeProviderProps {
  children: ReactNode;
}

// Custom hook for using theme
export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

// Theme provider component
export const ThemeProvider: React.FC<ThemeProviderProps> = ({ children }) => {
  // For now, just implement light theme, but could add dark theme toggle later
  const [isDark, setIsDark] = React.useState(false);
  const [colors, setColors] = React.useState<ThemeColors>(defaultTheme);

  const toggleTheme = () => {
    setIsDark(!isDark);
    // Would implement dark theme colors here
  };

  // Export CSS variables to be used with Tailwind
  React.useEffect(() => {
    const root = document.documentElement;
    Object.entries(colors).forEach(([name, value]) => {
      // Convert camelCase to kebab-case
      const cssName = name.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase();
      root.style.setProperty(`--md-${cssName}`, value);
    });
  }, [colors]);

  return (
    <ThemeContext.Provider value={{ colors, isDark, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
};

export default ThemeProvider;
