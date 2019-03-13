var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var async = require('async');
var category = require('./category');
var lesson = require('./lesson');
var exercise = require('./exercise');
var datum = require('./datum');
var report = require('./report');
var schedule = require('./schedule');
var preferred = require('./preferred_page');
var info = require('./info');
var history = require('public/history');
var loading = require('public/template/loading.tpl');
/**
 * 刚亮菜单class后缀
 * @type {string}
 * @private
 */
var _suffix = '_active';

var _actionClassMap = {
    book: {
        action: 'learn',
        obj: lesson
    },
    tadk: {
        action: 'exercise',
        obj: exercise
    },
    file: {
        action: 'datum',
        obj: datum
    },
    pipe: {
        action: 'report',
        obj: report
    },
    info: {
        action: 'info',
        obj: info
    }
};

var _initCategory = {
    book: 'live',
    tadk: 'practice'
};

module.exports = {
    preSelected: {},
    preSelectedClass: '',
    init: function () {
        var self = this;
        //菜单选择
        $(utils.lsParentId()).on('click', '.menu_v422 ul li span', function () {
            var element = $(this);
            var curClass = element.attr('class');
            if (curClass.indexOf(_suffix) === -1) {
                element.addClass(curClass + _suffix).removeClass(curClass);
                self.preSelected.addClass(self.preSelectedClass).removeClass(self.preSelectedClass + _suffix);
                self.preSelected = element;
                self.preSelectedClass = curClass;
                preferred.clear();
                history.push(schedule.id, {
                    plan: schedule.planId,
                    stage: schedule.planStageId,
                    nav: curClass
                });
                self.loadPanelDetail(curClass);
            }
        });

        //高亮菜单
        this.highLightMenu();
    },
    /**
     * 高亮菜单
     * @param use_default
     */
    highLightMenu: function (use_default) {
        var nav;
        var curMenu;
        if (preferred.nav && _actionClassMap[preferred.nav]) {
            nav = preferred.nav;
        }

        if (!nav || use_default) {
            curMenu = 'book';
        } else {
            curMenu = nav;
        }
        if (this.preSelected.length > 0) {
            this.preSelected.addClass(this.preSelectedClass).removeClass(this.preSelectedClass + _suffix);
        }
        var menu = $('.' + curMenu);
        menu.addClass(curMenu + _suffix).removeClass(curMenu);
        this.preSelected = menu;
        this.preSelectedClass = curMenu;
        this.loadPanelDetail(curMenu);
    },
    /**
     * 验证直接跳转到指定页面分类的参数是否正确
     * @param page
     * @returns {{}}
     */
    validatePreferredParam: function (page) {
        var params = {};
        if (_.isFunction(page.isValidCategory) && page.isValidCategory(preferred.category)) {
            params.category = preferred.category;
        }
        if (_.isFunction(page.isValidType) && page.isValidType(preferred.type)) {
            params.type = preferred.type;
        }
        return params;
    },
    /**
     * 加载菜单下的详细内容页面
     * @param action
     */
    loadPanelDetail: function (action) {
        var curCategory = _initCategory[action];
        var wrap = _actionClassMap[action]['action'];
        var obj = _actionClassMap[action]['obj'];
        var params = this.validatePreferredParam(obj);

        if (_.isFunction(obj.loadPanel)) {
            $('#ls_right').html(loading());
            obj.loadPanel(params, function (content) {
                setTimeout(function () {
                    $('#ls_right').html(content);
                    if (_.isFunction(obj.loadComplete)) {
                        obj.loadComplete();
                    }
                }, 150);
            });
        } else {
            utils.showLoading('#ls_right');
            async.parallel({
                nav: function (callback) {
                    if (_.isFunction(obj.loadNav)) {
                        obj.loadNav(callback, params);
                    } else {
                        var url = schedule.buildUrl('menu/' + wrap);
                        schedule.load('get', url, params, {
                            success: function (data) {
                                callback(null, data);
                            }
                        });
                    }
                },
                content: function (callback) {
                    // if (typeof curCategory !== 'undefined') {
                    //     category.setCurrent(curCategory);
                    // }
                    if (_.isFunction(obj.loadDetail)) {
                        obj.loadDetail(callback, params);
                    } else {
                        callback(null, {});
                    }
                }
            }, function (err, results) {
                utils.hideLoading('#ls_right', function () {
                    $('#ls_right').html(results.nav);
                    var detail = $('#detail_content');
                    if (detail.length > 0 && _.isFunction(obj.loadDetail)) {
                        detail.html(results.content);
                    }
                    if (_.isFunction(obj.loadComplete)) {
                        obj.loadComplete();
                    }
                });
            });
        }
    }
};
