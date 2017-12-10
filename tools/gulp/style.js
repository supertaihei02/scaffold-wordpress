const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const customProperties = require('postcss-custom-properties');
const nested = require('postcss-nested');
const importCss = require('postcss-import');
const customMedia = require('postcss-custom-media');
const cssFixes = require('postcss-fixes');
const url = require('postcss-url');
const hexAlpha = require('postcss-color-hex-alpha');
const simpleVars = require('postcss-simple-vars');
const mixins = require('postcss-mixins');
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
    mixins,
    hexAlpha,
    simpleVars,
    cssFixes,
    url(conf.style.urlOption),
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
    mixins,
    hexAlpha,
    simpleVars,
    cssFixes,
    url(conf.style.urlOption),
    autoprefixer(conf.style.autoprefixerOption),
    cssnano(conf.style.cssnanoOption)
  ]))
  .pipe(gulp.dest(conf.style.outputDir))
);
