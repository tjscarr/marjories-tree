import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Adding Filament and Livewire assets explicitly helps Vite optimize them
                'resources/css/filament/filament.css',
                'resources/js/filament/filament.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
                'app/Filament/**',  // Adding Filament paths for hot reload
            ],
        }),
    ],
    build: {
        manifest: true,
        outDir: 'public/build',
        // Optimizing chunk strategy for better caching and loading
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Group Filament-related code together
                    if (id.includes('filament')) {
                        return 'filament-vendor';
                    }
                    // Group Livewire-related code together
                    if (id.includes('livewire')) {
                        return 'livewire-vendor';
                    }
                    // Group third-party dependencies
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
                // Ensure consistent chunk naming for better caching
                chunkFileNames: 'assets/js/[name]-[hash].js',
                assetFileNames: 'assets/[ext]/[name]-[hash].[ext]',
            },
        },
        // Adding source maps for better debugging
        sourcemap: process.env.APP_ENV === 'local',
        // Optimizing chunk sizes
        chunkSizeWarningLimit: 1000,
    },
    optimizeDeps: {
        // Pre-bundle these dependencies to improve initial load time
        include: ['@filamentphp/forms', '@filamentphp/tables', 'alpinejs'],
    },
});