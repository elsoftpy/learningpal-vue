/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/js/**/*.js",
        "./resources/js/components/**/*.vue",
        "./resources/js/layouts/**/*.vue", 
        "./resources/js/pages/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'ui-sans-serif', 'system-ui'],
            },
        },
    },
    plugins: [],
}