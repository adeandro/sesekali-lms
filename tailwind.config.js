// NOTE: This file is NOT used by Tailwind v4 (@tailwindcss/vite).
// Configuration is now handled in resources/css/app.css via @source and @layer.
// Kept for reference only.
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  safelist: [
    'grid',
    'grid-cols-1',
    'lg:grid-cols-4',
    'lg:col-span-1',
    'lg:col-span-3',
    'gap-4',
    'md:gap-6',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
