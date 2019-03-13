var $ = require('jquery');
var oralAssessmentCalendar = require('./template/oral_assessment_calendar.tpl');
var target = '#oral_assessment_date';
module.exports = {
    init: function (dow) {
        target = dow;
        this.bindEvent();
    },
    bindEvent: function () {
        var self = this;
        $('body').on('click', '#last_month,#next_month', function () {
            self.show($(this).data('year'), $(this).data('month'));
        });
    },
    show: function (year, month) {
        var current_month_val = year;
        var lastMonth = this.getLastMonth(year, month);
        var nextMonth = this.getNextMonth(year, month);
        var current_month = this.getMonthZh(month);
        if (current_month) {
            current_month_val = current_month + ' ' + current_month_val;
        }
        var month_day = this.getMonthCalendar(year, month);
        var calendar = oralAssessmentCalendar({
            last_month: lastMonth.month,
            last_year: lastMonth.year,
            next_month: nextMonth.month,
            next_year: nextMonth.year,
            current_month_val: current_month_val,
            month_day: month_day
        });
        $(target).html(calendar).show();
    },
    getMonthZh: function (month) {
        var result = null;
        var monthInt = parseInt(month, 10);
        if (monthInt > 0 && monthInt <= 12) {
            var arr = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'];
            result = arr[monthInt - 1];
        }
        return result;
    },
    getLastMonth: function (year, month) {
        var lastMonth = parseInt(month, 10) - 1 > 0 ? parseInt(month, 10) - 1 : 12;
        var lastMonthYear = lastMonth == 12 ? parseInt(year, 10) - 1 : year;
        if (parseInt(lastMonth, 10) < 10) lastMonth = '0' + lastMonth;
        return {year: lastMonthYear, month: lastMonth};
    },
    getNextMonth: function (year, month) {
        var nextMonth = parseInt(month, 10) + 1 <= 12 ? parseInt(month, 10) + 1 : 1;
        var nextMonthYear = nextMonth == 1 ? parseInt(year, 10) + 1 : year;
        if (parseInt(nextMonth, 10) < 10) nextMonth = '0' + nextMonth;
        return {year: nextMonthYear, month: nextMonth};
    },
    getMonthCalendar: function (year, month) {
        var i = 0;
        var last = 0;
        var next = 0;
        var currentMonthWeek = parseInt(this.getYearMonthWeek(year, month), 10);
        var currentMonthDate = parseInt(this.getYearMonthDate(year, month), 10);
        var lastMonth = this.getLastMonth(year, month);
        var nextMonth = this.getNextMonth(year, month);
        var lastMonthDate = parseInt(this.getYearMonthDate(lastMonth.year, lastMonth.month), 10);
        var date = '';
        var html = '';
        var liClass;
        var year_month = '';
        for (var k = 1; k < 43; k++) {
            liClass = '';
            if (currentMonthWeek === 0) {
                last = 7;
            } else {
                last = currentMonthWeek;
            }
            if (k <= last) {
                liClass = 'color_aaa';
                i = lastMonthDate - last + k;
                year_month = lastMonth.year + '-' + lastMonth.month;
            } else if (k <= last + currentMonthDate) {
                i = k - last;
                year_month = year + '-' + month;
            } else {
                liClass = 'color_aaa';
                next++;
                i = next;
                year_month = nextMonth.year + '-' + nextMonth.month;
            }
            date = year_month + '-' + i;
            if (i < 10) date = year_month + '-0' + i;
            if (k % 7 == 0) liClass += ' mr00';
            html += '<li class="' + liClass + '" data-date="' + date + '">' + i + '</li>';
        }
        return html;
    },
    getYearMonthDate: function (year, month) {
        return new Date(year, month, 0).getDate();
    },
    getYearMonthWeek: function (year, month) {
        return new Date(year + '-' + month).getDay();
    }
};
