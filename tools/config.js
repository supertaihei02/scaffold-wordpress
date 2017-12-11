const { browserslist: browsers } = require('../package.json');

module.exports = {
  copy: {
    sources: [
      'frontend/assets/**/*',
    ],
    sourcesProduction: [
      'frontend/assets/**/*',
      '!frontend/assets/**/*.{jpg,jpeg,gif,png}'
    ],
    outputDir: 'wordpress/themes/fl'
  },

  image: {
    sources: ['frontend/assets/**/*.{jpg,jpeg,gif,png}'],
    outputDir: 'wordpress/themes/fl',
    // PNG形式: https://www.npmjs.com/package/imagemin-pngquant
    png: {
      // クオリティ 0(やり過ぎ) ~ 100(ほぼそのまま) -で繋いで2つ書くとmin-maxという意味合いらしいがよくわかりません
      quality: '65-80',
      // 処理速度を指定 1(じっくり) ~ 10(最速) 5％くらい質に違いが出るらしい
      speed: 1,
      // ディザリングを設定 0(無効) ~ 1(最大)
      floyd: 0,
      // フロイド-スタインバーグ・ディザリングを無効化するか
      // https://ja.wikipedia.org/wiki/%E3%83%95%E3%83%AD%E3%82%A4%E3%83%89-%E3%82%B9%E3%82%BF%E3%82%A4%E3%83%B3%E3%83%90%E3%83%BC%E3%82%B0%E3%83%BB%E3%83%87%E3%82%A3%E3%82%B6%E3%83%AA%E3%83%B3%E3%82%B0
      nofs: false
    },
    // JPG形式: https://www.npmjs.com/package/imagemin-mozjpeg
    jpg: {
      // クオリティ 0(やり過ぎ) ~ 100(ほぼそのまま)
      quality: 80,
      // プログレッシブjpegを作成するか falseにするとベースラインjpeg
      progressive: true,
    },
    // GIF形式: https://github.com/imagemin/imagemin-gifsicle#imagemingifsicleoptionsbuffer
    gif: {
      // 最適化レベル 1(ちょっと)-3(そこそこ)で指定
      optimizationLevel: 3
    },
    // SVG形式: https://github.com/svg/svgo#what-it-can-do
    svg: {
    },
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
  },

  customizer: {
    entries: [
      'wordpress/plugins/customizer/src/js/**/*.{js,jsx}',
      '!wordpress/plugins/customizer/src/js/**/_*.{js,jsx}',
    ],
    outputDir: 'wordpress/plugins/customizer/js'
  }
};
