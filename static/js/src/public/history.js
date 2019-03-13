var $ = require('jquery');
module.exports = {
    push: function (url, params) {
        var str = '';
        if (window.history && window.history.pushState) {
            if (params) {
                str = '?' + $.param(params);
            }
            window.history.pushState(null, null, url + str);
        }
    }
};
