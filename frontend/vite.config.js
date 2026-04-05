import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
  ],

  resolve: {
    alias: {
      // '@' maps to src/ — use in imports: import Foo from '@/components/Foo.vue'
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },

  server: {
    port: 5173,
    // Proxy /api/* requests to Laravel backend during development.
    // This avoids CORS issues locally and matches the production setup
    // where a reverse proxy (nginx) routes /api to the backend.
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        // Forward cookies (needed for HttpOnly refresh_token cookie)
        cookieDomainRewrite: 'localhost',
      },
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        // Bootstrap 5 still uses @import, to-rgb(), red(), green(), blue()
        // which are deprecated in Dart Sass 1.80+. Bootstrap 6 will fix this.
        // These flags silence the warnings until we upgrade to Bootstrap 6.
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
      },
    },
  },
})
