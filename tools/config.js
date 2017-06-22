module.exports = {
  //copy: {
  //  static: ['src/*.*'],
  //  assets: ['src/assets/**/*'],
  //},

  style: {
    entries: ['frontend/styles/*.css', '!frontend/styles/_*.css'],
    outputDir: 'themes/fl',
    watches: ['frontend/styles/*.css', 'frontend/styles/**/*.css']
  },

  script: {
    entries: ['frontend/scripts/*.{js,jsx}', '!frontend/scripts/_*.{js,jsx}'],
    outputDir: 'themes/fl/js',
    watches: ['frontend/scripts/*.{js,jsx}', 'frontend/scripts/**/*.{js,jsx}']
  }
};
