var $ = require('jquery');
var leftNav = require('./left_nav');
var schedule = require('./schedule');
var preferred = require('./preferred_page');
var notice = require('./notice');
var popup = require('public/popup');
var history = require('public/history');
var cpssCookie = require('public/cookie');
var _highLight = 'new_liveClass_current';

module.exports = {
    init: function () {
        var self = this;
        var slider = $('.slider3');
        //切换阶段课程插件
        slider.bxSlider({
            slideWidth: 248,
            minSlides: 4,
            maxSlides: 4,
            moveSlides: 1,
            slideMargin: 18,
            infiniteLoop: false,
            pager: false,
            hideControlOnEnd: true,
            responsive: false
        });
        $('.bx-wrapper').css('max-width', '1175px');
        slider.show();
        //切换阶段课程
        $('#switch_schedule').on('click', 'div', function (event) {
            var element = $(this);
            if (!element.hasClass(_highLight) && !element.hasClass('new_liveClass_none')) {
                var preHighLight = element.siblings('.' + _highLight);
                var highlightHtml = preHighLight.children('i');
                preHighLight.children('p').first().attr('class', 'imglist_title_w240 color_666');
                element.append(highlightHtml);
                element.children().first().attr('class', 'imglist_title');
                element.addClass(_highLight).siblings().removeClass(_highLight);
                schedule.update();
                history.push(schedule.id, {
                    plan: schedule.planId,
                    stage: schedule.planStageId
                });
                self.loadSchedule();
            } else if (element.hasClass('new_liveClass_none')) {
                var params = {
                    plan: schedule.planId,
                    stage: schedule.planStageId
                };
                window.location.href = '/#selectSchedule?' + $.param(params);
            }
        });
        //删除阶段课程
        $('.new_delete').on('click', function (event) {
            event.stopPropagation();
            var delId = $(this).parent().data('id');
            var nextSchedule = $(this).parent().siblings().first().data('id');
            if (delId !== schedule.id) {
                nextSchedule = schedule.id;
            }
            popup.confirmDelSchedule(function () {
                var url = schedule.buildUrl('schedule/delete');
                var data = {
                    nextId: nextSchedule || 0,
                    del: delId
                };
                schedule.load('post', url, data, {
                    success: function (ret) {
                        var str = $.param({
                            plan: schedule.planId,
                            stage: schedule.planStageId
                        });
                        if (ret.code === 200) {
                            if (!nextSchedule) {
                                window.location.href = '/#selectSchedule?' + str;
                            } else if (delId !== schedule.id) {
                                window.location.reload();
                            } else {
                                window.location.href = '/learningsystem/schedule/' + nextSchedule + '?' + str;
                            }
                        } else {
                            window.location.href = '/#selectSchedule?' + str;
                        }
                    }
                }, {dataType: 'json'});
            });
        });

        this.showTip();
        schedule.update();
        preferred.init();
        leftNav.init();
        notice.loadData();
        notice.showNavNotice();
    },
    /**
     * 显示阶段课程删课按钮提醒框
     */
    showTip: function () {
        if (cpssCookie.get('del_sche_tip') === '' && $('.new_delete_icon_off').length > 0) {
            var tipTpl = require('public/template/delete_schedule_tip.tpl');
            var content = tipTpl();
            var today = new Date();
            var expiryDate = new Date(today.getTime() + (360 * 86400000));
            cpssCookie.set('del_sche_tip', 1, expiryDate);
            $('.new_delete_contant').prepend(content);
            $('.new_delete_button').on('click', function (event) {
                $(this).parent().hide();
            });
        }
    },
    /**
     * 加载阶段课程数据
     */
    loadSchedule: function () {
        notice.loaded = false;
        notice.removePoint('nav');
        leftNav.highLightMenu(true);
        notice.loadData();
        notice.showNavNotice();

        var url = schedule.buildUrl('schedule/latest');
        schedule.load('post', url);
    }
};
