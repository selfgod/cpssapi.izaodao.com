var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var commonPrompt = require('public/template/common_prompt.hbs');
var layerPop = require('public/layer');
module.exports = {
    init: function () {
        var self = this;
        self._bindDomEvent();
        self._chooseClassGuide();
        self._requestFunc();
    },
    _bindDomEvent: function () { //元素绑定
        var self = this;
        $('.newbie_help').on('click', '.newbie_help_close', function () { //关闭窗口
            self.newGuideTipClose();
        });
        $('.newbie_help').on('click', '.newbie_help_btn', function () { //查看
            var step = $(this).data('step');
            self.viewGuideTip(step);
        });
        $('.nav_left_v5').on('click', '.shade_btn', function () { //查看
            var step = $(this).data('step');
            self.viewGuideTip(step);
        });
        $('.nav_left_v5').on('click', '.skip', function () { //跳过
            self.viewGuideSkip();
        });
        $('.nav_left_v5').on('click', '.close_box, .selectCourseClass', function () { //跳过强制引导
            self._chooseClassGuideClose();
        });
    },
    _chooseClassGuide: function () {
        var url = utils.buildURL('learn', 'have_formal_class');
        utils.callJson('get', url, {}, {
            success: function (res) {
                if (res.code === 500) { //强制新手引导
                    $('.shade_v5').show();
                    $('.mouse_icon').show();
                    $('.select_class').show();
                    $('.shade_step4_bg').show();
                    $('.shade_step4_bg').addClass('h80');
                    $('.selectCourseClass ').addClass('shade_main');
                }
            }
        });
    },
    _chooseClassGuideClose: function () {
        $('.shade_v5').hide();
        $('.mouse_icon').hide();
        $('.select_class').hide();
        $('.shade_step4_bg').hide();
        $('.selectCourseClass ').removeClass('shade_main');
    },
    _requestFunc: function () {
        var url = utils.buildURL('learn', 'new_guide_tip');
        utils.callJson('get', url, {}, {
            success: function (res) {
                if (res.code === 200) { //没观看过新手引导
                    $('.newbie_help').show();
                }
            }
        });
    },
    newGuideTipClose: function () {
        $('.newbie_help').remove();
        var url = utils.buildURL('learn', 'new_guide_tip_close');
        utils.callJson('get', url, {}, {
            success: function (res) {
            }
        });
    },
    viewGuideTip: function (step) {
        var self = this;
        self.viewGuideSkip();
        $('.shade_v5').show();
        $('.shade_step4_bg').show();
        $('.shade_step4_bg').addClass('h80');
        switch (step) {
            case 'step1':
                $('.select_class_top').show();
                $('.selectCourseClass').addClass('shade_main');
                break;
            case 'step2':
                $('.top175').show();
                $('.myCourseClass').addClass('shade_main');
                break;
            case 'step3':
                $('.top95').show();
                $('.learnHomeClass').addClass('shade_main');
                break;
            case 'step4':
                $('.shade_step4_bg').removeClass('h80');
                $('.shade_text_top').show();
                $('.nav_v5').addClass('shade_step4');
                $('.shade_step4_zIndex').show();
                $('.shade_step4_zIndex p').html($('#user_name').html() + '<i></i>');
                // $('.user_v5').addClass('shade_step4_zIndex');
                break;
            default:
                self.unActivatePopup();
                $('.shade_v5').hide();
                $('.shade_step4_bg').hide();
                break;
        }
    },
    viewGuideSkip: function () {
        $('.nav_v5').removeClass('shade_step4');
        $('.shade_step4_zIndex').hide();
        // $('.user_v5').removeClass('.shade_step4_zIndex');
        $('.shade_text_top').hide();
        $('.shade_step4_bg').hide();
        $('.shade_text').hide();
        $('.learn_nav ').removeClass('shade_main');
        $('.shade_v5').hide();
    },
    unActivatePopup: function () {
        layerPop.show(commonPrompt({
            title: '了解更多如何上课详情？',
            sub_title: '如果还不了解，请咨询页面右侧小道班主任~'
        }), {
            btn: ['不需要', '去了解'],
            btn2: function (index) {
                layerPop.layer.close(index);
                window.open(JP_DOMAIN + 'misc.php?mod=faq#id=118&messageid=120');
            }
        });
    }
};
