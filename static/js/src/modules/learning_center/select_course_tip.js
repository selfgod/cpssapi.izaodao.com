var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _dom = '.left_curricular';
module.exports = {
    init: function () {
        var self = this;
        $(_dom).on('click', '.select_course_tip_close', function () {
            self.selectCourseTipClose();
        });
    },
    requestFunc: function () {
        var select_course_tip = require('./template/select_course_tip.tpl');
        var url = utils.buildURL('learn', 'select_course_tip');
        utils.call('get', url, {}, {
            success: function (res) {
                if (res.code === 200) {
                    $(_dom).append(select_course_tip({
                        goods_names: res.data.goods_names
                    }));
                    $(_dom + ' .selectCourseClass').addClass('shade_main');
                }
            }
        }, {dataType: 'json'});
    },
    selectCourseTipClose: function () {
        $('#select_course_tip').remove();
        $(_dom + ' .selectCourseClass').removeClass('shade_main');
        var url = utils.buildURL('learn', 'select_course_tip_close');
        utils.call('get', url, {}, {
            success: function (res) {
            }
        }, {dataType: 'json'});
    }
};
