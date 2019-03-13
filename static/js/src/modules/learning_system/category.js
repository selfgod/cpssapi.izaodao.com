var $ = require('jquery');
var utils = require('../../public/util');
var _highLight = 'title_current';
var _current = {};

module.exports = {
    onClick: function (target, callback) {
        var self = this;
        $(utils.lsParentId()).on('click', '[data-name="' + target + '"] .title_v422 ul li', function () {
            var ele = $(this);
            if (!ele.hasClass(_highLight)) {
                ele.addClass(_highLight).siblings().removeClass(_highLight);
                callback(ele);
            }
        });
    },
    setCurrent: function (name, category) {
        _current[name] = category;
    },
    getCurrent: function (name) {
        return _current[name];
    }
};
