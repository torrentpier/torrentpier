'use strict';

const gulp = require('gulp');
const sourcemaps = require('gulp-sourcemaps');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

// Config
const paths = {
  styles: {
    src: './styles/templates/default/css/*.css',
    css: './styles/templates/default/css/',
  },
};

function styles() {
  return gulp.src(paths.styles.src)
    .pipe(sourcemaps.init())
    .pipe(
      postcss([
        autoprefixer(),
        sorting(sortOrder),
      ]),
    )
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.styles.css));
}
