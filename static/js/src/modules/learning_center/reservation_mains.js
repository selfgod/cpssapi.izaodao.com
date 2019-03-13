var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _config = require('./options');
var pagination = require('public/pagination');
var switch_curricular = require('./switch_curricular');
var learning_popup = require('./learning_popup');
module.exports = {
    curricular: '',
    basic: 0,
    content_cat: 0,
    page: 0,
    init: function () {
        this.bindDom();
        this.basic = 0;
        this.content_cat = 0;
        this.page = 0;
        this.curricular = '';
    },
    bindDom: function () {
        var self = this;
        $(_config.learnMianClass).on('click', '.fit_basic p', function () {
            if (!$(this).hasClass('current_screen_v5')) {
                self.basic = parseInt($(this).data('id'), 10);
                $(this).addClass('current_screen_v5').siblings().removeClass('current_screen_v5');
                self.selectReservationMain();
            }
        });
        $(_config.learnMianClass).on('click', '.content_cat p', function () {
            if (!$(this).hasClass('current_screen_v5')) {
                self.content_cat = parseInt($(this).data('id'), 10);
                $(this).addClass('current_screen_v5').siblings().removeClass('current_screen_v5');
                self.selectReservationMain();
            }
        });
        //分页点击
        pagination.bind('reservation_main', function (page) {
            self.page = page;
            self.selectReservationMain();
        });
        switch_curricular.bindCurricularType('.curricular_oral_type', function () {
            self.myReservationList();
        });
        switch_curricular.bindCurricularType('.curricular_elective_type', function () {
            self.myReservationList();
        });
        switch_curricular.bindCurricularType('.curricular_special_type', function () {
            self.myReservationList();
        });
        switch_curricular.bindCurricularType('.curricular_custom_type', function () {
            self.myReservationList();
        });
    },
    /**
     * 选课预约制请求
     * @param curricular
     */
    selectCourseReservation: function (curricular) {
        var self = this;
        self.curricular = curricular;
        self.basic = 0;
        self.content_cat = 0;
        self.page = 0;
        var url = utils.buildURL('learn', 'select_course_reservation');
        utils.callWithLoading('get', url, {curricular: curricular}, {
            success: function (res) {
                if (res) $(_config.learnMianClass + ' .cpss_layout').html(res);
            }
        }, {dataType: 'html'}, _config.learnMianClass + ' .cpss_layout');
    },
    /**
     * 预约制选课主体内容
     */
    selectReservationMain: function () {
        var self = this;
        var params = {
            curricular: self.curricular,
            basic: self.basic,
            content_cat: self.content_cat,
            page: self.page
        };
        var url = utils.buildURL('learn', 'select_reservation_main');
        utils.callWithLoading('get', url, params, {
            success: function (res) {
                if (res) $(_config.learnMianClass + ' .reservation_main').html(res);
            }
        }, {dataType: 'html'}, _config.learnMianClass + ' .reservation_list_detial');
    },
    /**
     * 预约前
     * @param lesson_id
     */
    reservationBeforeData: function (lesson_id) {
        var self = this;
        var url = utils.buildURL('learn', 'reservation_before');
        utils.call('post', url, {
            lesson_id: lesson_id,
            curricular: self.curricular
        }, {
            success: function (obj) {
                if (obj.code === 200) {
                    obj.lesson_id = lesson_id;
                    self.reservationBeforeHandle(obj);
                } else if (obj.code === 216) {
                    //未激活
                    learning_popup.unActivatePopup();
                } else if (obj.code === 221) {
                    //已过期
                    learning_popup.hasExpiredPopup(obj.goods_name, obj.show_time, obj.exist_un_activate);
                } else if (obj.code === 219) {
                    //预约人数已满
                    learning_popup.reservationNumFull();
                } else if (obj.code === 222) {
                    //无次数
                    learning_popup.numberUseup(obj.exist_un_activate, self.curricular);
                } else if (obj.code === 224) {
                    learning_popup.cannotExpireReservation();
                }
            }
        }, {dataType: 'json'});
    },
    /**
     * 预约
     * @param lesson_id
     */
    reservationLessonData: function (lesson_id) {
        var self = this;
        var url = utils.buildURL('learn', 'reservation_lesson');
        utils.call('post', url, {
            lesson_id: lesson_id,
            curricular: self.curricular
        }, {
            success: function (obj) {
                if (obj.code === 200) {
                    var rest_num = parseInt($(_config.learnMianClass + ' .rest_reservation_num').text(), 10);
                    if (rest_num > 0) {
                        $(_config.learnMianClass + ' .rest_reservation_num').text(rest_num - 1);
                    }
                    self.reservationSuccess(self.curricular, obj.enable_zdtalk);
                } else if (obj.code === 216) {
                    //未激活
                    learning_popup.unActivatePopup();
                } else if (obj.code === 221) {
                    //已过期
                    learning_popup.hasExpiredPopup(obj.goods_name, obj.show_time, obj.exist_un_activate);
                } else if (obj.code === 219) {
                    //预约人数已满
                    learning_popup.reservationNumFull();
                } else if (obj.code === 222) {
                    //无次数
                    learning_popup.numberUseup(obj.exist_un_activate, self.curricular);
                } else if (obj.code === 224) {
                    learning_popup.cannotExpireReservation();
                }
            }
        }, {dataType: 'json'});
    },
    /**
     * 取消预约
     * @param lesson_id
     * @param cb
     */
    cancelReservationLessonData: function (lesson_id, cb) {
        var self = this;
        var url = utils.buildURL('learn', 'cancel_reservation_lesson');
        learning_popup.cancelReservationPopup(function () {
            utils.call('post', url, {
                lesson_id: lesson_id,
                curricular: self.curricular
            }, {
                success: function (obj) {
                    if (obj.code === 200) {
                        cb();
                    } else if (obj.code === 216) {
                        //未激活
                        learning_popup.unActivatePopup();
                    } else if (obj.code === 221) {
                        //已过期
                        learning_popup.hasExpiredPopup(obj.goods_name, obj.show_time);
                    }
                }
            }, {dataType: 'json'});
        });
    },
    /**
     * 我的预约页
     * @param curricular
     */
    myReservation: function (curricular, type) {
        var self = this;
        self.curricular = curricular;
        var url = utils.buildURL('learn', 'my_reservation');
        utils.callWithLoading('get', url, {curricular: curricular, type: type}, {
            success: function (res) {
                if (res) $(_config.learnMianClass + ' .cpss_layout').html(res);
            }
        }, {dataType: 'html'}, _config.learnMianClass + ' .cpss_layout');
    },
    /**
     * 我的预约课程列表
     */
    myReservationList: function () {
        var self = this;
        var type = $(_config.learnMianClass + ' .my_curricular_type .current_v5').data('type');
        var url = utils.buildURL('learn', 'my_reservation_list');
        utils.callWithLoading('get', url, {curricular: self.curricular, type: type}, {
            success: function (res) {
                if (res) $(_config.learnMianClass + ' .my_reservation_lesson_list').html(res);
            }
        }, {dataType: 'html'}, _config.learnMianClass + ' .my_reservation_lesson_list');
    },
    //预约成功前操作
    reservationBeforeHandle: function (data) {
        var self = this;
        if (data.time_conflict === 1) {
            learning_popup.reservationTimeConflict(data.min_num, _.bind(self.reservationLessonData, self, data.lesson_id));
        } else {
            learning_popup.reservationBeforePopup(data.min_num, _.bind(self.reservationLessonData, self, data.lesson_id));
        }
    },
    /**
     * 预约成功提示层
     * @param curricular
     * @param enable_zdtalk
     */
    reservationSuccess: function (curricular, enable_zdtalk) {
        var popup = require('public/popup');
        var _commonConfig = require('public/config');
        var reservation_success = require('./template/reservation_success.tpl');
        var params = {
            image: popup.imgUrl('ff_ok.png'),
            course_link: 'myCourse/' + curricular + '?type=reserved'
        };
        if (enable_zdtalk === 1) {
            params.class_link = _commonConfig.zdTalkHelpLink;
        } else {
            params.class_link = _commonConfig.yyHelpLink;
        }
        $('.reservation_content').html(reservation_success(params));
        utils.scrollTop();
    }
};
