const { browserslist: browsers } = require('../package.json');

module.exports = {
  copy: {
    sources: ['frontend/assets/**/*'],
    outputDir: 'wordpress/themes/fl'
  },

  style: {
    entries: ['frontend/styles/*.css', '!frontend/styles/_*.css'],
    watches: ['frontend/styles/*.css', 'frontend/styles/**/*.css'],
    outputDir: 'wordpress/themes/fl',
    urlOption: { filter: ['./**/*'], url: 'inline' },
    autoprefixerOption: { grid: true },
    cssnanoOption: {
      // for postcss-fixes  https://www.npmjs.com/package/postcss-fixes#recommended-usage
      safe: true,
      calc: false,
    },
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
            browsers,
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
        'transform-object-rest-spread',
        'date-fns'
      ],
      cacheDirectory: true,
      babelrc: false
    }
  }
};
