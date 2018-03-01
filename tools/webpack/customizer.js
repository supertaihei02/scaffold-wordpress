const webpack = require('webpack');
const UglifyJs = require('uglifyjs-webpack-plugin');
const globby = require('globby');
const path = require('path');

const conf = require('../config');

const entry = {};

globby.sync(conf.customizer.entries)
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
  cache: false,
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: [
          'cache-loader',
          {
            loader: 'babel-loader',
            options: conf.script.babelOptions
          }
        ]
      }
    ]
  },
  plugins: [
    new webpack.LoaderOptionsPlugin({ debug: false }),
    new UglifyJs(),
  ]
};

