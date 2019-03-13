require('../css/redefine.css');
var $ = require('jquery');
var utils = require('./util');
var schedule = require('./schedule');
var choose_class = require('./choose_class');
var router = require('./router');
require('./ga');

router.init();
choose_class.init();
schedule.init();
router.route('choose', function () {
    choose_class.pageShow();
});

router.route('schedule', function () {
    schedule.pageShow();
});

$('body').on('click', '.back_button', function () {
    router.goBack();
});

$('body').on('click', '.wap_cpss_close', function () {
    $('.wap_cpss_popup_bg').hide();
    $('.wap_cpss_popup').hide();
});

//页面一进入就发送请求,得到精品课/非精品课页面
utils.call('get', '/learning_center_wap/Myclass/fine_class', {}, {
    success: function (res) {
        if (res.jump_url) {
            window.location.href = res.jump_url;
        } else if (res.have_class) { //课程表
            router.route('/', function () {
                schedule.pageShow();
            });
            schedule.pageShow();
        } else { //选课
            router.route('/', function () {
                choose_class.pageShow();
            });
            choose_class.pageShow();
        }
    }
});

