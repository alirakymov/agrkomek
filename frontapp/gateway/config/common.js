'use strict';

const webpack = require('webpack'),
    path = require('path'),
    fs = require('fs'),
    _ = require('lodash'),
    publicPath = '/static-gateway/',
    MiniCssPlugin = require('mini-css-extract-plugin'),
    HtmlPlugin = require('html-webpack-plugin'),
    { VueLoaderPlugin } = require('vue-loader'),
    { CleanWebpackPlugin } = require('clean-webpack-plugin'),
    { bundler, styles } = require('@ckeditor/ckeditor5-dev-utils');

module.exports = function(_path) {

    let plugins = [];

    let optimization = {
        splitChunks: {
            chunks: 'async',
            cacheGroups: {
                qore: {
                    test: /[\\/]qore[\\/]/,
                    name(module, chunks, cacheGroupKey) {
                        return 'qore';
                    },
                    priority: -30,
                    chunks: 'all',
                    reuseExistingChunk: true
                },
                vendors: {
                    test: /[\\/]node_modules[\\/].*\.js$/,
                    name(module, chunks, cacheGroupKey) {
                        return module.context.match(/[\\/]node_modules[\\/]([^\\/]*)/)[1].replace(/[\W]+/g,"_");
                    },
                    priority: -10,
                    chunks: 'all',
                    reuseExistingChunk: true
                }
            }
        }
    };

    plugins.push(
        /* ------------------------------------------
         * - Раскидываем наш css по файлам
         * ------------------------------------------ */
        new MiniCssPlugin({
            // Options similar to the same options in webpackOptions.output all options are optional
            filename: 'assets/css/[name].bundle.[hash].css',
            chunkFilename: 'assets/css/[id].[hash].css',
            ignoreOrder: false, // Enable to remove warnings about conflicting order
        })
        /* ------------------------------------------
         * - Куда же без нашего родного jQuery
         * ------------------------------------------ */
        , new webpack.ProvidePlugin({
            WysiwygEditor: ['_scripts/plugins/ckeditor/ckeditor.js', 'default'],
        })

        , new VueLoaderPlugin()

        , new webpack.DefinePlugin({
            PUBLIC_PATH: publicPath
        })

        , new CleanWebpackPlugin()
    );

    /* - ---------------------------------------------
     * - Собираем страницы через html-webpack-plugin
     * - Для создания новой страницы достаточно создать файл (*.html) в директории ./app/pages
     * - Страницы можно делить на компоненты согласно принятому в es6 шаблонному синтаксису
     * - Подробнее смотри на https://github.com/webpack-contrib/html-loader
     * - ---------------------------------------------
     * - Walk - Функция для рекурсивного сбора файлов в директории
     * - --------------------------------------------- */
    var walk = function(dir) {
        var results = {};
        var list = fs.readdirSync(dir);
        list.forEach(function(file) {
            var filePath = dir + '/' + file;
            var stat = fs.statSync(filePath);
            if (stat && stat.isDirectory()) {
                /* Recurse into a subdirectory */
                results = _.merge(results, walk(filePath));
            } else {
                /* Is a file */
                results[filePath] = filePath.substr((_path).length + 1);
            }
        });
        return results;
    }


    _.each(walk(_path + '/templates'), function(fileName, filePath) {
        if (fileName.match(/\.twig$/)) {
            plugins.push(new HtmlPlugin({
                filename: fileName,
                template: filePath,
                inject: false,
                scriptLoading: 'module',
            }));
        }
    });

    return {

        /* ------------------------------------------
         * - Блок настроек точек входа
         * -- app: основной файл FE
         * ------------------------------------------ */
        entry: _.merge(
            {
                app: _path + '/src/app.js',
                'app.worker': _path + '/src/app.worker.js',
            },
        ),

        optimization: optimization,

        /* ------------------------------------------
         * - Блок настроек вывода
         * -- path: путь до корневого каталога сборки
         * -- filename: шаблон скомпилированного entry-файла
         * -- publicPath: глобальный путь (идет префиксом) к ассетам
         * ------------------------------------------ */
        output: {
            path: path.join(_path, 'public'),
            filename: path.join('assets', 'js', '[name].bundle.js'),
            chunkFilename: path.join('assets', 'js', '[name].js'),
            publicPath: publicPath
        },

        /* ------------------------------------------
         * - Настройки к сторонним библиотекам
         * -- modulesDirectories: директория модулей nodejs
         * -- alias: namespace-ы для удбоного require('_namespace/some.pug')
         * ------------------------------------------ */
        resolve: {
            alias: {
                // - Алиасы статики
                _svg: path.join(_path, 'src', 'assets', 'svg'),
                _images: path.join(_path, 'src', 'assets', 'images'),
                _fonts: path.join(_path, 'src', 'assets', 'fonts'),
                _styles: path.join(_path, 'src', 'assets', 'scss'),
                // - Node modules
                _node: path.join(_path, 'src', 'node_modules'),
                // - скрипты
                _scripts: path.join(_path, 'src', 'app'),
                // - TODO: не смог настроить wp на поиск css в node_modules
                '@ckeditor': path.join(_path, 'src', 'node_modules', '@ckeditor'),
                './@ckeditor': path.join(_path, 'src', 'node_modules', '@ckeditor'),
            }
        },

        /* ------------------------------------------
         * - Настройки loader-ов.
         * - Эти настройки отвечают за подгрузку нужных типов файлов
         *   Очень мощный инструмент, а значит имеет множество нюансов.
         *   https://webpack.github.io/docs/using-loaders.html
         * ------------------------------------------ */
        module: {
            rules: [
                /* - JS файлы */
                {
                    test: /\.js$/,
                    use: [{
                        loader: 'babel-loader',
                        options: {
                            plugins: ['lodash'],
                            presets: [
                                [
                                    "@babel/env",
                                    {
                                        "targets": {
                                            esmodules: true,
                                        }
                                    }
                                ]
                            ],

                        }
                    }]
                },
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    options: {
                        loaders: {
                            // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
                            // the "scss" and "sass" values for the lang attribute to the right configs here.
                            // other preprocessors should work out of the box, no loader config like this necessary.
                            'scss': 'vue-style-loader!css-loader!sass-loader',
                            'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
                        }
                        // other vue-loader options go here
                    }
                },
                /* - Css стили */
                {

                    test: /\.(css|scss|sass)$/,
                    exclude: [
                        /ckeditor5/,
                    ],
                    use: [
                        { loader: MiniCssPlugin.loader },
                        'css-loader', 'sass-loader'
                    ]
                },
                /* - Разного рода статика */
                {
                    test: /\.(ttf|eot|woff|woff2|png|ico|jpg|jpeg|gif|svg|otf|webp)(\?.*$|$)/,
                    exclude: [
                        /ckeditor5/,
                    ],
                    use: ['file-loader?name=assets/static/[ext]/[name].[hash].[ext]&publicPath=' + publicPath]
                },
                {
                    test: /\.webmanifest$/i,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: function(resourcePath, query) {
                                    return path.join(
                                        // '..',
                                        // 'public',
                                        '[name].[ext]'
                                    );
                                },
                                publicPath: function(url) {
                                    return publicPath + url;
                                },
                                esModule: false,
                            },
                        },
                        {
                            loader: 'webpack-webmanifest-loader',
                        }
                    ],
                },
                {
                    test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
                    use: ['raw-loader']
                },
                {
                    test: /ckeditor5.*\.css$/,
                    use: [
                        { loader: MiniCssPlugin.loader },
                        'css-loader',
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: styles.getPostCssConfig( {
                                    themeImporter: {
                                        themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
                                    },
                                    minify: true
                                } )
                            }
                        }
                    ]
                }
            ]
        },

        /* -----------------------------------------
         * Plugins - Еще один крутой инструмент webpack-a, позволяющий
         * влиять на разные стадии компиляции.
         * Соответственно есть возможность выцепить и распределить все так
         * как нам будет удобно.
         * Что мы и делаем через плагин html-webpack-plugin.
         * ------------------------------------------ */
        plugins: plugins
    };
};
