var $ = require('jquery');
var _ = require('lodash');
var async = require('async');
var popup = require('../../public/popup');
var utils = require('../../public/util.js');
var category = require('./category');
var subCategory = require('./sub_category');
var pagination = require('../../public/pagination');
var schedule = require('./schedule');
var notice = require('./notice');
var user = require('public/user');

var _subCategoryMap = {
    all: 0,
    finished: 1,
    unfinished: 2
};

var _name = 'exercise_right_panel';

module.exports = {
    name: 'exercise_right_panel',
    init: function () {
        var self = this;
        //一课一练/单元测试
        category.onClick(_name, function (element) {
            var current = element.data('name');
            var params;
            category.setCurrent(_name, current);
            subCategory.setCurrent(_name, 'all');
            if (current === 'test') {
                params = {
                    category: 'test'
                };
                self.loadDetail(null, params, true);
            } else if (current === 'unit') {
                params = {
                    category: 'unit'
                };
                self.loadDetail(null, params, true);
            }
            notice.showTypeNotice(current);
        });
        //子分类点击：全部/已完成/未完成
        subCategory.onClick(_name, function (element) {
            var type = element.data('name');
            var params = {
                category: category.getCurrent(_name),
                type: _subCategoryMap[type],
                page: 1
            };
            if (type === 'unfinished') {
                notice.removePoint(category.getCurrent(_name));
            }
            self.loadDetail(null, params, true);
        });
        //分页点击
        pagination.bind(_name, function (page) {
            var type = _subCategoryMap[subCategory.getCurrent(_name)];
            var params = {
                category: category.getCurrent(_name),
                type: type,
                page: page
            };
            self.loadDetail(null, params, true);
        });

        $(utils.lsParentId()).on('click', '.exec', function () {
            var element = $(this);
            if (element.hasClass('over_30min')) {
                user.isOverLimit(function () {
                    popup.handleOver30min(element);
                });
            } else if (element.hasClass('retest')) {
                user.isOverLimit(function () {
                    popup.handleRetest(element);
                });
            } else if (element.hasClass('not_start')) {
                var parent = element.closest('ul');
                popup.handleNotStart(element, parseInt(parent.data('starttime'), 10));
            } else {
                user.isOverLimit(function () {
                    window.open(element.data('url'));
                });
            }
        });
    },
    isValidCategory: function (cate) {
        return (cate && cate.length > 0 &&
        $.inArray(cate, ['unit', 'test']) !== -1);
    },
    isValidType: function (type) {
        return (type && type.length > 0 &&
        $.inArray(type, ['0', '1', '2']) !== -1);
    },
    loadNav: function (callback, params) {
        params = params || {};
        if (params.category) {
            category.setCurrent(_name, params.category);
        }
        var url = schedule.buildUrl('menu/exercise');
        schedule.load('get', url, params, {
            success: function (data) {
                callback(null, data);
            }
        });
    },
    loadDetail: function (callback, params, loading) {
        var url = schedule.buildUrl('schedule/detail/exercise');
        params = params || {};
        if (_.isUndefined(params.type)) {
            params.type = '0';
        }
        if (_.isUndefined(params.category)) {
            params.category = 'test';
        }
        if (_.isUndefined(params.page)) {
            params.page = 1;
        }
        category.setCurrent(_name, params.category);
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
            schedule.load('get', url, params, success);
        }
    },
    // loadPanel: function (params, callback) {
    //
    // },
    loadComplete: function () {
        notice.showCategoryExercise();
        notice.showTypeNotice(category.getCurrent(_name));
    }
};
