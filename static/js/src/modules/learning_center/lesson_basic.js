var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var popup = require('public/popup');
var reservation_main = require('./reservation_mains');
var learning_popup = require('./learning_popup');
var _parentsDom = '.class_lesson_open';
module.exports = {
    init: function () {
        var self = this;
        //下载
        $(_options.learnMianClass).on('click', '.lesson_download', function () {
            var lesson_id = parseInt($(this).parents(_parentsDom).data('lesson'), 10);
            var schedule_id = parseInt($(this).parents(_parentsDom).data('schedule'), 10);
            var curricular = $(this).parents(_parentsDom).data('curricular');
            var plan_id = parseInt($(this).parents(_parentsDom).data('plan'), 10);
            var plan_stage_id = parseInt($(this).parents(_parentsDom).data('stage'), 10);
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            if (parseInt($(this).attr('data-exist'), 10) === 1) {
                self.lessionDownloadReview(schedule_id, lesson_id, plan_id, plan_stage_id, curricular, true);
            } else {
                popup.withoutCourseware();
            }
        });
        //回顾
        $(_options.learnMianClass).on('click', '.lesson_review', function () {
            var lesson_id = parseInt($(this).parents(_parentsDom).data('lesson'), 10);
            var schedule_id = parseInt($(this).parents(_parentsDom).data('schedule'), 10);
            var curricular = $(this).parents(_parentsDom).data('curricular');
            var plan_id = parseInt($(this).parents(_parentsDom).data('plan'), 10);
            var plan_stage_id = parseInt($(this).parents(_parentsDom).data('stage'), 10);
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            if (parseInt($(this).attr('data-exist'), 10) === 1) {
                self.lessionDownloadReview(schedule_id, lesson_id, plan_id, plan_stage_id, curricular, false);
            } else {
                popup.withoutCourseware();
            }
        });
        //查看教室
        $(_options.learnMianClass).on('click', '.look_room', function (event) {
            var element = $(this);
            var curricular = $(this).parents(_parentsDom).data('curricular');
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            var startTime = parseInt($(this).parents(_parentsDom).data('start'), 10);
            var roomName = $(this).parents(_parentsDom).data('room');
            var roomPwd = $(this).parents(_parentsDom).data('roompwd');
            var date = utils.formatTimestamp(parseInt(startTime, 10) * 1000);
            if (element.hasClass('zdtalk')) {
                var curDate = new Date();
                var download = element.attr('data-download');
                var url = element.attr('data-zdtalk');
                var cookie = require('public/cookie.js');
                var lesson_id = parseInt($(this).parents(_parentsDom).data('lesson'), 10);
                cookie.set('zdtalkLessonId', lesson_id, new Date(curDate.getTime() + 60000));
                popup.downloadZdTalk(url, element, download, parseInt(startTime, 10) * 1000);
            } else {
                learning_popup.lookClassroom(roomName, roomPwd, date);
            }
        });
        //不能查看教室
        $(_options.learnMianClass).on('click', '.not_look_room', function () {
            var look_room_time = $(this).data('look_time');
            learning_popup.cannotLookClassroom(look_room_time);
        });
        $(_options.learnMianClass).on('click', '.report_lesson', function () {
            var lesson_id = parseInt($(this).parents(_parentsDom).data('lesson'), 10);
            var schedule_id = parseInt($(this).parents(_parentsDom).data('schedule'), 10);
            var curricular = $(this).parents(_parentsDom).data('curricular');
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            var endTime = parseInt($(this).parents(_parentsDom).data('end'), 10);
            var now = Math.floor((new Date()).valueOf() / 1000);
            if (endTime - 60 * 30 > now) {
                //在下课前半小时之前
                popup.showCountDownPopup(endTime, now);
            } else {
                //报到+
                var params = {
                    scheduleId: schedule_id,
                    lessonId: lesson_id
                };
                params.isMajor = 0;
                if (curricular === 'major') {
                    var plan_id = parseInt($(this).parents(_parentsDom).data('plan'), 10);
                    var plan_stage_id = parseInt($(this).parents(_parentsDom).data('stage'), 10);
                    params.isMajor = 1;
                    params.planId = plan_id;
                    params.planStageId = plan_stage_id;
                }
                var checkIn = require('public/check_in');
                var user = require('public/user');
                user.isOverLimit(function () {
                    checkIn.show(params, function () {
                        location.reload();
                    });
                });
            }
        });
        //预约
        $(_options.learnMianClass).on('click', '.reservation_lesson', function () {
            var curricular = $(this).parents(_parentsDom).data('curricular');
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            var lesson_id = parseInt($(this).parents(_parentsDom).data('lesson'), 10);
            reservation_main.reservationBeforeData(lesson_id);
        });
        //不能预约
        $(_options.learnMianClass).on('click', '.not_reservation_lesson', function () {
            var curricular = $(this).parents(_parentsDom).data('curricular');
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            var reservation_time = $(this).data('reservation_time');
            learning_popup.cannotTimeReservation(reservation_time);
        });
        //不能取消预约
        $(_options.learnMianClass).on('click', '.not_cancel_reservation_lesson', function () {
            var curricular = $(this).parents(_parentsDom).data('curricular');
            if (!self.validateActivateAndExpire(curricular)) {
                return false;
            }
            learning_popup.cannotCancelReservation();
        });
    },
    /**
     * 取消预约
     */
    cancelReservation: function ($element, cb) {
        var $class = $element.parents(_parentsDom);
        var curricular = $class.data('curricular');
        if (!this.validateActivateAndExpire(curricular)) {
            return false;
        }
        var lesson_id = parseInt($class.data('lesson'), 10);
        reservation_main.cancelReservationLessonData(lesson_id, cb);
    },
    lessionDownloadReview: function (schedule_id, lesson_id, plan_id, plan_stage_id, curricular, download) {
        var params = {};
        if (curricular === 'major') {
            params.plan = plan_id;
            params.stage = plan_stage_id;
        } else {
            params.curricular = curricular;
        }
        if (!download) {
            window.open('/learningsystem/review/' + lesson_id + '?' + $.param(params));
            return false;
        }
        if (curricular === 'major' && download) {
            params.schedule = schedule_id;
        }
        var url = utils.buildURL('learningsystem', 'download/' + lesson_id + '?' + $.param(params));
        utils.call('get', url, {}, {
            success: function (data) {
                if (data.code === 200) {
                    if (download) {
                        location.href = data.data;
                    }
                }
            }
        }, {dataType: 'json'});
    },
    validateActivateAndExpire: function (curricular) {
        var learn_base = require('./learn_base');
        if (learn_base.info[curricular].hasGoods) {
            if (!learn_base.info[curricular].hasExpire) {
                if (learn_base.info[curricular].hasUnActivate) {
                    learning_popup.unActivatePopup();
                } else {
                    learning_popup.goodsUnExpirePopup();
                }
            } else {
                return true;
            }
        } else {
            learning_popup.operationFail('系统数据异常！');
        }
        return false;
    }
};
