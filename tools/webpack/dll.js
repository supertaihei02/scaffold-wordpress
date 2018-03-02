const path = require('path');
const webpack = require('webpack');

const { paths } = require('./base');
const conf = require('../config');
const { dependencies } = require(path.join(process.cwd(), 'package.json'));

module.exports = {
  entry: {
    vendor: Object.keys(dependencies).filter(name => !conf.script.dll.ignore.includes(name)),
  },
  output: {
    library: conf.script.dll.library,
    path: conf.script.dll.path.dll,
    filename: 'vendor.dll.js',
  },
  mode: 'production',
  plugins: [
    new webpack.DefinePlugin({
      NODE_ENV: '"production"',
    }),
    new webpack.DllPlugin({
      path: conf.script.dll.path.manifest,
      name: conf.script.dll.library,
    }),
  ],
};
