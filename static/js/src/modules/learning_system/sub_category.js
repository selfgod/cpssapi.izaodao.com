var $ = require('jquery');
var _ = require('lodash');
var utils = require('../../public/util');
var _highLight = 'current_v422';
var _current = {};

module.exports = {
    onClick: function (target, callback) {
        var self = this;
        $(utils.lsParentId()).on('click', '[data-name="' + target + '"] .sub_category ul li', function () {
            var ele = $(this);
            if (!ele.hasClass(_highLight)) {
                self.setCurrent(target, ele);
                if (callback) {
                    callback(ele);
                }
            }
        });
    },
    setCurrent: function (target, value) {
        var name;
        if (_.isString(value)) {
            name = value;
            value = $('[data-name="' + target + '"] .sub_category ul li[data-name="' + value + '"]');
        } else {
            name = value.data('name');
        }
        value.addClass(_highLight).siblings().removeClass(_highLight);
        _current[target] = name;
    },
    getCurrent: function (target) {
        return _current[target];
    }
};
