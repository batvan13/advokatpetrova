/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                petrova: {
                    main: '#1B2438',
                    deep: '#141C2E',
                    mid: '#2A3550',
                    top: '#3D4A63',
                    primary: '#F5F0E8',
                    secondary: '#B8B0A2',
                    gold: '#C9A96E',
                    'gold-hover': '#D4B87D',
                },
            },
            backgroundImage: {
                'petrova-hero':
                    'linear-gradient(to bottom, #3D4A63 0%, #2A3550 40%, #141C2E 100%)',
            },
            fontFamily: {
                playfair: ['"Playfair Display"', 'serif'],
            },
        },
    },
    plugins: [],
}
