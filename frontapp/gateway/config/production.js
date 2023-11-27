'use strict';

const CompressionPlugin = require("compression-webpack-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

var
    _ = require('lodash')
    , webpack = require('webpack')
    , common = require('./common.js')
    , path = require('path')
;

module.exports = function(_path) {

    return {
        context: _path,
        output: {
            path: path.resolve(_path, 'public'),
            filename: path.join('assets', 'js', '[name].[fullhash].js'),
            chunkFilename: path.join('assets', 'js', '[name].[fullhash].js'),
        },
        watch: false,
        mode: 'production',
        devtool: 'hidden-source-map',
        target: ['web', 'es5'],
        optimization: {
            minimize: true,
            minimizer: [
                new CssMinimizerPlugin({
                    minimizerOptions: {
                        preset: [
                            "default",
                            {
                                discardComments: { removeAll: true },
                            },
                        ],
                    },
                }),
                new TerserPlugin(),
            ],
            chunkIds: 'total-size',
            moduleIds: 'size',
        },
        plugins: _.concat(common(_path).plugins, [
            new webpack.optimize.AggressiveMergingPlugin(),
            new CompressionPlugin({
                test: /\.(js|css)(\?.*)?$/i,
            }),
            new webpack.DefinePlugin({
              'process.env.NODE_ENV': JSON.stringify('production')
            }),
        ])
    }
};
