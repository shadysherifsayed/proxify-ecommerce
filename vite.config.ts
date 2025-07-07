import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig, loadEnv } from 'vite';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig(({ mode }) => {
  // Load env file based on `mode` in the current working directory.
  // Set the third parameter to '' to load all env regardless of the `VITE_` prefix.
  const env = loadEnv(mode, process.cwd(), '');
  
  return {
    plugins: [
      laravel({
        input: ['resources/js/app.ts', 'resources/css/app.css'],
        refresh: true,
        valetTls: false,
        detectTls: false,
      }),
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
      vuetify({ autoImport: true }),
    ],
    server: {
      host: '0.0.0.0', // Allow access from outside the container
      port: parseInt(env.VITE_APP_PORT) || 5174, // Use VITE_PORT from Laravel env
      hmr: {
        port: parseInt(env.VITE_APP_PORT) || 5174,
        host: 'localhost', // Use localhost for HMR connections from browser
      },
      watch: {
        usePolling: true,
      },
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './resources/js'),
      },
    },
  };
});
