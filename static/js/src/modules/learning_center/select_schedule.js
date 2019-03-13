var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var learning_popup = require('./learning_popup');
var activation = require('public/activation');
var qq, zdtalk;
module.exports = {
    plan_id: 0,
    plan_stage_id: 0,
    schedule_id: 0,
    init: function () {
        this.bindDom();
    },
    bindDom: function () {
        var self = this;
        $(_options.learnMianClass).on('click', '.switch_class_mode p', function () {
            if (!$(this).hasClass('cpss_current')) {
                $(this).addClass('cpss_current').siblings().removeClass('cpss_current');
                self.scheduleLabel();
            }
        });
        $(_options.learnMianClass).on('click', '.class_time p', function () {
            if (!$(this).hasClass('current_screen_v5')) {
                $(this).addClass('current_screen_v5').siblings().removeClass('current_screen_v5');
                self.scheduleList();
            }
        });
        $(_options.learnMianClass).on('click', '.class_status p', function () {
            if (!$(this).hasClass('current_screen_v5')) {
                $(this).addClass('current_screen_v5').siblings().removeClass('current_screen_v5');
                self.scheduleList();
            }
        });
        $(_options.learnMianClass).on('click', '.join_schedule', function () {
            var element = $(this);
            self.schedule_id = parseInt(element.data('id'), 10);
            qq = element.data('qq');
            zdtalk = element.data('zdtalk');
            self.joinScheduleBefore();
        });
        $(_options.learnMianClass).on('click', '.joined_schedule', function () {
            self.schedule_id = parseInt($(this).data('id'), 10);
            var url = utils.buildURL('learn', 'joined_schedule');
            utils.call('post', url, {
                plan: self.plan_id,
                stage: self.plan_stage_id,
                schedule_id: self.schedule_id
            }, {
                success: function (obj) {
                    if (obj.code === 200) {
                        location.href = '/#myCourse?id=' + self.schedule_id;
                    } else if (obj.code === 283) {
                        learning_popup.activateProcessPopup(obj.num, obj.expire_str, _.bind(self.activationSingleGoods, self, obj.goods_id));
                    } else if (obj.code === 252 || obj.code === 221) {
                        learning_popup.goodsUnExpirePopup();
                    }
                }
            }, {dataType: 'json'});
        });
        //点击垃圾箱图标
        $('body').on('click', '.conflict_opt i', function () {
            var delSchedule = require('public/del_schedule');
            var conflict_id = parseInt($(this).parents('.conflict_opt').data('id'), 10);
            learning_popup.popupDialogClose();
            delSchedule.show({
                id: conflict_id,
                joinSchedule: true
            }, _.bind(self.scheduleList, self));
        });
    },
    /**
     * 加入阶段课程成功后提示页
     */
    showJoinSucceed: function (scheduleId) {
        zdtalk = parseInt(zdtalk, 10);
        var config = require('public/config');
        var tpl = require('./template/join_schedule_succ.tpl');
        var message = qq ? '加入班级QQ群：' + qq : '';
        var content = tpl({
            img: require('./img/class_v5.png'),
            qq: message,
            scheduleId: scheduleId,
            class_link: zdtalk === 1 ? config.zdTalkHelpLink : config.yyHelpLink
        });
        $('.cpss_layout').html(content);
        utils.scrollTop();
    },
    /**
     * 阶段课程主体
     * @param plan_id
     * @param plan_stage_id
     */
    scheduleMian: function (plan_id, plan_stage_id) {
        var self = this;
        self.plan_id = plan_id;
        self.plan_stage_id = plan_stage_id;
        var url = utils.buildURL('learn', 'select_schedule');
        utils.callWithLoading('get', url, {plan: plan_id, stage: plan_stage_id}, {
            success: function (res) {
                if (res) $(_options.learnMianClass + ' .cpss_layout').html(res);
                self.switchTeacher();
                self.validateActivation();
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' .cpss_layout');
    },
    /**
     * 阶段课程标签
     */
    scheduleLabel: function () {
        var self = this;
        var url = utils.buildURL('learn', 'schedule_label');
        var params = {
            plan: self.plan_id,
            stage: self.plan_stage_id,
            class_mode: $('.switch_class_mode .cpss_current').data('mode')
        };
        utils.callWithLoading('get', url, params, {
            success: function (res) {
                if (res) $(_options.learnMianClass + ' .schedule_lable').html(res);
                self.switchTeacher();
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' .schedule_lable');
    },
    /**
     * 阶段课程列表
     */
    scheduleList: function () {
        var self = this;
        var url = utils.buildURL('learn', 'schedule_list');
        var params = {
            plan: self.plan_id,
            stage: self.plan_stage_id,
            class_mode: $('.switch_class_mode .cpss_current').data('mode'),
            time_type: parseInt($('.class_time .current_screen_v5').data('id'), 10),
            course_status: parseInt($('.class_status .current_screen_v5').data('id'), 10)
        };
        utils.callWithLoading('get', url, params, {
            success: function (res) {
                if (res) $(_options.learnMianClass + ' #select_course_schedule').html(res);
                self.switchTeacher();
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' #select_course_schedule');
    },
    //课程次数变化提示
    scheduleNumChange: function (num, cb) {
        var self = this;
        var nums = parseInt(num, 10) > 0 ? parseInt(num, 10) : 0;
        if (nums > 1) {
            var snum = nums - 1;
            learning_popup.joinScheduleNumChange(snum, cb);
        } else if (nums === 1) {
            learning_popup.joinScheduleLastNum(cb);
        } else {
            learning_popup.scheduleNotNum();
        }
    },
    switchTeacher: function () {
        var self = this;
        $(_options.learnMianClass + ' .switch_teacher').each(function () {
            var data = {
                slideWidth: 240,
                moveSlides: 1,
                infiniteLoop: false,
                hideControlOnEnd: true,
                pager: false,
                controls: false
            };
            if (parseInt($(this).attr('data-length'), 10) > 1) {
                data.pager = true;
            }
            $(this).bxSlider(data);
        });
    },
    //加入阶段课程操作
    joinScheduleOperation: function (scheduleId) {
        var self = this;
        var url = utils.buildURL('learn', 'join_schedule');
        var params = {
            plan: self.plan_id,
            stage: self.plan_stage_id,
            schedule_id: scheduleId,
            before: -1
        };
        utils.call('post', url, params, {
            success: function (obj) {
                if (obj.code === 200) {
                    self.showJoinSucceed(scheduleId);
                }
            }
        }, {dataType: 'json'});
    },
    //加入阶段课程前
    joinScheduleBefore: function (before) {
        var self = this;
        var _before = before || 0;
        var url = utils.buildURL('learn', 'join_schedule');
        var params = {
            plan: self.plan_id,
            stage: self.plan_stage_id,
            schedule_id: self.schedule_id,
            before: _before
        };
        utils.call('post', url, params, {
            success: function (obj) {
                if (obj.code === 200) {
                    self.showJoinSucceed(self.schedule_id);
                } else if (obj.code === 212) {
                    self.scheduleNumChange(obj.num, function () {
                        self.joinScheduleOperation(self.schedule_id);
                    });
                } else if (obj.code === 211) {
                    learning_popup.scheduleTimeConflict(obj.conflict_time, obj.html);
                } else if (obj.code === 213) {
                    learning_popup.stageJoinScheduleBeyond(obj.join_count, obj.stage_name, _.bind(self.joinScheduleBefore, self, 1));
                } else if (obj.code === 283) {
                    learning_popup.activateProcessPopup(obj.num, obj.expire_str, _.bind(self.activationSingleGoods, self, obj.goods_id));
                } else if (obj.code === 221) {
                    learning_popup.goodsUnExpirePopup();
                }
                /*
                //暂时去掉有效期判断
                else if (obj.code === 254) {
                    learning_popup.scheduleExpireShortage();
                }*/
            }
        }, {dataType: 'json'});
    },
    validateActivation: function () {
        var self = this;
        var url = utils.buildURL('learn', 'activation_process');
        utils.callJson('post', url, {
            plan: self.plan_id,
            stage: self.plan_stage_id
        }, {
            success: function (obj) {
                if (obj.code === 283) {
                    learning_popup.activateProcessPopup(obj.num, obj.expire_str, _.bind(self.activationSingleGoods, self, obj.goods_id));
                }
            }
        });
    },
    activationSingleGoods: function (goods_id) {
        var self = this;
        var url = utils.buildURL('goods', 'activate');
        utils.callJson('post', url, {
            goods_id: goods_id
        }, {
            success: function (obj) {
                if (obj.code === 200) {
                    learning_popup.successPopup('激活成功！', function () {
                        location.reload();
                    });
                } else if (obj.code === 284) {
                    activation.notAllowActivateTimePopup(obj.data.allow_activate_time);
                } else {
                    learning_popup.operationFail(obj.msg);
                }
            }
        });
    }
};
