'use strict';

const webpack = require('webpack');
const path = require('path');

module.exports = {
    devtool: '#source-map',
    entry: {
        main: __dirname + '/src/AppBundle/Resources/assets/js/main.js'
    },
    output: {
        path: __dirname + '/web/js',
        filename: '[name].min.js'
    },
    module: {
        loaders: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader'
            }
        ]
    },
    plugins: [
        new webpack.optimize.UglifyJsPlugin({
            compress: { warnings: false },
            sourceMap: true
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: function (module) {
                return module.context && /node_modules/.test(module.context);
            }
        }),
    ],
    resolve: {
        mainFields: ['jsnext:main', 'main']
    }
};
