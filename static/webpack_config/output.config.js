var dir = require('./dir.config');
module.exports = {
    path: dir.dist, //输出目录的配置，模板、样式、脚本、图片等资源的路径配置都相对于它
    publicPath: '/static/js/dist/', //模板、样式、脚本、图片等资源对应的server上的路径
    filename: '[name].js?v=[chunkhash:8]',            //每个页面对应的主js的生成配置
    chunkFilename: '[name].js'   //chunk生成的配置
};