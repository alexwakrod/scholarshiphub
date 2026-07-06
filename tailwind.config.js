import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import aspectRatio from '@tailwindcss/aspect-ratio';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#3B82F6',
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                    950: '#172554',
                },
                accent: {
                    DEFAULT: '#FFD700',
                    50: '#FFFFF0',
                    100: '#FFFFE0',
                    200: '#FFFFB2',
                    300: '#FFEC8B',
                    400: '#FFE55C',
                    500: '#FFD700',
                    600: '#FFC107',
                    700: '#FFB300',
                    800: '#FFA000',
                    900: '#FF8F00',
                    950: '#FF6F00',
                },
                glass: {
                    light: 'rgba(255, 255, 255, 0.05)',
                    medium: 'rgba(255, 255, 255, 0.1)',
                    heavy: 'rgba(255, 255, 255, 0.15)',
                },
                'glass-dark': {
                    light: 'rgba(0, 0, 0, 0.2)',
                    medium: 'rgba(0, 0, 0, 0.3)',
                    heavy: 'rgba(0, 0, 0, 0.4)',
                },
            },
            backdropBlur: {
                glass: '8px',
                'glass-lg': '12px',
                'glass-xl': '20px',
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-out',
                'slide-up': 'slideUp 0.3s ease-out',
                shake: 'shake 0.3s',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                shake: {
                    '0%,100%': { transform: 'translateX(0)' },
                    '25%': { transform: 'translateX(-4px)' },
                    '75%': { transform: 'translateX(4px)' },
                },
            },
        },
    },
    plugins: [forms, typography, aspectRatio],
};