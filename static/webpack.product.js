var webpack = require('webpack');
var uglifyJsPlugin = webpack.optimize.UglifyJsPlugin;
var HtmlWebpackPlugin = require('html-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var path = require('path');

module.exports = {
    entry: require('./webpack_config/entry.config.js'),
    output: require('./webpack_config/output.config.js'),
    // devtool: 'cheap-module-source-map',
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'eslint-loader',
                enforce: 'pre',
                exclude: /node_modules/,
                query: {
                    failOnWarning: false,
                    failOnError: true,
                    configFile: './.eslintrc'
                }
            },
            {
                test: /\.tpl$/,
                loader: 'underscore-template-loader',
                options: {engine: 'lodash', attributes: []}
            },
            {
                test: /\.hbs$/,
                loader: 'handlebars-loader',
                query: {inlineRequires: '\/img\/', helperDirs: path.resolve(__dirname, './js/src/public/hbs_helpers')}
            },
            {
                test: /\.css$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader'
                })
            },
            {
                test: /\.(jpg|png|jpeg|gif)$/,
                loader: 'url-loader',
                query: {
                    name: 'images/[name].[ext]?v=[hash:8]',
                    limit: 2048
                }
            }
        ]
    },
    watchOptions: {
        poll: 1000
    },
    resolve: require('./webpack_config/resolve.config.js'),
    externals: require('./webpack_config/externals.config.js'),
    plugins: [
        new webpack.DefinePlugin({
            JP_DOMAIN: JSON.stringify('http://jp.izaodao.com/'),
            MAIN_DOMAIN: JSON.stringify('http://www.izaodao.com/'),
            CPSS_DOMAIN: JSON.stringify('http://cpss.izaodao.com/'),
            ZD_KNOW: JSON.stringify('http://know.izaodao.com/')
        }),
        new webpack.optimize.CommonsChunkPlugin({
            // names: ['common', 'vendor'],
            name: 'common', // 将公共模块提取
            chunks: ['lc', 'tc', 'review', 'purchased', 'p_detail', 'zdtalk', 'unit_enter',
                'unit_exam', 'unit_rest', 'unit_review', 'unit_result', 'level_test',
                'level_review', 'level_result', 'level_index', 'level_generate', 'level_notice'], //提取哪些模块共有的部分
            minChunks: 10 // 提取至少n个模块共有的部分
        }),
        new ExtractTextPlugin('css/[name].css?v=[contenthash:8]'),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/exam/views/unit_result_main.php'),
            template: 'views/unit_test.php',
            chunks: ['common', 'unit_result'],
            inject: 'body'
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/exam/views/unit_review_main.php'),
            template: 'views/unit_test.php',
            chunks: ['common', 'unit_review'],
            inject: 'body'
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/exam/views/unit_rest_main.php'),
            template: 'views/unit_test.php',
            chunks: ['common', 'unit_rest'],
            inject: 'body'
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/exam/views/unit_prepare_main.php'),
            template: 'views/unit_test.php',
            chunks: ['common', 'unit_enter'],
            inject: 'body'
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/exam/views/unit_exam_main.php'),
            template: 'views/unit_test.php',
            chunks: ['common', 'unit_exam'],
            inject: 'body'
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_center/views/main.php'),
            template: 'views/learning_center_main.php',
            chunks: ['common', 'lc'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_center/views/purchased_main.php'),
            template: 'views/main_white.php',
            chunks: ['common', 'purchased'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_center/views/purchased_detail_main.php'),
            template: 'views/main_white.php',
            chunks: ['common', 'p_detail'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_system/views/review_page.php'),
            template: 'views/review_main.php',
            chunks: ['common', 'review'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/teaching_center/views/main.php'),
            template: 'views/main_white.php',
            chunks: ['common', 'tc'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/upgrade/views/upgrade.php'),
            template: 'views/upgrade_main.php',
            chunks: ['upgrade'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/upgrade/views/accounting.php'),
            template: 'views/upgrade_main.php',
            chunks: ['accounting'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_center/views/privilege_main.php'),
            template: 'views/main_white.php',
            chunks: ['common'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/learning_center/views/zdtalk_main.php'),
            template: 'views/zdtalk_main.php',
            chunks: ['common', 'zdtalk'],
            inject: 'body',
        }),
        new uglifyJsPlugin({
            compress: {
                warnings: false
            },
            output: {
                comments: false
            },
            except: ['$', 'exports', 'require']
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_test_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_test'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_review_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_review'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_result_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_result'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_index_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_index'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_generate_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_generate'],
            inject: 'body',
        }),
        new HtmlWebpackPlugin({
            filename: path.resolve(__dirname, '../application/modules/level_test/views/level_notice_main.php'),
            template: 'views/level_test.php',
            chunks: ['common', 'level_notice'],
            inject: 'body',
        })
    ]
};
