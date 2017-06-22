const webpack = require('webpack');
const UglifyJs = require('uglifyjs-webpack-plugin');

const base = require('./base');

process.noDeprecation = true;

module.exports = Object.assign({}, base, {
  cache: false,
  devtool: '',
  plugins: [
    new webpack.LoaderOptionsPlugin({ debug: false }),
    new webpack.optimize.CommonsChunkPlugin({ name: 'vendor', filename: 'vendor.bundle.js' }),
    new UglifyJs(),
  ]
});
