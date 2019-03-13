require('./css/common.css');
require('./css/unit_rest.css');
var $ = require('jquery');
var util = require('public/util.js');

util.preventBack();
var remain = parseInt($('#remain_time').val(), 10);
var minDom = $('#minute_remain');
var secDom = $('#second_remain');
var paperId = $('#paper_id').val();
/**
 * 更新剩余时间
 */
var setRemainStr = function (remain_sec) {
    var min = Math.floor(remain_sec / 60);
    var sec = Math.floor(remain_sec) - (min * 60);
    if (min >= 0 && min < 10) {
        min = '0' + min;
    }
    if (sec >= 0 && sec < 10) {
        sec = '0' + sec;
    }
    minDom.html(min);
    secDom.html(sec);
};

/**
 * 继续答题
 */
var goPart2 = function () {
    window.location.href = '/exam/unitTest/' + paperId;
};
setRemainStr(remain);
var interval = setInterval(function () {
    remain--;
    setRemainStr(remain);
    if (remain <= 0) {
        clearInterval(interval);
        goPart2();
    }
}, 1000);

$('#btn_restOver').on('click', function () {
    goPart2();
});

