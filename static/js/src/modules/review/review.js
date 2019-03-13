require('../learning_center/css/cpss_tip_layer.css');
var $ = require('jquery');
var _ = require('lodash');
var cyberplayer = require('cyberplayer');
var checkIn = require('public/check_in');
var warning = require('./template/checkin_warning.tpl');
var popup = require('public/popup');
var util = require('public/util');
module.exports = {
    totalTime: 0, //sec
    dialog: null,
    status: false,
    showViedo: function () {
        var self = this;
        var player = $('#playercontainer');
        if (player.length > 0) {
            var planId = $('#plan_id').val();
            var planStageId = $('#plan_stage_id').val();
            var scheduleId = $('#schedule_id').val();
            var lessonId = $('#lesson_id').val();
            var url = $('#cpssapi_url').val();
            var is_reserved = $('#is_reserved').val();
            var isMajor = !!(parseInt(planId, 10) && parseInt(planStageId, 10));
            //点击补课
            $('.btn').on('click', function () {
                var element = $(this);
                if (!element.hasClass('already')) {
                    if (self.totalTime > 60 * 20) {
                        if (element.hasClass('active')) {
                            $('.tip').html('可以报到啦');
                        }
                        checkIn.show({
                            url: url + 'v/learning/checkIn',
                            isMajor: isMajor,
                            planId: planId,
                            planStageId: planStageId,
                            scheduleId: scheduleId,
                            lessonId: lessonId
                        }, function (data) {
                            self.btn_status = true;
                            element.removeClass('active').addClass('already').html('已报到');
                            $('.tip').html('');
                        });
                    } else {
                        $('.tip').html('累计观看时长不足<span>20</span>分钟，无法报到哦~');
                        var content = warning();
                        self.dialog = popup.show(content);
                    }
                }
            });

            $('.arrow').on('click', function () {
                $(this).toggleClass('show').next().toggleClass('show').children().toggle();
                self.player_resize($(window), myPlayer, is_reserved);
            });

            $('body').on('click', '.txt3, .close4', function () {
                self.dialog.close();
            });

            var height = this.getPlayerHeight($(window));
            var width = this.getPlayerWidth($(window));
            var link = $('#link').val();
            //播放器
            var myPlayer = cyberplayer('playercontainer').setup({
                width: width,
                height: height,
                margin: 'auto',
                backcolor: '#FFFFFF',
                stretching: 'uniform',
                file: link,
                autoStart: true,
                repeat: false,
                volume: 100,
                controls: 'over',
                controlbar: {
                    barLogo: false
                },
                ak: 'a853a3bd61c34ae7ad15e138b2463910' // 公有云平台注册即可获得accessKey
            });

            window.setInterval(function () {
                self.totalTime++;
                if (self.totalTime > 60 * 20 && !self.status) {
                    self.change_status();
                    self.status = true;
                }
            }, 1000);

            self.player_resize($(window), myPlayer, is_reserved);

            $(window).resize(_.throttle(function () {
                var element = $(this);
                self.player_resize(element, myPlayer, is_reserved);
            }, 300));
            this.heartBeat();
        }
    },
    /**
     * 延长session过期时间
     */
    heartBeat: function () {
        var url = util.buildURL('api', 'user/uid');
        window.setInterval(function () {
            util.call('get', url);
        }, 600000);
    },
    getPlayerHeight: function (element, r_h) {
        return element.height() - r_h;
    },
    getPlayerWidth: function (element) {
        var width = element.width();
        return width < 1024 ? 1024 : width;
    },
    change_status: function () {
        $('.btn').addClass('active');
        $('.tip').html('');
    },
    player_resize: function (element, myPlayer, is_reserved) {
        var self = this;
        var r_h = 170;
        if (!is_reserved) {
            r_h = 50;
        }
        var h = self.getPlayerHeight(element, r_h);
        var w = self.getPlayerWidth(element);
        if ($('.arrow').hasClass('show')) {
            w -= 200;
        }
        myPlayer.resize(w, h);
    }
};
