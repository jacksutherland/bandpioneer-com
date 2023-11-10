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

//*** PUBLIC WEBSITE - REDESIGN 2023 ***//

mix.setPublicPath('./web')
.sass('src/sass/site2023.scss', 'web/css/site2023.css')
.options({
  processCssUrls: false,
})
.minify('src/js/site2023.js', 'web/js/site2023.min.js')
.browserSync({
  files: ['web/css/*', 'web/js/*'],
  proxy: baseUrl,
  notify: false,
})
.version()
.disableNotifications()

mix.setPublicPath('./web')
.sass('src/sass/homepage2023.scss', 'web/css/homepage2023.css')
.options({
  processCssUrls: false,
})
.browserSync({
  files: ['web/css/*'],
  proxy: baseUrl,
  notify: false,
})
.version()
.disableNotifications()

mix.setPublicPath('./web')
.sass('src/sass/blogpost2023.scss', 'web/css/blogpost2023.css')
.options({
  processCssUrls: false,
})
.browserSync({
  files: ['web/css/*'],
  proxy: baseUrl,
  notify: false,
})
.version()
.disableNotifications()

mix.setPublicPath('./web')
.sass('src/sass/search.scss', 'web/css/search.css')
.options({
  processCssUrls: false,
})
.browserSync({
  files: ['web/css/*'],
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
.minify('src/js/studio.js', 'web/js/studio.min.js')
.minify('src/js/debate.js', 'web/js/debate.min.js')
.version()
.disableNotifications()