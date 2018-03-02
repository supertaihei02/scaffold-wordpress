const webpack = require('webpack');

const base = require('./base');
const conf = require('../config');

module.exports = Object.assign({}, base, {
  mode: 'development',
  devtool: 'inline-source-map',
  plugins: [
    new webpack.LoaderOptionsPlugin({ debug: true }),
    new webpack.DllReferencePlugin({
      manifest: conf.script.dll.manifest,
      context: process.cwd(),
    }),
  ]
});
