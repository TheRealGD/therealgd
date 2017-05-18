'use strict';

const fontello = require('gulp-fontello');
const gulp = require('gulp');
const less = require('gulp-less');
const LessNpmImport = require('less-plugin-npm-import');
const rename = require('gulp-rename');
const uglifycss = require('gulp-uglifycss');
const gutil = require('gulp-util');
const webpack = require('webpack');

gulp.task('default', ['css', 'fonts', 'js']);

gulp.task('css', () => {
    return gulp.src('./src/AppBundle/Resources/assets/less/main*.less')
        .pipe(less({
            plugins: [new LessNpmImport({prefix: '~'})]
        }))
        .pipe(uglifycss())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('./web/css/'));
});

gulp.task('fonts', () => {
    return gulp.src('./fontello.json')
        .pipe(fontello())
        .pipe(gulp.dest('./web/fonts'));
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

