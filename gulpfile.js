'use strict';

const gulp = require('gulp');
const less = require('gulp-less');
const LessNpmImport = require('less-plugin-npm-import');
const rename = require('gulp-rename');
const uglifycss = require('gulp-uglifycss');
const gutil = require('gulp-util');
const webpack = require('webpack');

gulp.task('default', ['css', 'js']);

gulp.task('css', () => {
    return gulp.src('./src/AppBundle/Resources/assets/less/main.less')
        .pipe(less({
            plugins: [new LessNpmImport({prefix: '~'})]
        }))
        .pipe(uglifycss())
        .pipe(rename('main.min.css'))
        .pipe(gulp.dest('./web/css/'));
});

const webpackCache = {};

gulp.task('js', (done) => {
    const config = require('./webpack.config');
    config.cache = webpackCache;

    webpack(config).run((err, stats) => {
        if (err) {
            throw new gutil.PluginError('webpack', err);
        }

        gutil.log('[webpack]', stats.toString({
            chunks: false,
            colors: true
        }));

        done();
    });
});

gulp.task('watch', ['css', 'js', 'watch:css', 'watch:js']);

gulp.task('watch:css', () => {
    return gulp.watch('./src/AppBundle/Resources/assets/less/**/*.*', ['css']);
});

gulp.task('watch:js', () => {
    return gulp.watch('./src/AppBundle/Resources/assets/js/**/*.*', ['js']);
});

