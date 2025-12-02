import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'build', // CRA used "build"
    rollupOptions: {
      output: {
        // Disable code splitting
        manualChunks: undefined,
        inlineDynamicImports: true,

        // Force deterministic filenames
        entryFileNames: 'static/js/[name].js',
        chunkFileNames: 'static/js/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'static/css/[name].css';
          }
          return 'static/[ext]/[name].[ext]';
        }
      }
    }
  },
  server: {
    port: 3000,
    open: true
  }
});
