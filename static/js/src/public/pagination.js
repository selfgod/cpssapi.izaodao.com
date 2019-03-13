var $ = require('jquery');
var _highLight = 'page_v422_current';
var _ = require('lodash');
var targetObj;
module.exports = {
    bind: function (target, callback, highLight) {
        if (highLight) {
            _highLight = highLight;
        }
        targetObj = '[data-name="' + target + '"]';
        $('body').on('click', targetObj + ' #pagination p', function (event) {
            event.stopPropagation();
            var page;
            var element = $(this);
            if (!_.isEmpty(element.data('name'))) {
                //点击前一页或后一页
                var action = element.data('name');
                page = parseInt($('#pagination .' + _highLight).text().trim(), 10);
                if (action === 'previous') {
                    page--;
                } else if (action === 'next') {
                    page++;
                } else if (action === 'first') {
                    page = 1;
                } else if (action === 'last') {
                    page = element.data('total');
                }
                if (callback) {
                    callback(page);
                }
            } else if (!element.hasClass(_highLight)) {
                element.addClass(_highLight).siblings().removeClass(_highLight);
                page = parseInt(element.text().trim(), 10);
                if (callback) {
                    callback(page);
                }
            }
        });
    }
};
