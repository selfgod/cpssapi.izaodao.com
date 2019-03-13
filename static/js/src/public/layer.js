var _ = require('lodash');
var $ = require('jquery');
var layer = require('layer');
module.exports = {
    show: function (content, params) {
        params = params || {};
        var options = _.assign({
            type: 1,
            skin: 'izaodao5',
            title: false,
            resize: false,
            move: false,
            area: ['460px', '260px'],
            shade: 0.6,
            content: content
        }, params);
        return this.layer.open(options);
    },
    //单行弹出层
    lineShow: function (content, params) {
        params.area = ['460px', '230px'];
        var layerIndex = this.show(content, params);
        $('.layui-layer-btn').css('top', '140px');
        return layerIndex;
    },
    //单行弹出层
    showOneLine: function (title, params) {
        params.area = ['460px', '230px'];
        var hbs = require('./template/common_prompt.hbs');
        this.show(hbs({title: title}), params);
        $('.layui-layer-btn').css('top', '140px');
    },
    //两行弹出层
    showTwoLine: function (title, subTitle, params) {
        var hbs = require('./template/common_prompt.hbs');
        this.show(hbs({title: title, sub_title: subTitle}), params);
    },
    showMsg: function (data, callback) {
        this.layer.msg(data.msg, {
            time: 1000
        }, function () {
            callback(data);
        });
    },
    layer: layer
};
