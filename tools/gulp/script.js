const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const webpack = require('webpack');
const webpackStream = require('webpack-stream');

const conf = require('../config');
const scriptConfDevelopment = require('../webpack/development');
const scriptConfProduction = require('../webpack/production');

gulp.task('script', () => $.plumber()
  .pipe(webpackStream(scriptConfDevelopment, webpack))
  .pipe(gulp.dest(conf.script.outputDir))
);

gulp.task('b.script', () => webpackStream(scriptConfProduction, webpack)
  .pipe(gulp.dest(conf.script.outputDir))
);

