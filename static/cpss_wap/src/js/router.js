var $ = require('jquery');
module.exports = {
    routes: {},
    init: function () {
        var self = this;
        window.addEventListener('popstate', function (event) {
            var currentUrl = location.hash.slice(1) || '/';
            if (self.routes[currentUrl]) {
                self.routes[currentUrl]();
            }
        });

        $('body').on('click', '.link', function (e) {
            var link = $(this).data('link');
            window.history.pushState(null, null, link);
            var currentUrl = location.hash.slice(1) || '/';
            if (self.routes[currentUrl]) {
                self.routes[currentUrl]();
            }
        });
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
