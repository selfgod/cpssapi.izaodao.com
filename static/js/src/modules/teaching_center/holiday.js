var $ = require('jquery');
var _ = require('lodash');
var utils = require('../../public/util.js');
var timeChoose = require('./timeChoose');
var holidayPopup = require('./holiday_popup');
module.exports = {
    holidayClass: '.holiday',
    init: function () {
        var self = this;
        timeChoose.init();
        self.bingTagInit();
    },
    bingTagInit: function () {
        var self = this;
        var startObj = '#choose_start_time';
        var endObj = '#choose_end_time';
        var reasonObj = '#holiday_reason';
        $(self.holidayClass).on('click', '.submit', function () {
            var submit_prompt_obj = self.holidayClass + ' .submit_prompt';
            var start_time = $.trim($(startObj).val().toString());
            var end_time = $.trim($(endObj).val().toString());
            var reason = $.trim($(reasonObj).val().toString());
            var dataType = $(this).attr('data-type');
            if (dataType == '' || dataType == null) {
                return false;
            }
            if (start_time == '' || start_time == null) {
                $(submit_prompt_obj).removeClass('hide').html('开始时间不能为空！');
                return false;
            }
            if (end_time == '' || end_time == null) {
                $(submit_prompt_obj).removeClass('hide').html('结束时间不能为空！');
                return false;
            }
            if (reason == '' || reason == null) {
                $(submit_prompt_obj).removeClass('hide').html($(reasonObj).attr('placeholder'));
                return false;
            }
            $(submit_prompt_obj).addClass('hide');
            if (dataType == 'suspend') {
                holidayPopup.suspendBeforPopup(_.bind(self.suspendRequestData, self, start_time, end_time, reason));
            }
            if (dataType == 'leave') {
                self.leaveRequestData(start_time, end_time, reason);
            }
        });
        $(self.holidayClass).on('click', '.unsubmit', function () {
            var doing = $(this).attr('data-do');
            if (doing == 'leave_doing') {
                holidayPopup.holidayDoingPopup('leave', 'leave');
            } else if (doing == 'suspend_doing') {
                holidayPopup.holidayDoingPopup('suspend', 'leave');
            }
        });
        $('.detail').on('click', '.cancel_leave', function () {
            var leave_id = parseInt($(this).attr('data-leave-id'), 10);
            if (!leave_id) {
                return false;
            }
            self.cancelLeave(leave_id);
        });
        $('.stop_suspend').on('click', function () {
            holidayPopup.suspendStopBeforPopup(_.bind(self.stopSuspendRequest, self));
        });
    },
    leaveRequestData: function (start_time, end_time, reason) {
        if (!start_time || !end_time || !reason) {
            return false;
        }
        var data = {
            start_time: start_time,
            end_time: end_time,
            reason: reason
        };
        var url = utils.buildURL('teaching', 'leave_submit');
        utils.call('post', url, data, {
            success: function (obj) {
                if (obj.code == 200) {
                    holidayPopup.holidaySuccessPopup('请假成功！');
                } else if (obj.code == 232) {
                    holidayPopup.holidayDoingPopup('leave', 'leave');
                } else if (obj.code == 233) {
                    holidayPopup.holidayDoingPopup('suspend', 'leave');
                } else if (obj.code == 235) {
                    holidayPopup.holidayTimePopup('leave');
                } else {
                    holidayPopup.holidayFailPopup('请假失败！');
                }
            }
        }, {dataType: 'json'});
    },
    cancelLeave: function (leave_id) {
        var url = utils.buildURL('teaching', 'cancel_leave');
        utils.call('post', url, {leave_id: leave_id}, {
            success: function (obj) {
                if (obj.code == 200) {
                    holidayPopup.holidaySuccessPopup('销假成功！');
                } else {
                    holidayPopup.holidayFailPopup('销假失败！');
                }
            }
        }, {dataType: 'json'});
    },
    suspendRequestData: function (start_time, end_time, reason) {
        if (!start_time || !end_time || !reason) {
            return false;
        }
        var data = {
            start_time: start_time,
            end_time: end_time,
            reason: reason
        };
        var url = utils.buildURL('teaching', 'suspend_submit');
        utils.call('post', url, data, {
            success: function (obj) {
                if (obj.code == 200) {
                    holidayPopup.holidaySuccessPopup('休学成功！');
                } else if (obj.code == 241) {
                    holidayPopup.suspendCannotPopup();
                } else if (obj.code == 242) {
                    holidayPopup.holidayFailPopup('请在课程有效期内开始休学！');
                } else if (obj.code == 243) {
                    holidayPopup.holidayTimePopup('suspend');
                } else {
                    holidayPopup.holidayFailPopup('休学失败！');
                }
            }
        }, {dataType: 'json'});
    },
    stopSuspendRequest: function () {
        var url = utils.buildURL('teaching', 'suspend_stop');
        utils.call('post', url, {}, {
            success: function (obj) {
                if (obj.code == 200) {
                    holidayPopup.holidaySuccessPopup('停止休学成功！');
                }
            }
        }, {dataType: 'json'});
    }
};
