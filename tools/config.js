module.exports = {
  //copy: {
  //  static: ['src/*.*'],
  //  assets: ['src/assets/**/*'],
  //},

  style: {
    entries: ['source/styles/*.css', '!source/styles/_*.css'],
    outputDir: 'themes/fl',
    watches: ['source/styles/*.css', 'source/styles/**/*.css']
  },

  script: {
    entries: ['source/scripts/*.{js,jsx}', '!source/scripts/_*.{js,jsx}'],
    outputDir: 'themes/fl/js',
    watches: ['source/scripts/*.{js,jsx}', 'source/scripts/**/*.{js,jsx}']
  }
};
