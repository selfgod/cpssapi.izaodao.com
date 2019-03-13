var util = require('public/util.js');
var layerPop = require('public/layer');
module.exports = {
    callJson: function (method, url, data, callbacks, options) {
        var oldError;
        callbacks = callbacks || {};
        if (callbacks.error) {
            oldError = callbacks.error;
        }
        callbacks.login = function () {
            layerPop.showOneLine('您目前是退出状态，请先登录后再试', {
                btn: ['去登录'],
                yes: function () {
                    window.location.reload();
                }
            });
        };
        callbacks.error = function (xhr, status) {
            if (xhr.status === 0) {
                alert('请求失败，请稍后重试');
            }
            if (oldError) {
                oldError(xhr, status);
            }
        };
        if (method === 'get') {
            data.v = Math.random();
        }
        util.callJson(method, url, data, callbacks, options);
    }
};
