const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       // Add any required postcss plugins or options here
   ])
   .copyDirectory('public/assets', 'public/dist/assets') // Assuming your static assets are in the 'public/assets' directory
   .version();