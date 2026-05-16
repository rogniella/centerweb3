import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'; //Ran Se Agrego

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
            //Ran Importamos Bootstrap 5 
            resolve:{
                alias:{
                    '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
                }
            },
            // Fin Importamos Bootstrap 5             
        }),
    ],
});
