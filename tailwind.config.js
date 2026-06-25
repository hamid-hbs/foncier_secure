/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Syne', 'system-ui', 'sans-serif'],
      },
      colors: {
        green: {
          50: '#f0f7f4',
          100: '#d1eae0',
          200: '#a8d5c4',
          300: '#6bb99e',
          400: '#40916c',
          500: '#2d6a4f',
          600: '#1b4332',
          700: '#143d2b',
          800: '#0f3324',
          900: '#0a281c',
        },
        gold: {
          50: '#fdf8f0',
          100: '#f9eddb',
          200: '#f2dab5',
          300: '#e8c18a',
          400: '#d4a373',
          500: '#c48f5c',
          600: '#a87848',
          700: '#8c633c',
          800: '#755135',
          900: '#634330',
        },
        danger: { DEFAULT: '#D62828', 50: '#fef2f2', 100: '#fee2e2', 200: '#fecaca', 300: '#fca5a5', 400: '#f87171', 500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d' },
        warning: { DEFAULT: '#E76F51', 50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 300: '#fdba74', 400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c', 800: '#9a3412', 900: '#7c2d12' },
        info: { DEFAULT: '#457B9D', 50: '#f0f7fb', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
        surface: {
          50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
          400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
          800: '#1e293b', 900: '#0f172a',
        }
      },
      borderRadius: { 'premium': '8px', 'premium-lg': '12px' },
      boxShadow: {
        'premium': '0 1px 3px rgba(0,0,0,0.08)',
        'premium-md': '0 4px 12px rgba(0,0,0,0.1)',
        'premium-lg': '0 10px 40px -4px rgba(0,0,0,0.12)',
        'premium-xl': '0 20px 60px -8px rgba(0,0,0,0.15)',
        'premium-inner': 'inset 0 1px 2px rgba(0,0,0,0.04)',
      },
    },
  },
  plugins: [],
}
