var $ = require('jquery');
var _ = require('lodash');
var utils = require('./util');
var layerPop = require('public/layer');
var commonPrompt = require('public/template/common_prompt.hbs');
module.exports = {
    showed: false,
    init: function () {

    },
    /**
     * 生成图片地址
     * @param name
     * @returns {string}
     */
    imgUrl: function (name) {
        return '/static/image/public/' + name;
    },
    /**
     * 显示弹出对话框
     * @param content
     * @param params
     * @returns {*}
     */
    show: function (content, params) {
        var self = this;
        if (this.showed) {
            return;
        }
        this.showed = true;
        params = params || {};

        var options = _.assign({
            width: '500px',
            title: false,
            max: false,
            min: false,
            drag: false,
            fixed: true,
            lock: true, /* 背景色 */
            background: '#000', /* 背景色 */
            opacity: 0.7, /* 透明度 */
            zIndex: 9999998,
            content: content,
            close: function () {
                $('body').off('click', '#close_popup');
                self.showed = false;
            }
        }, params);
        if (params.close) {
            options.close = function () {
                params.close();
                self.showed = false;
                if (!params.title) {
                    $('body').off('click', '#close_popup');
                }
            };
        }
        var dialog = $.dialog(options);
        if (!params.title) {
            $('body').on('click', '#close_popup', function () {
                dialog.close();
            });
        }

        return dialog;
        //     button: buttons
        //     // button: [
        //     //     {
        //     //         name: '暂不激活',
        //     //         callback: function () {
        //     //             this.content('你同意了').time(2);
        //     //             return false;
        //     //         },
        //     //     },
        //     //     {
        //     //         name: '前去激活',
        //     //         callback: function () {
        //     //             alert('激活');
        //     //         },
        //     //         focus: true,
        //     //     }
        //     // ]
    },
    /**
     * 显示通用弹出层
     * @param content
     * @param params
     * @returns {*}
     */
    showCommon: function (content, params) {
        var commonTpl = require('./template/common.tpl');
        var close = require('./img/close_v422.png');
        content = commonTpl({
            close: close,
            content: content
        });
        return this.show(content, params);
    },
    methodShow: function (content, params, html) {
        var self = this;
        var dom = 'body .ui_dialog tbody';
        var dialog = self.show(content, params);
        if ($(dom + ' tr').hasClass('append_ele')) {
            if (typeof html === 'undefined' || !html || html === '' || html === null) {
                $($(dom + ' .append_ele')).remove();
            } else {
                $(dom + ' .append_ele td').html(html);
            }
        } else if (typeof html !== 'undefined' && html !== '' && html !== null) {
            var htmls = '<tr class="append_ele"><td>' + html + '</td></tr>';
            $(dom).append(htmls);
        }

        return dialog;
    },
    /**
     * 显示报到倒计时
     * @param endTime
     * @param now
     */
    showCountDownPopup: function (endTime, now) {
        var self = this;
        layerPop.show(commonPrompt({
            title: '请您在本课下课后回来报到！',
            sub_title: '距离报到时间还有：<b class="color_4bb866" id="time_str">' + utils.getCountDownStr(endTime, now) + '</b>'
        }), {
            btn: ['知道了'],
            success: function () {
                self.interval = window.setInterval(function () {
                    var current = Math.floor((new Date()).valueOf() / 1000);
                    var diff = utils.getCountDownStr(endTime, current);
                    if (diff === 0) {
                        layerPop.layer.close(layerPop.layer.index);
                    } else {
                        $('#time_str').html(diff);
                    }
                }, 1000);
            },
            cancel: function () {
                window.clearInterval(self.interval);
            }
        });
    },
    /**
     * 教室信息弹出层
     * @param roomName
     * @param roomPwd
     * @param date
     */
    showRoomInfo: function (roomName, roomPwd, date) {
        var config = require('./config');
        var roomInfo = require('./template/room_info.tpl');
        var Clipboard = null;
        if (!utils.isOldBrowser()) {
            Clipboard = require('clipboard');
        }
        if (!this.clip && Clipboard) {
            this.clip = new Clipboard('#copy_pwd');
            this.clip.on('success', function (e) {
                alert('教室密码已复制:' + e.text);
                e.clearSelection();
            });

            this.clip.on('error', function () {
                alert('请使用Ctrl/Cmd + c 复制');
            });
        }

        var content = roomInfo({
            room_name: roomName,
            room_pwd: roomPwd,
            start_time: date,
            link: config.getLink('roomInfoLink'),
            yyimg: require('./img/yyImg_v422.png')
        });
        this.show(content, {title: '进入教室'});
    },
    withoutCourseware: function () {
        layerPop.show(commonPrompt({
            title: '录播课件正在制作中',
            sub_title: '稍后上传，请耐心等待哦~'
        }), {
            btn: ['知道了']
        });
    },
    /**
     * 下载zdtalk弹出层
     */
    downloadZdTalk: function (url, element, download, startTime) {
        if (utils.isMacOrLinux()) {
            layerPop.show(commonPrompt({
                title: '上课软件尚不支持此系统',
                sub_title: '请联系客服反馈问题'
            }), {
                btn: ['知道了']
            });
        } else {
            var lesson_classroom_url = '/zdtalk/lessonClassroom';
            var now = utils.now();
            if (now < startTime - 600 * 1000) {
                //开始上课前10分钟以前弹
                layerPop.show(commonPrompt({
                    title: '上课时间未到，是否现在进入教室',
                    sub_title: '现在进入教室没有老师哦'
                }), {
                    btn: ['进入教室'],
                    yes: function (index) {
                        layerPop.layer.close(index);
                        window.open(lesson_classroom_url, '_blank');
                        return true;
                    }
                });
            } else {
                window.open(lesson_classroom_url, '_blank');
            }
        }
    },
    /**
     * 达到每日学分上限提示
     * @param cb_continue
     */
    scoreLimit: function (cb_continue) {
        layerPop.show(commonPrompt({
            title: '今日学分已达上限，不会再获得学分了哦~',
            sub_title: '(出勤率和做题率正常统计，不会影响)'
        }), {
            btn: ['取消', '继续'],
            btn2: function (index) {
                layerPop.layer.close(index);
                cb_continue();
            }
        });
    },
    /**
     * 确认是否删除阶段课程
     * @param callback
     */
    confirmDelSchedule: function (callback) {
        layerPop.show(commonPrompt({
            title: '确定删除该课程吗？',
            sub_title: '删除课程后不可以重新添加'
        }), {
            btn: ['取消', '删除'],
            btn2: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    //按时完成类型单元测试超过30min进入考场提示
    handleOver30min: function (element) {
        var url = element.data('url');
        layerPop.show(commonPrompt({
            title: '超过开考时间30分钟进入考场',
            sub_title: '扣除30分钟测试时长，抓紧时间答题哦~',
        }), {
            btn: ['确定'],
            yes: function (index) {
                layerPop.layer.close(index);
                window.open(url, '_blank');
            }
        });
    },
    //一课一测重做提示
    handleTestRedo: function (element) {
        var url = element.data('url');
        layerPop.show(commonPrompt({
            title: '确定重做么？',
            sub_title: '重做会清空当前做题记录'
        }), {
            btn: ['重做', '不重做'],
            yes: function (index) {
                layerPop.layer.close(index);
                window.open(url, '_blank');
            }
        });
    },
    //单元测试重考提示
    handleRetest: function (element) {
        var url = element.data('url');
        layerPop.lineShow(commonPrompt({
            title: '参加重考，仅保留最新成绩哦'
        }), {
            btn: ['重做', '取消'],
            yes: function (index) {
                layerPop.layer.close(index);
                window.open(url, '_blank');
            }
        });
    },
    //单元测试开始考试倒计时
    handleNotStart: function (element, startTime, cb) {
        var self = this;
        var now = Math.floor((new Date()).valueOf() / 1000);
        if (startTime - now <= 0) {
            cb();
        }
        layerPop.show(commonPrompt({
            title: '距离考试时间还有：',
            sub_title: '<b class="color_888" id="time_str">' + utils.timeRemainStr(startTime - now) + '</b>',
        }), {
            btn: ['知道了'],
            success: function (layero, index) {
                self.interval = window.setInterval(function () {
                    now = Math.floor((new Date()).valueOf() / 1000);
                    if (startTime - now <= 0) {
                        window.clearInterval(self.interval);
                        layerPop.layer.close(index);
                        cb();
                    }
                    $('#time_str').html(utils.timeRemainStr(startTime - now));
                }, 1000);
            },
            cancel: function () {
                window.clearInterval(self.interval);
            }
        });
    }
};
