const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const runSequence = require('run-sequence');
const webpack = require('webpack');
const webpackStream = require('webpack-stream');

const conf = require('../config');
const scriptConf = require('../webpack/customizer');

gulp.task('customizer:script', () => $.plumber()
  .pipe(webpackStream(scriptConf, webpack))
  .pipe(gulp.dest(conf.customizer.outputDir))
);

gulp.task('customizer', () => runSequence(
  ['customizer:script']
));
