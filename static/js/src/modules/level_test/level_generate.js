var $ = require('jquery');
var _ = require('lodash');
require('./css/style.css');
var utils = require('public/util.js');
var unitCall = require('../exam/unit_call');
var layerPop = require('public/layer');
var mainHbs = require('./template/level_generate_main.hbs');
var experienceHbs = require('./template/level_generate_experience.hbs');
var selectHbs = require('./template/level_generate_select.hbs');
var userScheduleHbs = require('./template/level_generate_user_schedule.hbs');
var noticeHbs = require('./template/notice.hbs');
var publicJs = require('./public');

var generate = {
    init: function () {
        var self = this;
        self._bindDomEvent();
        self.loadLearningExperience();
        //self.loadProgressOption();
        self.loadUserScheduleInfo();
    },
    _bindDomEvent: function () { //元素绑定
        var self = this;
        $('.contant').html(mainHbs());//页面填充基础元素
        $('#main').on('change', '.book_action', function () {
            self.loadProgressOption();
        });
        $('#main').on('click', '#create_test', function () {
            self.checkSubmit();
        });
        $('#main').on('click', '.generateExam', function () {
            var $el = $(this);
            var grade_id = $el.data('grade');
            publicJs.generateExam(grade_id);
        });
    },
    loadLearningExperience: function () {  //页面学习经历
        utils.showLoading('.education');
        var url = utils.buildURL('level_test', 'Grade/learningExperience');
        unitCall.callJson('post', url, null, {
            success: function (ret) {
                if (ret.code == 200) {
                    $('.education').html(experienceHbs({book_info: ret.data.book_info}));
                    $('#select_box').html(selectHbs());
                }
            }
        }, {async: true});
    },
    loadProgressOption: function () { //加载进度下拉选项
        var book_id = parseInt($('select[name = book_id]').val(), 10);
        if (book_id === 0) {
            $('#select_box').html(selectHbs());
            $('#select_book').addClass('flL');
            return true;
        }
        var data = {book_id: book_id};
        var url = utils.buildURL('level_test', 'Grade/loadBookProgress');
        unitCall.callJson('post', url, data, {
            success: function (ret) {
                if (ret.code == 200) {
                    if (ret.data.no_progress) {
                        $('#select_book').addClass('flL');
                        $('#select_box').html(selectHbs({progress_info: ret.data.progress_info}));
                    } else {
                        $('#select_box').html('');
                        $('#select_book').removeClass('flL');
                    }
                    $('#no_progress_tip').val(ret.data.no_progress);
                }
            }
        }, {async: true});
    },
    loadUserScheduleInfo: function () { //加载用户阶段课程信息
        var url = utils.buildURL('level_test', 'Grade/getUserScheduleInfo');
        unitCall.callJson('post', url, null, {
            success: function (ret) {
                if (ret.code === 200 && !_.isEmpty(ret.data.user_schedule_info)) {
                    $('.user_info').html(userScheduleHbs({user_schedule_info: ret.data.user_schedule_info}));
                }
            }
        }, {async: true});
    },
    checkSubmit: function () {
        var book_id = parseInt($('select[name = book_id]').val(), 10);
        var progress_id = parseInt($('select[name = progress_id]').val(), 10);
        var no_progress_tip = $('#no_progress_tip').val();
        if (book_id == '0') {
            $('#text_error').html('请选择学过的教材');
        } else if (progress_id == '0' && (no_progress_tip == 1)) {
            $('#text_error').html('请选择课程进度');
        } else {
            $('#text_error').html('');
            var url = utils.buildURL('level_test', 'Grade/checkGrade');
            unitCall.callJson('post', url, {book_id: book_id, progress_id: progress_id}, {
                success: function (ret) {
                    if (ret.code == 200) {
                        ret.data.is_first_grade = 1;//is_first_grade: 1 //1为定级 2为升级（在等级页加这个参数）
                        var noticeContent = noticeHbs({
                            list: ret.data
                        });
                        $('#contant').html(noticeContent); //todo
                    } else {
                        $('#text_error').html(ret.msg);
                    }
                }
            }, {async: true});
        }
    }
};
generate.init();
