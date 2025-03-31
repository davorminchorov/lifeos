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
          600: '#0F766E', // Primary color
        },
        slate: {
          800: '#1E293B', // Text color
          600: '#475569', // Secondary text
          400: '#94A3B8', // Muted text
        },
        gray: {
          50: '#F8FAFC',  // Background
          100: '#F1F5F9', // Secondary background
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
