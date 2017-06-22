const webpack = require('webpack');

const base = require('./base');

module.exports = Object.assign({}, base, {
  cache: true,
  devtool: 'inline-source-map',
  plugins: [
    new webpack.LoaderOptionsPlugin({ debug: false }),
    new webpack.optimize.CommonsChunkPlugin({ name: 'vendor', filename: 'vendor.bundle.js' }),
  ]
});
