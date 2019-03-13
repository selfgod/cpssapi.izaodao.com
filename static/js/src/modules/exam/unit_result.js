require('./css/common.css');
require('./css/unit_result.css');
var $ = require('jquery');
var util = require('public/util.js');
var layerPop = require('public/layer');
var config = require('public/config');

util.preventBack();

var submitted = $('#submitted').val();
var scheduleId = $('#schedule_id').val();
var lessonId = $('#lesson_id').val();
var planId = $('#plan_id').val();
var planStageId = $('#plan_stage_id').val();
var paperId = $('#paper_id').val();
var rightPer = $('#right_per').val();
var reviewUrl = config.getExamReviewLink(paperId);
var retestUrl = '/exam/unitTest/prepare/' + paperId + '?schedule_id=' + scheduleId + '&lesson_id=' + lessonId;
if (submitted === '0' && scheduleId !== '0') {
    //没有提交过答题记录
    var url = util.buildURL('exam', 'unitTest/done');
    util.callJson('post', url, {
        schedule_id: scheduleId,
        schedule_lesson_id: lessonId,
        id: paperId,
        plan_id: planId,
        plan_stage_id: planStageId
    }, {
        success: function (ret) {
            if (ret.code === 257) {
                layerPop.showTwoLine('正确率' + rightPer + '%', '本套题已加过学分啦!重考，不会重复加学分哦', {
                    btn: ['重做', '回顾'],
                    yes: function () {
                        window.location.href = retestUrl;
                    },
                    btn2: function () {
                        window.location.href = reviewUrl;
                    }
                });
            } else if (ret.code === 200) {
                if (ret.data.score > 0) {
                    layerPop.showTwoLine('正确率' + rightPer + '%，+' + ret.data.score + '学分！',
                        '学分可以换礼品，去积分商城逛逛吧~', {
                            btn: ['回顾详情', '积分商城'],
                            yes: function () {
                                window.location.href = reviewUrl;
                            },
                            btn2: function (index) {
                                layerPop.layer.close(index);
                                window.open(config.scoreShopLink, '_blank');
                            }
                        });
                } else if (ret.data.over_limit) { //达到当日积分上线
                    layerPop.showOneLine('正确率' + rightPer + '%，今日学分已达上限', {
                        btn: ['回顾详情', '积分商城'],
                        yes: function () {
                            window.location.href = reviewUrl;
                        },
                        btn2: function (index) {
                            layerPop.layer.close(index);
                            window.open(config.scoreShopLink, '_blank');
                        }
                    });
                } else if (ret.data.no_reward) { //当前阶段课程不加分
                    layerPop.showOneLine('正确率' + rightPer + '%', {
                        btn: ['重做', '回顾'],
                        yes: function () {
                            window.location.href = retestUrl;
                        },
                        btn2: function () {
                            window.location.href = reviewUrl;
                        }
                    });
                } else { //分数不达标
                    layerPop.showOneLine('正确率' + rightPer + '%，不能加学分', {
                        btn: ['重做', '回顾'],
                        yes: function () {
                            window.location.href = retestUrl;
                        },
                        btn2: function () {
                            window.location.href = reviewUrl;
                        }
                    });
                }
            } else {
                alert(ret.msg);
            }
        }
    });
}

