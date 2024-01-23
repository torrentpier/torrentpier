'use strict';

const gulp = require('gulp');
const concat = require('gulp-concat');

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
        .pipe(gulp.dest(paths.javascript.output));
});

gulp.task('build-css', function () {
    return gulp.src(['assets/css/main.css', 'assets/css/custom.css'])
        .pipe(concat('stylesheet.css'))
        .pipe(gulp.dest('public/build/css'));
});
