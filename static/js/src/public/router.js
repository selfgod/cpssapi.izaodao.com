var $ = require('jquery');
var utils = require('./util');
module.exports = {
    routes: {},
    init: function () {
        var self = this;
        if (!utils.isOldBrowser(9)) {
            window.addEventListener('popstate', function () {
                self.handleCurrentUrl();
            });
        }
        $('body').on('click', '.link', function (e) {
            var link = $(this).data('link');
            self.goHashUrl(link);
        });
    },
    /**
     * 处理当前地址
     */
    handleCurrentUrl: function () {
        var controller;
        var args = {};
        var currentUrl = location.hash.slice(1) || '/';
        if (currentUrl.indexOf('?') > 0) {
            var currentUrlArr = currentUrl.split('?');
            controller = currentUrlArr[0];
            var thisArg = currentUrlArr[1];
            if (thisArg && typeof (thisArg) !== 'undefined') {
                thisArg.split('&').forEach(function (pair) {
                    pair = pair.split('=');
                    args[pair[0]] = decodeURIComponent(pair[1] || '');
                });
                args = JSON.parse(JSON.stringify(args));
            }
        } else {
            controller = currentUrl;
        }
        if (this.routes[controller]) {
            this.routes[controller](args);
        }
    },
    goHashUrl: function (link) {
        if (!utils.isOldBrowser(9)) {
            window.history.pushState(null, null, '#' + link);
        } else {
            $.hash.go(link);
        }
        this.handleCurrentUrl();
    },
    /**
     * 设置路由
     * @param path
     * @param callback
     */
    route: function (path, callback) {
        this.routes[path] = callback || function () {};
    },
    /**
     * 后退
     */
    goBack: function () {
        window.history.back();
    }

};
