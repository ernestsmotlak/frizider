import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
    plugins: [vue({
        template: {
            compilerOptions: {
                isCustomElement: (tag) => tag === 'emoji-picker'
            }
        }
    }), tailwindcss()],
    server: {
        host: '127.0.0.1',
        port: 8003,
        strictPort: true,
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8004',
                changeOrigin: true,
                secure: false,
            }
        }
    }
})
