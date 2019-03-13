var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var calendar = require('../learning_system/calendar');
var day_switch_lesson = require('./day_switch_lesson');
var _selectedDate;
//获取当前选择的日期
var _getSelectedDate = function () {
    var element = $('.calendar_week .current_v5');
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
        this.bindDom();
        day_switch_lesson.init();
    },
    bindDom: function () {
        var self = this;
        $(_options.learnMianClass).on('click', '.calendar_week .day_switch_date', function () {
            if (!$(this).hasClass('current_v5')) {
                $(this).addClass('current_v5 h39').siblings('.day_switch_date').removeClass('current_v5 h39');
                var date = $(this).attr('data-date') || null;
                day_switch_lesson.liveScheduleDay(date);
                _updateSelectedDate(date);
            }
        });
        $(_options.learnMianClass).on('click', '#calendar', function (event) {
            calendar.init(self.loadCalendar, self.daySelected);
            var calButton = $('#calendar');
            if (!calendar.doms.panel.is(':visible')) {
                calButton.find('img').attr('src', $('#calendar_show').val());
                calButton.addClass('bg_3399ff');
                calendar.loadData();
                calendar.doms.panel.show();
            } else {
                calButton.find('img').attr('src', $('#calendar_hide').val());
                calButton.removeClass('bg_3399ff');
                calendar.doms.panel.hide();
            }
        });
        //空白处点击隐藏日历
        $(document).mouseup(function (e) {
            var calButton = $('#calendar');
            if (calendar.isInitialized()) {
                if ((!calendar.doms.panel.is(e.target) && calendar.doms.panel.has(e.target).length === 0) &&
                    (!calButton.is(e.target) && calButton.has(e.target).length === 0)) {
                    if (calendar.doms.panel.is(':visible')) {
                        calButton.find('img').attr('src', $('#calendar_hide').val());
                        calButton.removeClass('bg_3399ff');
                        calendar.doms.panel.hide();
                    }
                }
            }
        });
        $(_options.learnMianClass).on('click', '.today_after_live', function () {
            var date = $(this).data('date');
            day_switch_lesson.liveScheduleMain(date);
        });
    },
    requestFunc: function () {
        day_switch_lesson.liveScheduleMain();
    },
    loadCalendar: function (year, month) {
        var url = utils.buildURL('learn', 'monthly_calendar');
        var selected = _getSelectedDate();
        month = month < 10 ? '0' + month : month;
        var params = {
            year_month: year + '-' + month,
            selected: selected.year + '-' + selected.month + '|' + selected.day
        };
        utils.call('get', url, params, {
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
        day_switch_lesson.liveScheduleMain(date);
    }
};
