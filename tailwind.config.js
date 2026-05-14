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
                    'linear-gradient(135deg, #0b1120 0%, #111827 38%, #1e1b4b 72%, #312e81 100%)',
                'auth-glow':
                    'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.45), transparent), radial-gradient(ellipse 60% 40% at 100% 0%, rgba(34, 211, 238, 0.12), transparent), radial-gradient(ellipse 50% 50% at 0% 100%, rgba(139, 92, 246, 0.2), transparent)',
            },
            keyframes: {
                'auth-blob': {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '33%': { transform: 'translate(30px, -20px) scale(1.05)' },
                    '66%': { transform: 'translate(-20px, 10px) scale(0.95)' },
                },
                'auth-glow-pulse': {
                    '0%, 100%': { opacity: '0.5' },
                    '50%': { opacity: '1' },
                },
                'auth-fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'auth-shimmer': {
                    '0%': { backgroundPosition: '200% 0' },
                    '100%': { backgroundPosition: '-200% 0' },
                },
                'auth-float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                'auth-drift': {
                    '0%, 100%': { transform: 'translate(0, 0) rotate(0deg)' },
                    '50%': { transform: 'translate(-6px, 4px) rotate(1deg)' },
                },
            },
            animation: {
                'auth-blob': 'auth-blob 18s ease-in-out infinite',
                'auth-blob-slow': 'auth-blob 26s ease-in-out infinite reverse',
                'auth-glow-pulse': 'auth-glow-pulse 4s ease-in-out infinite',
                'auth-fade-up': 'auth-fade-up 0.6s ease-out both',
                'auth-fade-up-delay': 'auth-fade-up 0.7s ease-out 0.08s both',
                'auth-fade-up-delay-2': 'auth-fade-up 0.7s ease-out 0.16s both',
                'auth-shimmer': 'auth-shimmer 8s linear infinite',
                'auth-float': 'auth-float 7s ease-in-out infinite',
                'auth-float-slow': 'auth-float 9s ease-in-out infinite reverse',
                'auth-drift': 'auth-drift 22s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
