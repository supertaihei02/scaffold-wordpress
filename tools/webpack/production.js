const webpack = require('webpack');

const base = require('./base');
const conf = require('../config');

process.noDeprecation = true;

module.exports = Object.assign({}, base, {
  mode: 'production',
  plugins: [
    new webpack.LoaderOptionsPlugin({ debug: false }),
    new webpack.DllReferencePlugin({
      manifest: conf.script.dll.manifest,
      context: process.cwd(),
    }),
  ]
});
