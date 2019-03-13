/**
 * 上课任务页面
 */
var $ = require('jquery');
var category = require('./category');
var subCategory = require('./sub_category');
var utils = require('../../public/util');
var async = require('async');
var pagination = require('../../public/pagination');
var popup = require('../../public/popup');
var schedule = require('./schedule');
var checkIn = require('../../public/check_in');
var calendar = require('./calendar');
var notice = require('./notice');
var user = require('public/user');

var _selectedDate;
var _highLight = 'haveclass_current';
var _name = 'learn_right_panel';
var _subCategoryMap = {
    all: 0,
    finished: 1,
    unfinished: 2
};

var _loadLiveList = function (date, callback, loading) {
    var params = {
        date: date
    };
    schedule.loadDetail('schedule/detail/live', params, callback, loading);
};

var _loadLivePanel = function (date) {
    var params = {
        date: date
    };
    utils.showLoading('#lesson_panel');
    async.parallel({
        weekday: function (callback) {
            var url = schedule.buildUrl('schedule/rightNav/live');
            schedule.load('get', url, params, {
                success: function (data) {
                    callback(null, data);
                }
            });
        },
        detailList: function (callback) {
            _loadLiveList(date, callback);
        }
    }, function (error, results) {
        utils.hideLoading('#lesson_panel', function () {
            $('#lesson_panel').html(results.weekday);
            $('#detail_content').html(results.detailList);
        });
    });
};

/**
 * 加载录播回顾列表
 * @param params
 * @param callback
 * @param loading
 * @private
 */
var _loadRecordDetail = function (params, callback, loading) {
    schedule.loadDetail('schedule/detail/record', params, callback, loading);
};

var _loadRecordPanel = function () {
    utils.showLoading('#lesson_panel');
    async.parallel({
        category: function (callback) {
            var url = schedule.buildUrl('schedule/rightNav/record');
            schedule.load('get', url, {}, {
                success: function (data) {
                    callback(null, data);
                }
            });
        },
        detailList: function (callback) {
            subCategory.setCurrent(_name, 'all');
            var params = {
                type: 0,
                page: 1
            };
            _loadRecordDetail(params, callback);
        }
    }, function (error, results) {
        utils.hideLoading('#lesson_panel', function () {
            $('#lesson_panel').html(results.category);
            $('#detail_content').html(results.detailList);
            notice.showTypeNotice('record');
        });
    });
};

//获取当前选择的日期
var _getSelectedDate = function () {
    // if (!_selectedDate) {
    var element = $('#lesson_panel .haveclass_current');
    if (element.length > 0) {
        _selectedDate = element.data('date').split('-');
        _selectedDate = {
            year: _selectedDate[0],
            month: _selectedDate[1],
            day: _selectedDate[2]
        };
    } else {
        _selectedDate = null;
    }
    // }
    return _selectedDate;
};

var _updateSelectedDate = function (date) {
    _selectedDate = date.split('-');
    _selectedDate = {
        year: _selectedDate[0],
        month: _selectedDate[1],
        day: _selectedDate[2]
    };
};

