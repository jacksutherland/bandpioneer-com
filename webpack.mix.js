const mix = require('laravel-mix')
const baseUrl = process.env.BASE_URL

//*** PUBLIC WEBSITE ***//

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

//*** AUTHENTICATED BAND WEBSITE ***//

mix.setPublicPath('./web')
.sass('src/sass/bands.scss', 'web/css/bands.css')
.sass('src/sass/epk.scss', 'web/css/epk.css')
.options({
  processCssUrls: false,
})
.minify('src/js/bands.js', 'web/js/bands.min.js')
.minify('src/js/epk.js', 'web/js/epk.min.js')
.version()
.disableNotifications()