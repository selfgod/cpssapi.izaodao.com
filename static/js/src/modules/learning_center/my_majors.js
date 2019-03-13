var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var switch_curricular = require('./switch_curricular');
require('lib/jquery-circle');
module.exports = {
    init: function () {
        var self = this;
        switch_curricular.bindCurricularType('.curricular_major_type', function () {
            self.myMajorList();
        });
        //删除课程
        $(_options.learnMianClass).on('click', '.myclass_major_delete', function () {
            var id = parseInt($(this).data('id'), 10);
            var delSchedule = require('public/del_schedule');
            delSchedule.show({id: id}, _.bind(self.myMajorList, self));
        });
    },
    requestFunc: function (type) {
        this.myCourseMajor(type);
    },
    myCourseMajor: function (type) {
        var self = this;
        var url = utils.buildURL('learn', 'my_course_major');
        utils.callWithLoading('get', url, {type: type}, {
            success: function (res) {
                if (res) {
                    $(_options.learnMianClass + ' .cpss_layout').html(res);
                    self.drawCircle();
                }
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' .cpss_layout');
    },
    myMajorList: function () {
        var self = this;
        var type = $(_options.learnMianClass + ' .my_curricular_type .current_v5').data('type');
        var url = utils.buildURL('learn', 'my_major_list');
        utils.callWithLoading('get', url, {type: type}, {
            success: function (res) {
                if (res) {
                    $(_options.learnMianClass + ' .my_major_list').html(res);
                    self.drawCircle();
                }
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' .my_major_list');
    },
    /**
     * 渲染个人阶段课程完成度圆圈
     */
    drawCircle: function () {
        $('.complete_circle').each(function () {
            var $el = $(this);
            var complete = $el.data('complete') + 0;
            var total = $el.data('total') + 0;
            var value = 0;
            if (total !== 0) {
                value = parseInt(complete * 100 / total, 10);
            }
            $el.circleChart({
                size: 50,
                value: value,
                color: '#3399ff',
                backgroundColor: '#eee',
                startAngle: 75,
                text: complete + '/' + total
            });
        });
    }
};
