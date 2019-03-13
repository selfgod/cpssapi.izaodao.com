var $ = require('jquery');
var utils = require('./util');
var layer = require('./layer');

var d_date = new Date();
var _date = {
    year: d_date.getFullYear(),
    month: d_date.getMonth() + 1,
    day: d_date.getDate(),
    week: d_date.getDay()
};

if (_date.day < 10) _date.day = '0' + _date.day;

module.exports = {
    _onDom: 'body',
    _inited: false,
    init: function () {
        if (!this._inited) {
            this._bindEventDom();
            this._inited = true;
        }
    },
    pageShow: function (p_data, second_flag) {
        var self = this;
        var schedule = require('../template/schedule.hbs');
        var post_data = p_data;
        var method = p_data ? 'post' : 'get';
        var second_select = second_flag ? 0 : 1;
        utils.call(method, '/learning_center_wap/Myclass/fine_class_schedule_lesson', post_data, {
            success: function (res) {
                var header_content = require('../template/wap_cpss_header.hbs');
                var header_data = {title: '课程表', iconR_show: 0};
                var main_content = require('../template/wap_cpss_schedule_main.hbs');
                var mian_data = {
                    data: res.finish_all_courses,
                    data_today: res.schedule_info_today,
                    next_class_data: res.next_class_info
                };
                var title_content = require('../template/wap_schedule_title.hbs');
                var title_data = {
                    month: _date.month,
                    week_array: res.week_day_array
                };
                var pop_data = {};
                var time_content = '';
                if (res.expire_time.time) {
                    pop_data = {
                        expire_text: res.expire_time.time[0] + '年' + res.expire_time.time[1] + '月' + res.expire_time.time[2] + '号',
                        jump_url: res.jump_url
                    };
                    var date_content = ['有效期截止至' + res.expire_time.time[0] + '-' + res.expire_time.time[1] + '-' + res.expire_time.time[2], '于' + res.expire_time.time[0] + '年' + res.expire_time.time[1] + '月' + res.expire_time.time[2] + '日过期'];
                    time_content = date_content[res.expire_time.expire_flag];
                }
                var data = {
                    header_content: header_content(header_data),
                    time_content: time_content,
                    main_content: main_content(mian_data),
                    title_content: title_content(title_data),
                    expire_flag: res.expire_time.expire_flag
                };
                var content = schedule(data);
                $('body').html(content);
                if (data.expire_flag && second_select) {
                    var over_time_content = require('../template/pop_over_time.hbs');
                    content = over_time_content(pop_data);
                    layer.show(content);
                }
            }
        });
    },
    _bindEventDom: function () {
        var self = this;
        $(self._onDom).on('click', '.wap_schedule_day li', function () {
            var day = $(this).data('day');
            var w = $(this).data('w');
            var p_data = {date: day, day: w};
            self.pageShow(p_data, 1);
        });

        $(self._onDom).on('click', '.wap_cpss_classroom_button', function () {
            var schedule_lesson_id = $(this).data('schedule_lesson_id');
            utils.call('post', '/learning_center_wap/Myclass/get_classroom_info', {schedule_lesson_id: schedule_lesson_id}, {
                success: function (res) {
                    var over_time_content = require('../template/pop_have_class.hbs');
                    var content = over_time_content({
                        yy_number: res.yy,
                        room_name: res.room_name,
                        room_pwd: res.room_pwd,
                        class_time: res.start_time
                    });
                    layer.show(content);
                    if (!self.clip) {
                        require.ensure([], function (require) {
                            var Clipboard = require('clipboard');
                            self.clip = new Clipboard('.copy_pwd');
                            self.clip.on('success', function (e) {
                                alert('教室密码已复制:' + e.text);
                                e.clearSelection();
                            });
                        });
                    }
                }
            });
        });
    }
};
