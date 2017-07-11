const gulp = require('gulp');
const runSequence = require('run-sequence');
const browserSync = require('browser-sync');

const conf = require('../config');

gulp.task('default', () => process.env.NODE_ENV === 'production' ? runSequence(
  // production
  ['b.script', 'b.style']
) : runSequence(
  // development
  ['script', 'style'],
  'browserSync',
  'watch'
));

gulp.task('watch', () => {
  gulp.watch(conf.script.watches, ['scripts']);
  gulp.watch(conf.style.watches, ['styles']);
});

gulp.task('browserSync', function () {
  browserSync({
    proxy: 'localhost',
    files: [
      "./wordpress/themes/fl/style.css",
      "./wordpress/themes/fl/js/*.js",
      "./wordpress/**/*.php",
      "!./wordpress/themes/fl/js/vendor.bundle.js",
    ]
  });
});
