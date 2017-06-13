'use strict';

const Encore = require('@symfony/webpack-encore');
const merge = require('webpack-merge');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableLessLoader()
    .enableSourceMaps(!Encore.isProduction())
    .addStyleEntry('red', './assets/less/main.less')
    .addStyleEntry('night', './assets/less/main-night.less')
    .createSharedEntry('vendor', ['bazinga-translator', 'jquery', 'moment/src/moment', 'underscore'])
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
