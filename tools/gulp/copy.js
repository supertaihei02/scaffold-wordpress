const gulp = require('gulp');

const conf = require('../config');

gulp.task('copy', () => gulp.src(conf.copy.sources)
  .pipe(gulp.dest(conf.copy.outputDir))
);

gulp.task('b.copy', () => gulp.src(conf.copy.sourcesProduction)
  .pipe(gulp.dest(conf.copy.outputDir))
);

