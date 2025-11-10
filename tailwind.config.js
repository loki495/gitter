/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html',
    './src/**/*.{js,ts,vue,jsx,tsx}',
    'resources/css/app.css', 'resources/js/app.js',
    `resources/views/**/*`,
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  safelist: [
    // common FluxUI dynamic classes
    'hidden', 'block', 'flex', 'grid', 'inline-block',
    'bg-red-500', 'bg-green-500', 'bg-blue-500',
    'text-white', 'text-black', 'text-center', 'text-left',
    /^btn-/ // regex: matches any class starting with btn-
  ],
}
