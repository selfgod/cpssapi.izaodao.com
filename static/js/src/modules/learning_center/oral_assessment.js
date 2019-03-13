var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var popup = require('public/popup');
var _options = require('./options');
var assessment_date = require('./assessment_date');
var dialog;
var verify = true;
module.exports = {
    lesson_id: 0,
    isApply: false,
    canApply: false,
    init: function () {
        this.bindDom();
        assessment_date.init('#oral_assessment_date');
    },
    bindDom: function () {
        var self = this;
        var event = 'body';
        $(_options.learnMianClass).on('click', '.apply_oral_assessment', function () {
            self.applyOralAssessment();
        });
        $(event).on('click', '.dialog_close', function () {
            dialog.close();
        });
        $(event).on('click', '.oral_assessment_detial_close', function () {
            $('.oral_assessment_detial').html('');
        });
        $(event).on('click', '#hope_time', function () {
            self.oralAssessmentDate();
        });
        $(event).on('click', '.myclassv222_date_day li', function () {
            self.oralAssessmentTeacher($(this).data('date'));
        });
        $(event).on('click', '#last_date,#next_date', function () {
            self.oralAssessmentTeacher($(this).data('date'));
        });
        $(event).on('click', '.apply_data_teacher', function () {
            $('#hope_time').val($(this).data('time'));
            $('#teacher_uid').val($(this).data('teacher'));
            $('.oral_assessment_detial').html('');
        });
        $(event).on('click', '.submit_oral_assessment', function () {
            self.verifySubmit();
        });
    },
    requestFunc: function (lesson) {
        var self = this;
        self.lesson_id = lesson;
        self.oralAssessmentMain(lesson);
    },
    oralAssessmentMain: function (lesson_id) {
        var self = this;
        self.isApply = false;
        self.canApply = false;
        var url = utils.buildURL('learn', 'oral_assessment');
        utils.callWithLoading('post', url, {lesson_id: lesson_id}, {
            success: function (obj) {
                if (obj.code === 200) {
                    $(_options.learnMianClass + ' .cpss_layout').html(obj.data.html);
                    if (parseInt(obj.data.is_apply, 10) === 1) self.isApply = true;
                    if (parseInt(obj.data.can_apply, 10) === 1) self.canApply = true;
                }
            }
        }, {dataType: 'json'}, _options.learnMianClass + ' .cpss_layout');
    },
    applyOralAssessment: function () {
        var self = this;
        var oralAssessmentApply = require('./template/oral_assessment_apply.tpl');
        var oralAssessmentWait = require('./template/oral_assessment_wait.tpl');
        var oralAssessmentFail = require('./template/oral_assessment_fail.tpl');
        if (self.isApply === true) {
            dialog = popup.show(oralAssessmentWait());
        } else if (self.canApply === true) {
            dialog = popup.show(oralAssessmentApply({
                master_domain: $('#master_domain').val(),
                title: $('#schedule_name_val').text()
            }));
        } else {
            dialog = popup.show(oralAssessmentFail({
                master_domain: $('#master_domain').val()
            }));
        }
    },
    oralAssessmentDate: function () {
        var self = this;
        if (self.canApply !== true) return false;
        var oralAssessmentDetial = require('./template/oral_assessment_detial.tpl');
        $('.oral_assessment_detial').html(oralAssessmentDetial({
            image: popup.imgUrl('myclassv222_close.png')
        }));
        var date = new Date();
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        if (month < 10) month = '0' + month;
        assessment_date.show(year, month);
    },
    oralAssessmentTeacher: function (date) {
        var self = this;
        if (self.canApply !== true) return false;
        var event = '#oral_assessment_teacher';
        var url = utils.buildURL('learn', 'oral_assessment_date_teacher');
        utils.callWithLoading('get', url, {date: date}, {
            success: function (res) {
                $('#oral_assessment_date').hide();
                $(event).html(res).show();
            }
        }, {dataType: 'html'}, event);
    },
    verifySubmit: function () {
        var self = this;
        if (verify !== true) return false;
        if (self.canApply !== true) return false;
        var event = 'body .oral_assessment_apply';
        var patrnStr = /^[^@\/\'\\\"#!$%&()\^\*0-9]+$/;
        var patrnTime = /^(\d{4})-(0\d{1}|1[0-2])-(0\d{1}|[12]\d{1}|3[01])\s(0\d{1}|1\d{1}|2[0-3]):[0-5]\d{1}-(0\d{1}|1\d{1}|2[0-3]):[0-5]\d{1}$/;
        var patrnPhone = /(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/;
        var reg_name = $('#reg_name').val();
        var mobile = $('#mobile').val();
        var teacher_uid = $('#teacher_uid').val();
        var hope_time = $('#hope_time').val();
        $(event + ' .reg_name').html('');
        if (!patrnStr.exec(reg_name)) {
            $(event + ' .reg_name').html('姓名格式不正确！');
            return false;
        }
        $(event + ' .mobile').html('');
        if (!patrnPhone.exec(mobile)) {
            $(event + ' .mobile').html('电话格式不正确！');
            return false;
        }
        $(event + ' .hope_time').html('');
        if (!parseInt(teacher_uid, 10) || !patrnTime.exec(hope_time)) {
            $(event + ' .hope_time').html('请选择口语测评时间！');
            return false;
        }
        verify = false;
        var params = {
            lesson_id: self.lesson_id,
            reg_name: reg_name,
            mobile: mobile,
            teacher_uid: teacher_uid,
            hope_time: hope_time
        };
        var url = utils.buildURL('learn', 'oral_assessment_submit');
        utils.call('post', url, params, {
            success: function (obj) {
                if (obj.code === 200) {
                    location.reload();
                } else if (obj.code === 247) {
                    $(event + ' .hope_time').html('本次测评已申请！');
                } else if (obj.code === 249) {
                    $(event + ' .hope_time').html('该时段已被预约！');
                } else if (obj.code === 281) {
                    $(event + ' .hope_time').html('请选择正确时间！');
                } else {
                    $(event + ' .hope_time').html('申请口语测评失败！');
                }
            }
        }, {dataType: 'json'});
    }
};
