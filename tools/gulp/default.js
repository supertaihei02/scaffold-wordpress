const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const runSequence = require('run-sequence');

//const webpack = require('webpack');
//const webpackStream = require('webpack-stream');

const postcssImport = require('postcss-import');
const cssnext = require('postcss-cssnext');
const flexbugsFixes = require('postcss-flexbugs-fixes');
const nthChildFix = require('postcss-nth-child-fix');

//const conf = require('../webpack/base');

gulp.task('default', () => runSequence(
  //['scripts', 'styles']
  ['styles']
));

//gulp.task('scripts', () => (
//  $.plumber()
//    .pipe(webpackStream(conf, webpack))
//    .pipe(gulp.dest('./build/'))
//));

gulp.task('styles', () => (
  gulp.src(['./source/style.css'])
    .pipe($.plumber())
    .pipe($.postcss([
      postcssImport,
      flexbugsFixes,
      nthChildFix,
      cssnext
    ]))
    .pipe(gulp.dest('./themes/fl/'))
));
