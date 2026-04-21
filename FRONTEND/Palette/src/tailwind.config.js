// tailwind.config.js
export default {
  darkMode: 'class', // ← this is the key line
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}