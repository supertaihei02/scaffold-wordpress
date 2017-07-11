const { browserslist } = require('../package.json');

module.exports = {
  copy: {
    sources: ['frontend/assets/**/*'],
    outputDir: 'wordpress/themes/fl'
  },

  style: {
    entries: ['frontend/styles/*.css', '!frontend/styles/_*.css'],
    watches: ['frontend/styles/*.css', 'frontend/styles/**/*.css'],
    outputDir: 'wordpress/themes/fl',
    autoprefixerOption: { grid: true }
  },

  script: {
    entries: ['frontend/scripts/*.{js,jsx}', '!frontend/scripts/_*.{js,jsx}'],
    watches: ['frontend/scripts/*.{js,jsx}', 'frontend/scripts/**/*.{js,jsx}'],
    outputDir: 'wordpress/themes/fl/js',
    babelOptions: {
      presets: [
        ['env', {
          // package.jsonで指定したbrowserslistを利用する
          targets: {
            browsers: browserslist,
            uglify: process.env.NODE_ENV === 'production'
          },
          // babel-polyfillのうちbrowserslistを踏まえて必要なものだけ読み込む
          useBuiltIns: true,
          // productionの場合tree shakingを有効化
          modules: process.env.NODE_ENV === 'production' ? false : 'commonjs',
          // developmentの際にデバッグ情報を出力する
          debug: process.env.NODE_ENV === 'development'
        }],
        'flow'
      ],
      plugins: [
        'transform-object-rest-spread'
      ],
      cacheDirectory: true,
      babelrc: false
    }
  }
};
