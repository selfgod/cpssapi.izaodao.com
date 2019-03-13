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
        $(_options.learnMianClass).on('click', '.my_reservation_lesson_list .cancel_reservation_lesson', function () {
            basic.cancelReservation($(this), function () {
                reservation_main.myReservationList();
            });
        });
    },
    requestFunc: function (curricular, type) {
        $(_options.learnMianClass + ' .cpss_layout').html('');
        reservation_main.myReservation(curricular, type);
    }
};
