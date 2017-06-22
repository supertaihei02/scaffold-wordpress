const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const runSequence = require('run-sequence');

const webpack = require('webpack');
const webpackStream = require('webpack-stream');

const postcssImport = require('postcss-import');
const cssnext = require('postcss-cssnext');
const flexbugsFixes = require('postcss-flexbugs-fixes');
const nthChildFix = require('postcss-nth-child-fix');
const cssnano = require('cssnano');

const conf = require('../config');
const scriptConfDevelopment = require('../webpack/development');
const scriptConfProduction = require('../webpack/production');

gulp.task('default', () => process.env.NODE_ENV === 'production' ? runSequence(
  // production
  ['b.scripts', 'b.styles']
) : runSequence(
  // development
  ['scripts', 'styles'],
  'watch'
));

gulp.task('watch', () => {
  gulp.watch(conf.script.watches, ['scripts']);
  gulp.watch(conf.style.watches, ['styles']);
});

gulp.task('scripts', () => (
  $.plumber()
    .pipe(webpackStream(scriptConfDevelopment, webpack))
    .pipe(gulp.dest(conf.script.outputDir))
));

gulp.task('b.scripts', () => (
  $.plumber()
    .pipe(webpackStream(scriptConfProduction, webpack))
    .pipe(gulp.dest(conf.script.outputDir))
));

gulp.task('styles', () => (
  gulp.src(conf.style.entries)
    .pipe($.plumber())
    .pipe($.postcss([
      postcssImport,
      flexbugsFixes,
      nthChildFix,
      cssnext
    ]))
    .pipe(gulp.dest(conf.style.outputDir))
));

gulp.task('b.styles', () => (
  gulp.src(conf.style.entries)
    .pipe($.postcss([
      postcssImport,
      flexbugsFixes,
      nthChildFix,
      cssnext,
      cssnano
    ]))
    .pipe(gulp.dest(conf.style.outputDir))
));
