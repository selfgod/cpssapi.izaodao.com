require('./css/common.css');
require('./css/unit_enter.css');
var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('./unit_call.js');

var sec = 4;
var $btn = $('#start');
var interval = setInterval(function () {
    if (sec > 0) {
        $btn.html('开始考试（0' + sec + '）');
        sec--;
    } else {
        $btn.html('开始考试').addClass('btn-green');
        clearInterval(interval);
    }
}, 1000);

//增加做题记录
var addResult = function (paperId, scheduleId, lessonId, cb) {
    var url = util.buildURL('exam', 'unitTest/addResult');
    unitCall.callJson('post', url, {paper_id: paperId, schedule_id: scheduleId, lesson_id: lessonId}, {
        success: function (data) {
            if (data.data.ret) {
                cb();
            }
        }
    });
};

$btn.on('click', function () {
    var $el = $(this);
    if (!$el.hasClass('btn-green')) {
        return;
    }
    var paperId = $el.data('paper');
    var url = util.buildURL('exam', 'unitTest/checkStatus');
    unitCall.callJson('get', url, {paper_id: paperId}, {
        success: function (ret) {
            var status = ret.data.status;
            if (status === 'not_finish') {
                var layerPop = require('public/layer');
                var commonPrompt = require('public/template/common_prompt.hbs');
                layerPop.show(commonPrompt({
                    title: '是否要继续上次答题？',
                    sub_title: '重新答题不保留上次答题记录！'
                }), {
                    btn: ['继续答题', '重新答题'],
                    yes: function () {
                        window.location.href = '/exam/unitTest/' + paperId;
                    },
                    btn2: function () {
                        addResult(paperId, $el.data('schedule'), $el.data('lesson'), function () {
                            window.location.href = '/exam/unitTest/' + paperId;
                        });
                    }
                });
            } else {
                addResult(paperId, $el.data('schedule'), $el.data('lesson'), function () {
                    window.location.href = '/exam/unitTest/' + paperId;
                });
            }
        }
    });
});

