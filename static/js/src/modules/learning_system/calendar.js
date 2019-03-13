var $ = require('jquery');
var _initialized = false;

module.exports = {
    doms: {},
    callback: {},
    init: function (ymSwitch, daySelected) {
        this.getDoms();
        if (!_initialized) {
            _initialized = true;
            this.bindEvent();
            this.callback = {
                ymSwitch: ymSwitch,
                daySelected: daySelected
            };
        }
    },
    isInitialized: function () {
        return _initialized;
    },
    getDoms: function () {
        this.doms.currentYear = $('#current_year');
        this.doms.currentMonth = $('#current_month');
        this.doms.panel = $('#calendar_panel');
        this.doms.detail = $('#calendar_detail');
    },
    getYear: function () {
        return parseInt(this.doms.currentYear.text(), 10);
    },
    setYear: function (year) {
        this.doms.currentYear.text(parseInt(year, 10));
    },
    getMonth: function () {
        return parseInt(this.doms.currentMonth.text(), 10);
    },
    setMonth: function (month) {
        month = parseInt(month, 10);
        if (month < 10) {
            month = '0' + month;
        }
        this.doms.currentMonth.text(month);
    },
    loadData: function () {
        this.callback.ymSwitch(this.getYear(), this.getMonth());
    },
    daySelected: function (day) {
        var year = this.getYear();
        var month = this.getMonth();
        this.callback.daySelected(year, month, day);
    },
    /**
     * 切换年，月
     */
    switchYearMonth: function (event) {
        var self = event.data;
        event.stopPropagation();
        var id = $(this).attr('id'), year, month;
        switch (id) {
            case 'last_year':
                year = self.getYear();
                if (year - 1 > 1970) {
                    self.setYear(year - 1);
                    self.loadData();
                }
                break;
            case 'next_year':
                year = self.getYear();
                self.setYear(year + 1);
                self.loadData();
                break;
            case 'last_month':
                month = self.getMonth();
                if (month - 1 > 0) {
                    self.setMonth(month - 1);
                } else {
                    self.setMonth(12);
                    self.setYear(self.getYear() - 1);
                }
                self.loadData();
                break;
            case 'next_month':
                month = self.getMonth();
                if (month + 1 < 13) {
                    self.setMonth(month + 1);
                } else {
                    self.setMonth(1);
                    self.setYear(self.getYear() + 1);
                }
                self.loadData();
                break;
            default:
                break;
        }
    },
    /**
     * 绑定事件
     */
    bindEvent: function () {
        var self = this;
        var element = $('body');
        element.on('click', '#last_year,#next_year,#last_month,#next_month',
            self, this.switchYearMonth);

        element.on('click', '#calendar_detail li', function (event) {
            event.stopPropagation();
            var day = parseInt($(this).text().trim(), 10);
            if (isNaN(day)) {
                return;
            }
            self.daySelected(day);
        });
    }
};
