var $ = require('jquery');
var _options = require('./options');
var reservation_main = require('./reservation_mains');
module.exports = {
    init: function () {
        this.bindDom();
        reservation_main.init();
    },
    bindDom: function () {
        var basic = require('./lesson_basic');
        //取消预约按钮
        $(_options.learnMianClass).on('click', '.reservation_main .cancel_reservation_lesson', function () {
            basic.cancelReservation($(this), function () {
                var rest_num = parseInt($(_options.learnMianClass + ' .rest_reservation_num').text(), 10);
                if (rest_num >= 0) {
                    $(_options.learnMianClass + ' .rest_reservation_num').text(rest_num + 1);
                }
                reservation_main.selectReservationMain();
            });
        });
    },
    requestFunc: function (param) {
        $(_options.learnMianClass + ' .cpss_layout').html('');
        reservation_main.selectCourseReservation(param);
    }
};
