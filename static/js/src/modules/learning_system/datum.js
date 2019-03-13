var $ = require('jquery');
var schedule = require('./schedule');
var subCategory = require('./sub_category');
var pagination = require('../../public/pagination');

var _name = 'datum_right_panel';

module.exports = {
    init: function () {
        var self = this;
        subCategory.onClick(_name, function (element) {
            var type = element.data('name');
            var params = {
                type: type,
                page: 1
            };
            self.loadDetail(null, params, true);
        });

        //分页点击
        pagination.bind(_name, function (page) {
            var type = subCategory.getCurrent(_name);
            var params = {
                type: type,
                page: page
            };
            self.loadDetail(null, params, true);
        });
    },
    isValidType: function (type) {
        return (type && type.length > 0 &&
        $.inArray(type, ['0', '1', '2', '3']) !== -1);
    },
    loadDetail: function (callback, params, loading) {
        var url = schedule.buildUrl('schedule/detail/datum');
        params = params || {};
        params.type = params.type || 0;
        params.page = params.page || 1;

        var success = {
            success: function (data) {
                if (callback) {
                    callback(null, data);
                } else {
                    $('#detail_content').html(data);
                }
            }
        };

        if (loading) {
            schedule.loadWithLoading('get', url, params, success, {}, '#detail_content');
        } else {
            schedule.load('get', url, params, success, {});
        }
    }
};
