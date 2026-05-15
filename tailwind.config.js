import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                sidebar: {
                    DEFAULT: '#0f111a',
                    elevated: '#141824',
                    border: 'rgba(148, 163, 184, 0.12)',
                },
                surface: {
                    DEFAULT: '#f4f6fb',
                    card: '#ffffff',
                    muted: '#e8ecf4',
                },
            },
            boxShadow: {
                'card': '0 1px 2px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.06)',
                'card-hover': '0 2px 4px rgba(15, 23, 42, 0.06), 0 12px 32px rgba(15, 23, 42, 0.08)',
                'glass': '0 8px 32px rgba(15, 23, 42, 0.12)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
            backgroundImage: {
                'mesh-light':
                    'radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.06) 0px, transparent 45%), radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.05) 0px, transparent 40%)',
                'mesh-dark':
                    'radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(30, 27, 75, 0.8) 0px, transparent 55%)',
                'login-gradient':
                    'linear-gradient(145deg, #020617 0%, #0a0f1e 22%, #0f172a 42%, #1e1b4b 68%, #172554 88%, #0c4a6e 100%)',
                'enterprise-mesh':
                    'radial-gradient(ellipse 120% 80% at 10% 0%, rgba(124, 58, 237, 0.35) 0%, transparent 55%), radial-gradient(ellipse 90% 70% at 90% 20%, rgba(34, 211, 238, 0.2) 0%, transparent 50%), radial-gradient(ellipse 80% 60% at 50% 100%, rgba(79, 70, 229, 0.25) 0%, transparent 55%), linear-gradient(160deg, #020617 0%, #0b1120 40%, #0f172a 100%)',
                'auth-glow':
                    'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.45), transparent), radial-gradient(ellipse 60% 40% at 100% 0%, rgba(34, 211, 238, 0.12), transparent), radial-gradient(ellipse 50% 50% at 0% 100%, rgba(139, 92, 246, 0.2), transparent)',
                'auth-card-border':
                    'linear-gradient(135deg, rgba(34, 211, 238, 0.5), rgba(99, 102, 241, 0.4), rgba(167, 139, 250, 0.35), rgba(34, 211, 238, 0.25))',
            },
            keyframes: {
                'auth-blob': {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '33%': { transform: 'translate(30px, -20px) scale(1.05)' },
                    '66%': { transform: 'translate(-20px, 10px) scale(0.95)' },
                },
                'auth-glow-pulse': {
                    '0%, 100%': { opacity: '0.45' },
                    '50%': { opacity: '1' },
                },
                'auth-fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'auth-shimmer': {
                    '0%': { backgroundPosition: '200% 0' },
                    '100%': { backgroundPosition: '-200% 0' },
                },
                'auth-float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-6px)' },
                },
                'auth-drift': {
                    '0%, 100%': { transform: 'translate(0, 0) rotate(0deg)' },
                    '50%': { transform: 'translate(-8px, 6px) rotate(2deg)' },
                },
                'auth-mesh-shift': {
                    '0%, 100%': { opacity: '0.85', transform: 'scale(1) translate(0, 0)' },
                    '50%': { opacity: '1', transform: 'scale(1.04) translate(-2%, 1%)' },
                },
                'auth-border-glow': {
                    '0%, 100%': { opacity: '0.55' },
                    '50%': { opacity: '1' },
                },
                'auth-logo-pulse': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(99, 102, 241, 0.35), 0 8px 32px -8px rgba(79, 70, 229, 0.5)' },
                    '50%': { boxShadow: '0 0 0 6px rgba(99, 102, 241, 0.12), 0 12px 40px -6px rgba(34, 211, 238, 0.35)' },
                },
                'auth-particle': {
                    '0%, 100%': { transform: 'translateY(0) translateX(0)', opacity: '0.25' },
                    '50%': { transform: 'translateY(-24px) translateX(8px)', opacity: '0.85' },
                },
                'auth-scan': {
                    '0%': { transform: 'translateY(-100%)' },
                    '100%': { transform: 'translateY(100vh)' },
                },
                'auth-metric-glow': {
                    '0%, 100%': { borderColor: 'rgba(34, 211, 238, 0.15)' },
                    '50%': { borderColor: 'rgba(34, 211, 238, 0.4)' },
                },
            },
            animation: {
                'auth-blob': 'auth-blob 18s ease-in-out infinite',
                'auth-blob-slow': 'auth-blob 26s ease-in-out infinite reverse',
                'auth-glow-pulse': 'auth-glow-pulse 4s ease-in-out infinite',
                'auth-fade-up': 'auth-fade-up 0.55s ease-out both',
                'auth-fade-up-delay': 'auth-fade-up 0.65s ease-out 0.1s both',
                'auth-fade-up-delay-2': 'auth-fade-up 0.65s ease-out 0.2s both',
                'auth-fade-up-delay-3': 'auth-fade-up 0.65s ease-out 0.3s both',
                'auth-shimmer': 'auth-shimmer 8s linear infinite',
                'auth-float': 'auth-float 6s ease-in-out infinite',
                'auth-float-slow': 'auth-float 8.5s ease-in-out infinite reverse',
                'auth-drift': 'auth-drift 22s ease-in-out infinite',
                'auth-mesh-shift': 'auth-mesh-shift 14s ease-in-out infinite',
                'auth-border-glow': 'auth-border-glow 3s ease-in-out infinite',
                'auth-logo-pulse': 'auth-logo-pulse 3s ease-in-out infinite',
                'auth-particle': 'auth-particle 6s ease-in-out infinite',
                'auth-scan': 'auth-scan 8s linear infinite',
                'auth-metric-glow': 'auth-metric-glow 4s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
