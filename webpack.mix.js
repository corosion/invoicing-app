const mix = require('laravel-mix')

mix.webpackConfig({
  devtool: 'inline-source-map'
})

mix.js('resources/js/app.js', 'public/js/')
  .react()

mix.sass('resources/scss/app.scss', 'public/css/')
  .sourceMaps()
