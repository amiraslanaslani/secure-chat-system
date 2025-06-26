import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['front/**/*.test.ts'],
    exclude: ['**/node_modules/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
      reportsDirectory: './coverage',
      include: ['front/**/*.{ts,tsx,js,jsx}'],
      exclude: ['**/__tests__/**', '**/node_modules/**'],
    },
    globals: true,
    environment: 'node',
    setupFiles: [],
  },
  resolve: {
    alias: {},
  },
  esbuild: {},
}); 
