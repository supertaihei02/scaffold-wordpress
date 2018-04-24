const gulp = require('gulp');
const runSequence = require('run-sequence');
const browserSync = require('browser-sync');
const dotenv = require('dotenv');

const conf = require('../config');

dotenv.config();

gulp.task('default', () => process.env.NODE_ENV === 'production' ? runSequence(
  // production
  ['b.copy', 'image'],
  ['b.script', 'b.style']
) : runSequence(
  // development
  'copy',
  ['script', 'style'],
  'browserSync',
  'watch'
));

gulp.task('watch', () => {
  gulp.watch(conf.copy.sources, ['copy']);
  gulp.watch(conf.script.watches, ['script']);
  gulp.watch(conf.style.watches, ['style']);
});

gulp.task('browserSync', () => {
  browserSync({
    proxy: `localhost:${process.env.EXPOSE_WEB_PORT}`,
    files: [
      "./wordpress/themes/fl_vue/**/*",
      "!./wordpress/themes/fl_vue/js/vendor.bundle.js",
    ]
  });
});
