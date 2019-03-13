var $ = require('jquery');
var utils = require('./util');
var schedule = require('./schedule');
var layer = require('./layer');
module.exports = {
    _onDom: 'body',
    _inited: false,
    init: function () {
        if (!this._inited) {
            this._bindEventDom();
            this._inited = true;
        }
    },
    pageShow: function () {
        var self = this;
        var choose_class = require('../template/choose_class.hbs');
        utils.call('get', '/learning_center_wap/Myclass/fine_class_schedule_list', {}, {
            success: function (res) {
                var header_content = require('../template/wap_cpss_header.hbs');
                var header_data = {title: '选课', iconR_show: res.have_schedule};
                var main_content = require('../template/wap_cpss_choose_class_main.hbs');
                var mian_data = {data: res.schedule_info, plan_stage_data: res.plan_stage_data};
                var data = {header_content: header_content(header_data), main_content: main_content(mian_data)};
                var content = choose_class(data);
                $(self._onDom).html(content);
            }
        });
    },
    _bindEventDom: function () {
        var self = this;
        $(self._onDom).on('click', '.join_class', function () {
            var schedule_id = $(this).data('schedule_id');
            var plan_id = $(this).data('plan_id');
            var plan_stage_id = $(this).data('plan_stage_id');
            utils.call('post', '/learning_center_wap/Myclass/join_fine_class', {
                plan_id: plan_id,
                plan_stage_id: plan_stage_id,
                schedule_id: schedule_id
            }, {
                success: function (res) {
                    var toast_content = require('../template/toast.hbs');
                    var content = toast_content({
                        text: res.msg
                    });
                    layer.show2(content);
                    setTimeout(function () {
                        layer.hide2();
                        self.pageShow();
                    }, 2000);
                    //
                    // var toast = $('#toast');
                    // if (toast.css('display') != 'none') {
                    //     return;
                    // }
                    // $('#toast p').html(res.msg);
                    // toast.show();
                }
            });
        });
    }
};
