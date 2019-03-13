var webpack = require('webpack');
var uglifyJsPlugin = webpack.optimize.UglifyJsPlugin;
var HtmlWebpackPlugin = require('html-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var failPlugin = require('webpack-fail-plugin');
var path = require('path');
var appDir = path.resolve(__dirname, '../../application');

module.exports = {
    entry: { //配置入口文件，有几个写几个
        index: './src/js/index.js',
        // vendor: ['./js/src/lib/layer/layer.js']
    },
    output: {
        path: 'dist', //输出目录的配置，模板、样式、脚本、图片等资源的路径配置都相对于它
        publicPath: '/static/cpss_wap/dist/', //模板、样式、脚本、图片等资源对应的server上的路径
        filename: '[name].js?v=[chunkhash:8]',            //每个页面对应的主js的生成配置
        chunkFilename: '[name].js'   //chunk生成的配置
    },
    // devtool: 'cheap-module-source-map',
    module: {
        preLoaders: [
            {
                test: /\.js$/,
                loader: "eslint-loader",
                exclude: /node_modules/
            },
            // {
            //     test: /\.js$/,
            //     loader: "source-map-loader",
            //     exclude: /node_modules/
            // }
        ],
        loaders: [
            {
                test: /\.(jpg|png|jpeg|gif)$/,
                loader: 'url?name=images/[name].[ext]&limit=1024'
                // loader: "file-loader?name=[name].[ext]&publicPath=assets/foo/&outputPath=app/images/"
            },
            {
                test: /\.hbs$/,
                loader: 'handlebars-loader',
                query: { inlineRequires: '\/images\/' }
            },
            {
                test: /\.css$/,
                loader: ExtractTextPlugin.extract('style-loader', 'css-loader'),
            }

        ]
    },
    watchOptions: {
        poll: 1000
    },
    resolve: {
        alias: {
            public: path.resolve(__dirname, './js/src/public')
        }
    },
    eslint: {
        failOnWarning: false,
        failOnError: true,
        configFile: '../.eslintrc'
    },
    externals: {
        // jquery: 'jQuery',
        // lodash: '_',
        // async: 'async'
    },
    plugins: [
        failPlugin,
        // new webpack.optimize.CommonsChunkPlugin({
        //     // names: ['common', 'vendor'],
        //     name: 'common', // 将公共模块提取
        //     chunks: [], //提取哪些模块共有的部分
        //     minChunks: 4 // 提取至少n个模块共有的部分
        // }),
        new ExtractTextPlugin('css/[name].css?v=[chunkhash:8]'),
        new HtmlWebpackPlugin({
            filename: appDir + '/modules/learning_center_wap/views/main.php',
            template: 'views/main.php',
            chunks: ['index'],
            inject:'body'
        })
        // new uglifyJsPlugin({
        //     compress: {
        //         warnings: false
        //     },
        //     output: {
        //         comments: false
        //     },
        //     except: ['$', 'exports', 'require']
        // })
    ]
};