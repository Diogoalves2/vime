const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const imagemin = require('gulp-imagemin');
const mozjpeg = require('imagemin-mozjpeg');
const pngquant = require('imagemin-pngquant');
const webp = require('imagemin-webp');
const cleanCSS = require('gulp-clean-css');
const terser = require('gulp-terser');
const { series, parallel } = require('gulp');
const del = require('del');


// Compilando o sass, adicionando autoprefixed e dando refresh na pagina
function compilaSass() {
  return gulp.src('scss/*.scss')
  .pipe(sass())
  .pipe(autoprefixer({
    overrideBrowserslist: ['last 2 versions'],
    cascade: false,
  }))
  .pipe(gulp.dest('css/'))
  .pipe(browserSync.stream());
}
// tarefa do sass
gulp.task('sass', compilaSass);

function pluginsCSS() {
  return gulp.src('css/lib/*.css')
  .pipe(concat('plugins.css'))
  .pipe(gulp.dest('css/'))
  .pipe(browserSync.stream())
}

gulp.task('plugincss', pluginsCSS);

function gulpJs() {
  return gulp.src('js/scripts/*.js')
  .pipe(concat('all.js'))
  .pipe(babel({
      presets: ['@babel/env']
  }))
  .pipe(uglify())
  .pipe(gulp.dest('js/'))
  .pipe(browserSync.stream());
}
gulp.task('alljs', gulpJs);

function pluginsJs() {
  return gulp
  .src(['./js/lib/aos.min.js','./js/lib/swiper.min.js'])
  .pipe(concat('plugins.js'))
  .pipe(gulp.dest('js/'))
  .pipe(browserSync.stream())
}

gulp.task('pluginjs', pluginsJs);

// funcao do browsersync
function browser() {
  browserSync.init({
    server: {
      baseDir: './'
    }
  })
}
//tarefa do browsersync
gulp.task('browser-sync', browser);

//funcao do watch para alteracoes em scss e html
function watch() {
  gulp.watch('scss/*.scss', compilaSass);

  gulp.watch('css/lib/*.css', pluginsCSS);

  gulp.watch('*.html').on('change', browserSync.reload);

  gulp.watch('js/scripts/*js', gulpJs);

  gulp.watch('js/lib/*.js', pluginsJs);
}
//tarefa do watch
gulp.task('watch', watch);

function optimizeImages() {
  return gulp.src('img/**/*')
    .pipe(imagemin([
      mozjpeg({ quality: 75, progressive: true }),
      pngquant({ quality: [0.6, 0.8] }),
      webp({ quality: 75 })
    ]))
    .pipe(gulp.dest('img/optimized'));
}

exports.optimizeImages = optimizeImages;

function styles() {
  return gulp.src('scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(gulp.dest('css'));
}

function scripts() {
  return gulp.src('js/**/*.js')
    .pipe(terser())
    .pipe(gulp.dest('js/dist'));
}

exports.styles = styles;
exports.scripts = scripts;

function clean() {
  return del(['dist', 'css/maps', '*.map']);
}

const production = series(
  clean,
  parallel(
    compilaSass,
    gulpJs,
    pluginsCSS,
    pluginsJs
  ),
  optimizeImages
);

exports.production = production;

// tarefas default que executa o watch e o browsersync
gulp.task('default', gulp.parallel('watch', 'browser-sync', 'sass', 'plugincss', 'alljs', 'pluginjs'));