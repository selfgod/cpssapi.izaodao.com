var $ = require('jquery');
var popup = require('public/popup');
var commonPrompt = require('public/template/common_prompt.hbs');
var layerPop = require('public/layer');
var dialogObj = {};
module.exports = {
    popup_button: '知道了',
    popupLayerClose: function () {
        layerPop.layer.closeAll();
    },
    popupDialogClose: function () {
        dialogObj.close();
    },
    layerMsg: function (msg) {
        layerPop.layer.msg(msg, {
            time: 1000
        });
    },
    unActivatePopup: function () {
        layerPop.show(commonPrompt({
            title: '你报名的商品还未激活，无法选课学习',
            sub_title: '现在激活，即可上课、享受特权哦'
        }), {
            btn: ['暂不激活', '前去激活'],
            btn2: function (index) {
                layerPop.layer.close(index);
                location.href = '/purchased';
            }
        });
    },
    unlockBeforePrompt: function (callback) {
        layerPop.show(commonPrompt({
            title: '前一阶段课程未全部完成，确定现在解锁本阶段么？',
            sub_title: '建议完成上一阶段后，再继续解锁'
        }), {
            btn: ['现在解锁', '暂不解锁'],
            yes: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    unlockSuccess: function (plan, stage, callback) {
        layerPop.show(commonPrompt({
            title: '解锁成功啦! 每个阶段，都可以自由选课程哦',
            sub_title: '根据实际需要选择上课进度、喜欢的老师、合适的上课时间'
        }), {
            btn: ['去选课'],
            yes: function (index) {
                layerPop.layer.close(index);
                var router = require('public/router');
                router.goHashUrl('selectSchedule?plan=' + plan + '&stage=' + stage);
            },
            cancel: function (index) {
                layerPop.layer.close(index);
                callback();
                return false;
            }
        });
    },
    cannotUnlockPrompt: function (plan_name) {
        layerPop.show(commonPrompt({
            title: '解锁本阶段需要升级当前课程计划',
            sub_title: '你的当前计划是' + plan_name
        }), {
            btn: ['以后再说', '现在升级'],
            btn2: function (index) {
                layerPop.layer.close(index);
                location.href = '/upgrade';
            }
        });
    },
    reservationBeforePopup: function (num, callback) {
        var reservationBeforePopup = require('./template/reservation_before.tpl');
        var self = this;
        var content = reservationBeforePopup({
            num: num
        });
        dialogObj = popup.show(content, {
            title: '提示',
            button: [
                {
                    name: '确认预约',
                    callback: function () {
                        callback();
                    },
                    focus: true
                }
            ]
        });
    },
    reservationTimeConflict: function (num, callback) {
        var self = this;
        layerPop.show(commonPrompt({
            title: '和其他课程的上课时间冲突啦',
            sub_title: '是否要继续预约本课？'
        }), {
            btn: ['确定', '取消'],
            yes: function (index) {
                layerPop.layer.close(index);
                self.reservationBeforePopup(num, callback);
            }
        });
    },
    reservationNumFull: function () {
        var self = this;
        layerPop.show(commonPrompt({
            title: '本课预约人数已满',
            sub_title: '换个课程预约学习吧'
        }), {
            btn: [self.popup_button]
        });
    },
    hasExpiredPopup: function (goods_name, show_time, exist_un_activate) {
        //已过期
        var button_name;
        if (exist_un_activate) {
            button_name = '立即激活';
        } else {
            button_name = '重新报名';
        }
        layerPop.show(commonPrompt({
            title: '已过期，无法继续学习',
            sub_title: '你报名的商品' + goods_name + '已于' + show_time + '过期'
        }), {
            btn: [button_name],
            yes: function (index) {
                layerPop.layer.close(index);
                location.href = '/purchased';
            }
        });
    },
    numberUseup: function (exist_un_activate, curricular) {
        var button_name, href_url;
        if (exist_un_activate) {
            href_url = '/purchased';
            button_name = '立即激活';
        } else {
            href_url = JP_DOMAIN + 'course/topic/vip';
            if (curricular === 'special' || curricular === 'custom') {
                href_url = JP_DOMAIN + 'course/topic/svip';
            }
            button_name = '重新报名';
        }
        layerPop.show(commonPrompt({
            title: '次数已用完',
            sub_title: '无法进行学习啦'
        }), {
            btn: [button_name],
            yes: function (index) {
                layerPop.layer.close(index);
                location.href = href_url;
            }
        });
    },
    cannotTimeReservation: function (reservation_time) {
        var self = this;
        layerPop.show(commonPrompt({
            title: '请预约 7 天内课程！',
            sub_title: '建议' + reservation_time + '以后预约本课程'
        }), {
            btn: [self.popup_button]
        });
    },
    cannotExpireReservation: function () {
        var self = this;
        layerPop.show(commonPrompt({
            title: '不能预约此课程',
            sub_title: '请预约开课时间在有效期以内的课程'
        }), {
            btn: [self.popup_button]
        });
    },
    cannotCancelReservation: function () {
        var self = this;
        layerPop.show(commonPrompt({
            title: '课程已开课',
            sub_title: '不能取消预约'
        }), {
            btn: [self.popup_button]
        });
    },
    lookClassroom: function (roomName, roomPwd, date) {
        dialogObj = popup.showRoomInfo(roomName, roomPwd, date);
    },
    cannotLookClassroom: function (look_room_time) {
        var self = this;
        layerPop.show(commonPrompt({
            title: '请您在' + look_room_time + '以后查看教室信息',
            sub_title: '开课前两小时可进入教室'
        }), {
            btn: [self.popup_button]
        });
    },
    joinScheduleNumChange: function (snum, callback) {
        layerPop.show(commonPrompt({
            title: '确认添加，将被扣除1次主修课次数',
            sub_title: '还剩 <b class="ml05 mr05 color_4bb866">' + snum + '次</b> 添加新课程的机会,点此查看<a href="/purchased/curricular/major" class="color_1d93f9"  target="_blank">次数使用规则</a>',
        }), {
            btn: ['取 消', '确 定'],
            btn2: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    joinScheduleLastNum: function (callback) {
        layerPop.lineShow(commonPrompt({
            title: '本次添加后，次数将全部用完',
        }), {
            btn: ['取 消', '确 定'],
            btn2: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    scheduleNotNum: function () {
        layerPop.show(commonPrompt({
            title: '次数已用完，无法添加新课程',
            sub_title: '如需继续学习，可以去购买新课'
        }), {
            btn: ['去购课'],
            yes: function (index) {
                layerPop.layer.close(index);
                location.href = JP_DOMAIN;
            }
        });
    },
    //上课时间冲突提示
    scheduleTimeConflict: function (conflict_time, content) {
        var conflictPopup = require('./template/conflict.tpl');
        var popup_content = conflictPopup({
            image: require('public/img/ff_nu.png'),
            title: conflict_time,
            content: content
        });
        dialogObj = popup.methodShow(popup_content, {
            title: '提示',
            button: [
                {
                    name: '不添加了',
                    focus: true
                }
            ]
        });
    },
    goodsUnExpirePopup: function () {
        layerPop.show(commonPrompt({
            title: '你报名的商品已过期',
            sub_title: '无法继续学习'
        }), {
            btn: ['查看详情'],
            yes: function (index) {
                layerPop.layer.close(index);
                location.href = '/purchased';
            }
        });
    },
    cancelReservationPopup: function (callback) {
        layerPop.show(commonPrompt({
            title: '是否取消该次预约？',
            sub_title: '请珍惜你的预约名额，建议不要取消'
        }), {
            btn: ['取消预约', '暂不取消'],
            yes: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    activateProcessPopup: function (num, expire_str, callback) {
        layerPop.show(commonPrompt({
            title: '你报名的课程商品还未激活，不能学习哦！',
            sub_title: '现在不激活，报名超过45天，系统会帮你自动激活哒'
        }), {
            btn: ['前去激活'],
            yes: function (index) {
                layerPop.layer.close(index);
                if (num === 1) {
                    callback();
                } else {
                    location.href = '/purchased';
                }
            }
        });
    },
    scheduleExpireShortage: function () {
        var self = this;
        layerPop.show(commonPrompt({
            title: '糟糕，有效期不足，无法完成本课的学习',
            sub_title: '建议更换其他更早开课的课程'
        }), {
            btn: [self.popup_button]
        });
    },
    stageJoinScheduleBeyond: function (count, name, callback) {
        layerPop.show(commonPrompt({
            title: '已添加' + count + '个' + name + '，确定继续添加吗？',
            sub_title: '别重复加课，给新同学留一个上课机会吧'
        }), {
            btn: ['我要添加', '不添加啦'],
            yes: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    operationFail: function (fail_msg) {
        var self = this;
        var msg = fail_msg || '操作失败！';
        layerPop.lineShow(commonPrompt({
            title: msg
        }), {
            btn: [self.popup_button]
        });
    },
    successPopup: function (title, callback) {
        var self = this;
        layerPop.lineShow(commonPrompt({
            title: title
        }), {
            btn: [self.popup_button],
            yes: function (index) {
                layerPop.layer.close(index);
                callback();
            },
            cancel: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    }
};
