const mix = require('laravel-mix')
const baseUrl = process.env.BASE_URL

mix.setPublicPath('./web')
.sass('src/sass/import.scss', 'web/css/site.css')
.options({
  processCssUrls: false,
})
.minify('src/js/site.js', 'web/js/site.min.js')
.browserSync({
  files: ['web/css/*', 'web/js/*'],
  proxy: baseUrl,
  notify: false,
})
.version()
.disableNotifications()
