var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('../exam/unit_call');
var layerPop = require('public/layer');
var levelsHbs = require('./template/levels.hbs');
var grade = require('./grade');
require('./css/graded.css');

var applyHbs = require('public/template/apply_change_grade.hbs');
var baseUrl = util.buildURL('level_test', 'grade');
var ChoseBasics = $('#ChoseBasics');
var Apply = $('#indexContant');
var levelData = '';
var index = {
    init: function () {
        var self = this;
        //动画效果
        $('.grade_card').addClass('animated zoomIn');
        var angle = 0;
        setInterval(function () {
            angle += 3;
            $('.banner_rotation').rotate(angle);
        }, 300);
        //闪烁动画
        setInterval(function () {
            $('.banner_star i.srar-01').fadeOut(200).fadeIn(200);
        }, 2500);

        setInterval(function () {
            $('.banner_star i.srar-02').fadeOut(200).fadeIn(200);
        }, 2000);

        setInterval(function () {
            $('.banner_star i.srar-03').fadeOut(200).fadeIn(200);
        }, 1500);

        setInterval(function () {
            $('.banner_star i.srar-04').fadeOut(200).fadeIn(200);
        }, 3000);

        setInterval(function () {
            $('.banner_star i.srar-05').fadeOut(200).fadeIn(200);
        }, 1500);

        setInterval(function () {
            $('.banner_star i.srar-06').fadeOut(200).fadeIn(200);
        }, 2000);
        //等级列表请求
        self.levelInfo(function (cb) {
            levelData = cb.data;
            var levelsContent = levelsHbs({list: cb.data});
            $('#levels_content').html(levelsContent);
            // 等级说明
            $('.level_list li').on('mouseover', function () {
                $(this).find('.level_list_off').stop().animate({bottom: '0px'});
            });
            $('.level_list li').on('mouseout', function () {
                $(this).find('.level_list_off').stop().animate({bottom: '-300px'});
            });
        });
    },
    levelInfo: function (cb) {
        unitCall.callJson('post', baseUrl + '/level_info_ajax', {}, {
            success: function (data) {
                cb(data);
            }
        });
    }
};
//零基础
ChoseBasics.on('click', '.have_no_basic', function () {
    var titleFirst = '零基础无需参加测试，你将被直接定级为L1';
    var titleSub = '定级后，将从发音入门开始学习，如不合适，可调级';
    var params = '';
    layerPop.showTwoLine(titleFirst, titleSub,
        {
            btn: ['取消', '确定'],
            btn2: function () {
                grade.SubmitAction(params, baseUrl + '/setUserZeroBased', function () {
                    window.location.reload();
                });
            }
        });
});
Apply.on('click', '.applyChangeGrade', function () {
    var $body = $('body');
    var applyContent = applyHbs({list: levelData});
    $body.on('click', '.applySubmit', function () {
        var reason = $('#reason').val();
        var grade_id = $('#grade_id').val();
        if (reason.length < 20) {
            $('.error_msg').html('请输入不少于20字的调级原因');
            return false;
        } else {
            $('.error_msg').html('');
        }
        if (grade_id < 1) {
            $('.error_msg_level').html('请选择期望级别');
            return false;
        } else {
            $('.error_msg_level').html('');
        }
        unitCall.callJson('post', baseUrl + '/applyChangeGrade', {grade_id: grade_id, reason: reason}, {
            success: function (data) {
                if (data.code == 200) {
                    window.location.reload();
                } else {
                    $('.error_msg').html(data.data);
                }
            }
        });
    });
    layerPop.show(applyContent, {
        area: ['450px', '330px']
    });
});
index.init();
