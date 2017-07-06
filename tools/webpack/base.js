const globby = require('globby');
const path = require('path');

const conf = require('../config');

const entry = {
  vendor: [
    'babel-polyfill',
    'jquery'
  ]
};

globby.sync(conf.script.entries)
  .forEach((filename) => {
    const basename = path.basename(filename, path.extname(filename));
    entry[basename] = `./${filename}`;
  });

module.exports = {
  entry,
  output: {
    filename: '[name].js',
    sourceMapFilename: '[name].map' //inline-source-mapの時は特に必要ないが一応
  },
  resolve: {
    modules: ['node_modules'],
    extensions: ['.jsx', '.js']
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: conf.babelOptions
        }
      }
    ]
  }
};
