// FRONTEND/Palette/vite.config.js

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
  

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), tailwindcss(),],
  server: {
    port: 5173,
    strictPort: true, // ← forces 5173, errors if taken
  }
})


