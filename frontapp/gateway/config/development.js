'use strict';

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

let _ = require('lodash')
    , webpack = require('webpack')
    , common = require('./common.js');

/**
 * Development config
 */
module.exports = function(_path) {

    return {
        context: _path,
        devtool: 'eval-cheap-module-source-map',
        mode: 'development',
        watch: true,
        plugins: _.concat(common(_path).plugins, [
            // new BundleAnalyzerPlugin(),
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: true,
                __VUE_PROD_DEVTOOLS__: true,
            })
        ]),
    }
};
