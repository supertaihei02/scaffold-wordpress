const gulp = require('gulp');
const runSequence = require('run-sequence');
const browserSync = require('browser-sync');

const conf = require('../config');

gulp.task('default', () => process.env.NODE_ENV === 'production' ? runSequence(
  // production
  'copy',
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
    proxy: 'localhost',
    files: [
      "./wordpress/themes/fl/**/*",
      "!./wordpress/themes/fl/js/vendor.bundle.js",
    ]
  });
});
