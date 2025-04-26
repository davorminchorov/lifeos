/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
    "./resources/**/*.ts",
    "./resources/**/*.tsx",
  ],
  theme: {
    extend: {
      colors: {
        teal: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0F766E', // Primary color
          700: '#0d9488',
          800: '#0f766e',
          900: '#134e4a',
        },
        slate: {
          50: '#f8fafc',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94A3B8', // Muted text
          500: '#64748b',
          600: '#475569', // Secondary text
          700: '#334155',
          800: '#1E293B', // Text color
          900: '#0f172a',
        },
        gray: {
          50: '#F8FAFC',  // Background
          100: '#F1F5F9', // Secondary background
        },
        red: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      // Add Material Design elevation shadows
      boxShadow: {
        'elevation-1': '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.14)',
        'elevation-2': '0 3px 6px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.10)',
        'elevation-3': '0 10px 20px rgba(0,0,0,0.12), 0 3px 6px rgba(0,0,0,0.10)',
        'elevation-4': '0 15px 25px rgba(0,0,0,0.12), 0 5px 10px rgba(0,0,0,0.10)',
      },
      // Add ripple animation for Material Design buttons
      keyframes: {
        ripple: {
          '0%': { transform: 'scale(0)', opacity: '0.5' },
          '100%': { transform: 'scale(2)', opacity: '0' },
        }
      },
      animation: {
        ripple: 'ripple 0.6s linear forwards',
      },
      borderRadius: {
        'md': '4px',
        'lg': '8px',
        'xl': '12px',
        '2xl': '16px',
        '3xl': '28px',
      },
    },
  },
  safelist: [
    'shadow-elevation-1',
    'shadow-elevation-2',
    'shadow-elevation-3',
    'shadow-elevation-4',
    'animate-ripple',
  ],
  plugins: [],
}
