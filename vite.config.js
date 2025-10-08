import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { glob } from 'glob';

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
  return glob.sync(query);
}

/**
 * Collect JS & SCSS Files
 */
const pageJsFiles   = GetFilesArray('resources/assets/js/*.js');
const vendorJsFiles = GetFilesArray('resources/assets/vendor/js/*.js');
const libsJsFiles   = GetFilesArray('resources/assets/vendor/libs/**/*.js');

const coreScssFiles  = GetFilesArray('resources/assets/vendor/scss/**/!(_)*.scss');
const libsScssFiles  = GetFilesArray('resources/assets/vendor/libs/**/!(_)*.scss');
const libsCssFiles   = GetFilesArray('resources/assets/vendor/libs/**/*.css');
const fontsScssFiles = GetFilesArray('resources/assets/vendor/fonts/!(_)*.scss');

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/assets/vendor/libs/apex-charts/apex-charts.scss',

        ...pageJsFiles,
        ...vendorJsFiles,
        ...libsJsFiles,
        ...coreScssFiles,
        ...libsScssFiles,
        ...libsCssFiles,
        ...fontsScssFiles
      ],
      refresh: true,
    }),
  ],

  // ✅ build config should be here, not inside laravel()
  build: {
    rollupOptions: {
      external: [
        'perfect-scrollbar',
        'masonry-layout',
        'jquery',
         'highlight.js',
      ],
    },
  },
});
