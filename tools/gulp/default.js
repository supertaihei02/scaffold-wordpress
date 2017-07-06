const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const runSequence = require('run-sequence');
const webpack = require('webpack');
const webpackStream = require('webpack-stream');
const customProperties = require('postcss-custom-properties');
const nested = require('postcss-nested');
const importCss = require('postcss-import');
const customMedia = require('postcss-custom-media');
const cssFixes = require('postcss-fixes');
const url = require('postcss-url');
const autoprefixer = require('autoprefixer');
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

gulp.task('scripts', () => $.plumber()
  .pipe(webpackStream(scriptConfDevelopment, webpack))
  .pipe(gulp.dest(conf.script.outputDir))
);

gulp.task('b.scripts', () => webpackStream(scriptConfProduction, webpack)
  .pipe(gulp.dest(conf.script.outputDir))
);

gulp.task('styles', () => gulp.src(conf.style.entries)
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

gulp.task('b.styles', () => gulp.src(conf.style.entries)
  .pipe($.postcss([
    importCss,
    customProperties,
    customMedia,
    nested,
    cssFixes,
    autoprefixer(conf.style.autoprefixerOption),
    cssnano
  ]))
  .pipe(gulp.dest(conf.style.outputDir))
);
