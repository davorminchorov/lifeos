/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
    "./resources/**/*.tsx",
  ],
  theme: {
    extend: {
      colors: {
        // Material Design 3 color system using CSS variables
        primary: {
          DEFAULT: 'var(--md-primary)',
          container: 'var(--md-primary-container)',
          on: 'var(--md-on-primary)',
          'on-container': 'var(--md-on-primary-container)',
        },
        secondary: {
          DEFAULT: 'var(--md-secondary)',
          container: 'var(--md-secondary-container)',
          on: 'var(--md-on-secondary)',
          'on-container': 'var(--md-on-secondary-container)',
        },
        tertiary: {
          DEFAULT: 'var(--md-tertiary)',
          container: 'var(--md-tertiary-container)',
          on: 'var(--md-on-tertiary)',
          'on-container': 'var(--md-on-tertiary-container)',
        },
        surface: {
          DEFAULT: 'var(--md-surface)',
          variant: 'var(--md-surface-variant)',
          on: 'var(--md-on-surface)',
          'on-variant': 'var(--md-on-surface-variant)',
        },
        error: {
          DEFAULT: 'var(--md-error)',
          container: 'var(--md-error-container)',
          on: 'var(--md-on-error)',
          'on-container': 'var(--md-on-error-container)',
        },
        background: 'var(--md-background)',
        'on-background': 'var(--md-on-background)',
        outline: 'var(--md-outline)',
      },
      keyframes: {
        ripple: {
          'to': {
            transform: 'scale(4)',
            opacity: '0'
          },
        },
      },
      animation: {
        ripple: 'ripple 0.6s linear forwards',
      },
      boxShadow: {
        // Material elevation levels
        'elevation-1': '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.14)',
        'elevation-2': '0 3px 6px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.10)',
        'elevation-3': '0 10px 20px rgba(0,0,0,0.12), 0 3px 6px rgba(0,0,0,0.10)',
        'elevation-4': '0 15px 25px rgba(0,0,0,0.12), 0 5px 10px rgba(0,0,0,0.10)',
      },
      borderRadius: {
        'xs': '4px',
        'sm': '8px',
        'md': '12px',
        'lg': '16px',
        'xl': '28px',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        brand: ['Roboto', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        // Material typography scale
        'display-large': ['3.56rem', { lineHeight: '4rem', fontWeight: '300', letterSpacing: '-0.016em' }],
        'display-medium': ['2.81rem', { lineHeight: '3.25rem', fontWeight: '300', letterSpacing: '-0.008em' }],
        'display-small': ['2.25rem', { lineHeight: '2.75rem', fontWeight: '300', letterSpacing: '0em' }],
        'headline-large': ['2rem', { lineHeight: '2.5rem', fontWeight: '400', letterSpacing: '0em' }],
        'headline-medium': ['1.75rem', { lineHeight: '2.25rem', fontWeight: '400', letterSpacing: '0em' }],
        'headline-small': ['1.5rem', { lineHeight: '2rem', fontWeight: '400', letterSpacing: '0em' }],
        'title-large': ['1.375rem', { lineHeight: '1.75rem', fontWeight: '400', letterSpacing: '0em' }],
        'title-medium': ['1rem', { lineHeight: '1.5rem', fontWeight: '500', letterSpacing: '0.015em' }],
        'title-small': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '500', letterSpacing: '0.01em' }],
        'body-large': ['1rem', { lineHeight: '1.5rem', fontWeight: '400', letterSpacing: '0.015em' }],
        'body-medium': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '400', letterSpacing: '0.017em' }],
        'body-small': ['0.75rem', { lineHeight: '1rem', fontWeight: '400', letterSpacing: '0.025em' }],
        'label-large': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '500', letterSpacing: '0.01em' }],
        'label-medium': ['0.75rem', { lineHeight: '1rem', fontWeight: '500', letterSpacing: '0.05em' }],
        'label-small': ['0.688rem', { lineHeight: '0.938rem', fontWeight: '500', letterSpacing: '0.05em' }],
      },
    },
  },
  plugins: [],
}