module.exports = {
    init: function () {
        var self = this;
        category.onClick(_name, function (element) {
            var current = element.data('name');
            category.setCurrent(_name, current);
            if (current === 'live') {
                var date = _getSelectedDate();
                if (date) {
                    _loadLivePanel(date.year + '-' + date.month + '-' + date.day);
                } else {
                    _loadLivePanel();
                }
            } else if (current === 'record') {
                _loadRecordPanel();
            }
        });

        subCategory.onClick(_name, function (element) {
            var type = _subCategoryMap[element.data('name')];
            // subCategory.setCurrent(_name, element.data('name'));
            var params = {
                type: type,
                page: 1
            };
            _loadRecordDetail(params, null, true);
            if (element.data('name') === 'unfinished') {
                notice.removePoint(category.getCurrent(_name));
            }
        });

        //右侧日历图表点击
        $(utils.lsParentId()).on('click', '#calendar_v422', function (event) {
            calendar.init(self.loadCalendar, self.daySelected);
            // var calendar = $('#calendar_panel');
            var calButton = $('#calendar_v422');
            if (!calendar.doms.panel.is(':visible')) {
                calButton.find('img').attr('src', $('#calendar_show').val());
                calButton.addClass('rili_ov');
                // _loadCalendar();
                calendar.loadData();
                calendar.doms.panel.show();
            } else {
                calButton.find('img').attr('src', $('#calendar_hide').val());
                calButton.removeClass('rili_ov');
                calendar.doms.panel.hide();
            }
        });

        //空白处点击隐藏日历
        $(document).mouseup(function (e) {
            // var calendar = $('#calendar_panel');
            var calButton = $('#calendar_v422');
            if (calendar.isInitialized()) {
                if ((!calendar.doms.panel.is(e.target) && calendar.doms.panel.has(e.target).length === 0) &&
                    (!calButton.is(e.target) && calButton.has(e.target).length === 0)) {
                    if (calendar.doms.panel.is(':visible')) {
                        calButton.find('img').attr('src', $('#calendar_hide').val());
                        calButton.removeClass('rili_ov');
                        calendar.doms.panel.hide();
                    }
                }
            }
        });

        //直播课表点击某一天
        $(utils.lsParentId()).on('click', '.haveclass_v422 .part_v422', function () {
            var element = $(this);
            if (!element.hasClass(_highLight) && !element.attr('id')) {
                element.addClass(_highLight).siblings().removeClass(_highLight);
                var date = element.data('date').split('-');
                calendar.init(self.loadCalendar, self.daySelected);
                calendar.setYear(date[0]);
                calendar.setMonth(date[1]);
                _updateSelectedDate(element.data('date'));
                _loadLiveList(element.data('date'), null, true);
            }
        });

        //分页点击
        pagination.bind(_name, function (page) {
            var type = _subCategoryMap[subCategory.getCurrent(_name)];
            var params = {
                type: type,
                page: page
            };
            _loadRecordDetail(params, null, true);
        });

        //点击教室信息
        $(utils.lsParentId()).on('click', '.room', function (event) {
            event.stopPropagation();
            var startTime = $(this).closest('ul').data('starttime');
            if (!startTime) {
                //从下次上课提醒中点击教室信息
                startTime = $(this).parent().data('timestamp');
            }
            var date = utils.formatTimestamp(parseInt(startTime, 10) * 1000);
            popup.showRoomInfo(schedule.roomName, schedule.roomPwd, date);
        });

        //点击教室信息 zdtalk
        $(utils.lsParentId()).on('click', '.zdtalk', function () {
            var element = $(this);
            var download = element.attr('data-download');
            var url = element.attr('data-zdtalk');
            popup.downloadZdTalk(url, element, download);
        });

        //回顾按钮点击
        $(utils.lsParentId()).on('click', '.review', function () {
            var element = $(this);
            var reviewUrl;
            var lessonId = element.closest('ul').data('id');
            var params = {
                plan: schedule.planId,
                stage: schedule.planStageId,
                schedule: schedule.id
            };
            reviewUrl = schedule.buildUrl('review/' + lessonId) + '?' + $.param(params);
            window.open(reviewUrl);
        });
        //下载按钮
        $(utils.lsParentId()).on('click', '.download', function () {
            var element = $(this);
            var lessonId = element.closest('ul').data('id');
            var url = schedule.buildUrl('download/' + lessonId);
            schedule.load('get', url, {}, {
                success: function (data) {
                    if (data.code === 200) {
                        location.href = data.data;
                    }
                }
            }, {dataType: 'json'});
        });

        $(utils.lsParentId()).on('click', '.none_record', function () {
            popup.withoutCourseware();
        });

        /**
         * 报到按钮点击
         */
        $(utils.lsParentId()).on('click', '.checkin', function (event) {
            event.stopPropagation();
            var element = $(this);
            if (element.hasClass('button_Hui')) {
                return;
            }
            var parent = element.closest('ul');
            var lessonId = parent.data('id');
            var endTime = parseInt(parent.data('endtime'), 10);
            var now = Math.floor((new Date()).valueOf() / 1000);
            if (endTime - 60 * 30 > now) {
                //在下课前半小时之前
                popup.showCountDownPopup(endTime, now);
            } else {
                user.isOverLimit(function () {
                    //报到
                    checkIn.show({
                        isMajor: true,
                        planId: schedule.planId,
                        planStageId: schedule.planStageId,
                        scheduleId: schedule.id,
                        lessonId: lessonId
                    }, function (data) {
                        var date = new Date();
                        var today = date.getFullYear() + '-' + utils.getMonth(date) + '-' + utils.getDay(date);
                        _loadLiveList(today, null, true);
                    });
                });
            }
        });
    },
    loadCalendar: function (year, month) {
        var url = schedule.buildUrl('schedule/calendar');
        var selected = _getSelectedDate();
        month = month < 10 ? '0' + month : month;
        var params = {
            year_month: year + '-' + month,
            selected: selected.year + '-' + selected.month + '|' + selected.day
        };
        schedule.load('get', url, params, {
            success: function (data) {
                calendar.doms.detail.html(data);
            }

        });
    },
    daySelected: function (year, month, day) {
        day = day < 10 ? '0' + day : day;
        month = month < 10 ? '0' + month : month;
        var date = year + '-' + month + '-' + day;
        _updateSelectedDate(date);
        _loadLivePanel(date);
    },
    isValidCategory: function (cate) {
        return (cate && cate.length > 0 &&
        $.inArray(cate, ['live', 'record']) !== -1);
    },
    isValidType: function (type) {
        return (type && type.length > 0 &&
        $.inArray(type, ['0', '1', '2']) !== -1);
    },
    loadNav: function (callback, params) {
        var element = $('#pre_date');
        if (element.length > 0) {
            var preDate = element.val();
            if (preDate.length > 0) {
                params.date = preDate;
            }
            var url = schedule.buildUrl('menu/learn');
            schedule.load('get', url, params, {
                success: function (data) {
                    callback(null, data);
                }
            });
        }
    },
    loadDetail: function (callback, params) {
        var preDate = $('#pre_date').val();
        var today;
        var topCategory = params.category || 'record';
        if (preDate.length > 0) {
            today = preDate;
        } else {
            var date = new Date();
            today = date.getFullYear() + '-' + utils.getMonth(date) + '-' + utils.getDay(date);
        }

        var classMode = schedule.mode;
        if (classMode === 1 && topCategory === 'live') {
            _loadLiveList(today, callback);
        } else {
            category.setCurrent(_name, 'record');
            _loadRecordDetail(params, callback);
        }
    },
    loadComplete: function () {
        if (schedule.mode === 1) {
            _getSelectedDate();
        }
        notice.showCategoryLearn();
        notice.showTypeNotice('record');
    }
};
