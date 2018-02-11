'use strict';

const Encore = require('@symfony/webpack-encore');
const merge = require('webpack-merge');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableLessLoader()
    .enableSourceMaps(!Encore.isProduction())
    .addStyleEntry('red', './assets/less/main.less')
    .addStyleEntry('night', './assets/less/main-night.less')
    .createSharedEntry('vendor', [
        'babel-polyfill',
        'bazinga-translator',
        'date-fns/distance_in_words',
        'date-fns/distance_in_words_to_now',
        'date-fns/is_before',
        'jquery',
        'underscore',
    ])
    .addEntry('main', './assets/js/main.js')
    .configureBabel(babelConfig => {
        babelConfig.presets.push(['es2015', { modules: false }]);
        babelConfig.plugins = ['syntax-dynamic-import'];
    })
    .enableVersioning();

module.exports = merge(Encore.getWebpackConfig(), {
    externals: {
        "fosjsrouting": "Routing"
    },
});
