var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var laydate = require('laydate');
var endDate = null;
module.exports = {
    holidayClass: '.holiday',
    init: function () {
        this.bingTagInit();
    },
    bingTagInit: function () {
        var self = this;
        var start, end, currentTime, endMin, endMax;
        var startObj = '#choose_start_time';
        var endObj = '#choose_end_time';
        var lastDate = $(startObj).data('last') || '';
        var maxAfter = parseInt($(endObj).data('max'), 10);
        var minAfter = parseInt($(endObj).data('min'), 10);
        var format = 'YYYY-MM-DD';
        $(startObj).focus(function () {
            start = {
                elem: startObj,
                min: utils.formatDate(new Date(), format),
                show: true,
                showBottom: false,
                done: function (datas) {
                    if (datas) {
                        currentTime = datas;
                        if (minAfter > 0) {
                            endMin = self.addNDays(currentTime, minAfter, format);
                        } else {
                            endMin = currentTime;
                        }
                        endMax = self.addNDays(currentTime, maxAfter - 1, format);
                        $(endObj).val('').removeAttr('disabled');
                        self.changeEndDate(endMin, endMax);
                    }
                }
            };
            if (lastDate) start.max = lastDate;
            laydate.render(start);
        });
        $(endObj).focus(function () {
            if (!start || !currentTime || !endMin || !endMax) return false;
            end = {elem: endObj};
            if (endDate === null) {
                end.min = endMin;
                end.max = endMax;
                end.show = true;
                end.showBottom = false;
                endDate = laydate.render(end);
            } else {
                laydate.render(end);
            }
        });
    },
    changeEndDate: function (endMin, endMax) {
        console.log(endMin);
        console.log(endMax);
        if (endDate !== null) {
            var minDate = new Date(Date.parse(endMin.replace(/-/g, '/')));
            var maxDate = new Date(Date.parse(endMax.replace(/-/g, '/')));
            endDate.config.min = {
                year: minDate.getFullYear(),
                month: minDate.getMonth(),
                date: minDate.getDate()
            };
            endDate.config.max = {
                year: maxDate.getFullYear(),
                month: maxDate.getMonth(),
                date: maxDate.getDate()
            };
        }
    },
    addNDays: function (date, n, format) {
        var d = new Date(Date.parse(date.replace(/-/g, '/')));
        var time = d.getTime();
        var newTime = time + n * 24 * 60 * 60 * 1000;
        return utils.formatDate(new Date(newTime), format);
    }
};
