const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const customProperties = require('postcss-custom-properties');
const nested = require('postcss-nested');
const importCss = require('postcss-import');
const customMedia = require('postcss-custom-media');
const cssFixes = require('postcss-fixes');
const url = require('postcss-url');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

const conf = require('../config');

gulp.task('style', () => gulp.src(conf.style.entries)
  .pipe($.plumber())
  .pipe($.postcss([
    importCss,
    customProperties,
    customMedia,
    nested,
    cssFixes,
    //url(conf.style.urlOption),
    autoprefixer(conf.style.autoprefixerOption)
  ]))
  .pipe(gulp.dest(conf.style.outputDir))
);

gulp.task('b.style', () => gulp.src(conf.style.entries)
  .pipe($.postcss([
    importCss,
    customProperties,
    customMedia,
    nested,
    cssFixes,
    autoprefixer(conf.style.autoprefixerOption),
    cssnano({
      safe: true,
      calc: false
    })
  ]))
  .pipe(gulp.dest(conf.style.outputDir))
);
