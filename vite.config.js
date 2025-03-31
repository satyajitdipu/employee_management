import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/filament/app/theme.scss'
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
                'app/Tables/Columns/**'
            ],
        }),
    ],
    resolve: {
        alias: {
            '~filament': path.resolve(__dirname, 'vendor/filament/filament'),
        }
    },
});
