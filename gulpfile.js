'use strict';

const gulp = require('gulp');
const concat = require('gulp-concat');
const minify = require('gulp-minify');

// Config
const paths = {
    javascript: {
        src: ['./styles/js/src/*.js'],
        concat: 'scripts.js',
        output: './styles/js/'
    },
    styles: {
        src: ['./styles/js/src/*.js'],
    }
};

gulp.task('build-js', function () {
    return gulp.src(paths.javascript.src)
        .pipe(concat(paths.javascript.concat))
        .pipe(minify())
        .pipe(gulp.dest(paths.javascript.output));
});
