var $ = require('jquery');
var _ = require('lodash');
var schedule = require('./schedule');
var imgBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxMTRCNDQxN0ZEMzYxMUU1OTFBRUUzMEJDOEEwQkVDMiIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxMTRCNDQxOEZEMzYxMUU1OTFBRUUzMEJDOEEwQkVDMiI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjExNEI0NDE1RkQzNjExRTU5MUFFRTMwQkM4QTBCRUMyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjExNEI0NDE2RkQzNjExRTU5MUFFRTMwQkM4QTBCRUMyIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+0JaOWwAAAF5JREFUeNpi+M/AwADFvkC8D4g/QzGI7QOThylqAeL/OHALTKEPHkUw7MsAtYKQwn2MQOIT0GBeBvzgMxMDcYARpPAMEQpPw4KFKM8QHTww7AcNgS9QvA9qG1geIMAAGg563mXSKc8AAAAASUVORK5CYII=';
var redPointTemp = '<i class="has_v422"><img src="' + imgBase64 + '" alt=""></i>';
var subPointTemp = '<b class="has_v422 mt04"><img src="' + imgBase64 + '" alt=""></b>';

/**
 * 只有直播阶段课程才显示小红点，录播阶段课程不显示
 */
module.exports = {
    record_show: null,
    practice_show: null,
    unit_show: null,
    taskList: [],
    /**
     * 是否已经加载完成
     */
    loaded: false,
    /**
     * 加载小红点数据
     */
    loadData: function () {
        if (schedule.mode === 2) {
            return;
        }
        var self = this;
        var url = schedule.buildUrl('notice');
        self.loaded = false;
        schedule.load('get', url, {}, {
            success: function (data) {
                self.record_show = data.record;
                self.practice_show = data.practice;
                self.unit_show = data.unit;
                self.loaded = true;
                _.forEach(self.taskList, function (task) {
                    var param = null;
                    if (task.length > 1) {
                        param = task[1];
                    }
                    task[0].apply(self, [param]);
                });
                self.clearTaskList();
            }
        }, {dataType: 'json'});
    },
    clearTaskList: function () {
        this.taskList = [];
    },
    showNavNotice: function () {
        if (schedule.mode === 2) {
            return;
        }
        if (!this.loaded) {
            this.taskList.push([this.showNavNotice]);
            return;
        }
        if (this.record_show) {
            var bookNav = $('.menu_v422 span[class^="book"]');
            if (bookNav.length > 0) {
                bookNav.parent().append(redPointTemp);
            }
        }
        if (this.practice_show || this.unit_show) {
            var exerciseNav = $('.menu_v422 span[class^="tadk"]');
            if (exerciseNav.length > 0) {
                exerciseNav.parent().append(redPointTemp);
            }
        }
    },
    showCategoryLearn: function () {
        if (schedule.mode === 2) {
            return;
        }
        if (!this.loaded) {
            this.taskList.push([this.showCategoryLearn]);
            return;
        }
        var category;
        if (this.record_show) {
            category = $('.title_v422 ul li[data-name="record"]');
            if (category.length > 0) {
                category.append(redPointTemp);
            }
        }
    },
    showCategoryExercise: function () {
        if (schedule.mode === 2) {
            return;
        }
        if (!this.loaded) {
            this.taskList.push([this.showCategoryExercise]);
            return;
        }
        var category;
        if (this.practice_show) {
            category = $('.title_v422 ul li[data-name="test"]');
            if (category.length > 0) {
                category.append(redPointTemp);
            }
        }
        if (this.unit_show) {
            category = $('.title_v422 ul li[data-name="unit"]');
            if (category.length > 0) {
                category.append(redPointTemp);
            }
        }
    },
    showTypeNotice: function (category) {
        if (schedule.mode === 2) {
            return;
        }
        this.removeTypeNotice();
        if (!this.loaded) {
            this.taskList.push([this.showTypeNotice, category]);
            return;
        }
        if ((this.record_show && category === 'record') ||
            (this.practice_show && category === 'test') ||
            (this.unit_show && category === 'unit')) {
            var typeElement = $('.subNav_v422 ul li[data-name="unfinished"]');
            if (typeElement.length > 0) {
                typeElement.append(subPointTemp);
            }
        }
    },
    removeTypeNotice: function () {
        if (schedule.mode === 2) {
            return;
        }
        $('.subNav_v422 ul li[data-name="unfinished"]>b').remove();
    },
    removePoint: function (type) {
        if (type === 'nav') {
            $('#schedule_body .has_v422').remove();
        }
        if (schedule.mode === 2) {
            return;
        }
        if (type === 'unit' && this.unit_show) {
            $('.subNav_v422 ul li[data-name="unfinished"]>b').remove();
            $('.title_v422 ul li[data-name="unit"]>i').remove();
            if (!this.practice_show) {
                $('.menu_v422 span[class^="tadk"]').parent().children('i').remove();
            }
            this.unit_show = 0;
            this.updateData(type);
        }
        if (type === 'test' && this.practice_show) {
            $('.subNav_v422 ul li[data-name="unfinished"]>b').remove();
            $('.title_v422 ul li[data-name="test"]>i').remove();
            if (!this.unit_show) {
                $('.menu_v422 span[class^="tadk"]').parent().children('i').remove();
            }
            this.practice_show = 0;
            this.updateData(type);
        }
        if (type === 'record' && this.record_show) {
            $('.subNav_v422 ul li[data-name="unfinished"]>b').remove();
            $('.title_v422 ul li[data-name="record"]>i').remove();
            $('.menu_v422 span[class^="book"]').parent().children('i').remove();
            this.record_show = 0;
            this.updateData(type);
        }
    },
    /**
     * 更新红点记录
     * @param type
     */
    updateData: function (type) {
        var url = schedule.buildUrl('notice/update');
        var params = {
            type: type,
            scheduleId: schedule.id
        };
        schedule.load('post', url, params);
    }
};
