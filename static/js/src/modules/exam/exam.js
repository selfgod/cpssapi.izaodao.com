var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('./unit_call');
var layerPop = require('public/layer');
var config = require('public/config');
require('./css/report_wrong.css');
module.exports = {
    defaultCate: '',
    part1Num: '',
    part2Num: '',
    paperId: 0,
    dom: {},
    baseUrl: '',
    currentCate: '',
    /**
     * 已答题数
     */
    choseNum: {
        part1: 0,
        part2: 0
    },
    init: function (baseUrl, succ) {
        var self = this;
        var part1Submit = $('#part1_submit').val() === '1';
        this.defaultCate = $('#default_cate').val();
        this.part1Num = parseInt($('#part1_num').val(), 10);
        this.part2Num = parseInt($('#part2_num').val(), 10);
        this.paperId = $('#paper_id').val();
        this.baseUrl = baseUrl;
        var remain;
        this.getDom();

        if (this.defaultCate === 'listening' || part1Submit) {
            remain = parseInt($('#part2_remain_time').val(), 10);
            if (remain === 0) {
                //听力考试时间已到
                this.autoSubmit('listening');
            }
            this.dom.part_total.html(this.part2Num);
        } else {
            this.dom.part_total.html(this.part1Num);
            remain = parseInt($('#part1_remain_time').val(), 10);
            if (remain === 0) {
                //第一部分时间已到
                this.autoSubmit();
            }
        }
        //选择某个答案
        this.dom.detail.on('click', '.ykyc_v4_radio1', function () {
            var $el = $(this);
            var questionId = $el.parents('.ykyc_v4_choose').data('qid');
            var answerId = $el.val();
            unitCall.callJson('post', self.baseUrl + '/makeChoice', {
                paper_id: self.paperId,
                question_id: questionId,
                answer_id: answerId
            }, {
                success: function () {
                    self.changeCardStatus(questionId, 'chose');
                },
                error: function () {
                    $el.attr('checked', false);
                }
            });
        });
        //纠错
        this.dom.detail.on('click', '.report_wrong', function () {
            var questionId = $(this).data('qid');
            require('./css/report_wrong.css');
            var reportHbs = require('./template/report_wrong.hbs');
            self.layerObj = layerPop.show(reportHbs({questionId: questionId}), {area: ['460px', '350px']});
        });
        //提交纠错
        $('body').on('click', '.submit_desc', function () {
            var questionId = $(this).data('qid');
            var value = $('.wrong_desc').val() || '';
            var prompt = $('.reason_prompt');
            prompt.text('');
            if (value.length === 0) {
                prompt.text('错误描述不能为空');
            } else {
                layerPop.layer.close(self.layerObj);
                unitCall.callJson('post', self.baseUrl + '/answerCorrection', {
                    paper_id: self.paperId,
                    question_id: questionId,
                    description: value
                }, {
                    success: function () {
                        alert('提交成功');
                    }
                });
            }
        });
        //继续答题按钮
        this.dom.questionCard.on('click', '.continue_btn', function () {
            var $el = $(this);
            var cate = $el.data('cate');
            if ($el.hasClass('submit_total')) {
                //交卷
                self.manuallySubmit(cate, true);
            } else if ($el.hasClass('submit_part1')) {
                //交卷答听解
                self.manuallySubmit(cate, false);
            } else {
                var switchCate = require('./switch_cate');
                switchCate.triggerClick(switchCate.nextCate(cate));
            }
        });
        //去休息
        $('body').on('click', '#btn_rest', function () {
            self.submit(self.currentCate, function () {
                window.location.href = self.baseUrl + '/rest?paper_id=' + self.paperId;
            });
        });
        this.startTimer(remain);
        succ();
    },
    /**
     * 手动交卷
     */
    manuallySubmit: function (cate, isFinal) {
        var self = this;
        var remainNum = self.getTotalNum() - self.getAnswerNum();
        var titleFirst = '还有' + remainNum + '道题没有作答';
        var titleSub = '';
        if (remainNum === 0) {
            titleFirst = '您已答完本部分所有题目';
        }
        if (isFinal) {
            titleSub = '交卷后将不能再次回看或更改';
        } else {
            titleSub = '交卷后将不能再次回看或更改，您还可以<a href="javascript:;" id="btn_rest"><u>交卷并休息</u></a>十分钟哦';
        }
        layerPop.showTwoLine(titleFirst + '，是否马上交卷?', titleSub,
            {
                btn: ['再检查下', '交卷'],
                btn2: function () {
                    self.submit(cate, function () {
                        if (isFinal) {
                            window.location.href = config.getExamResultLink(self.paperId);
                        } else {
                            window.location.reload();
                        }
                    });
                }
            });
    },
    /**
     * 时间到，自动提交
     */
    autoSubmit: function (cate) {
        var i = 10;
        var self = this;
        if (cate === 'listening') {
            layerPop.showTwoLine('你的考试时间已到！', '<b id="sec_10">10</b>秒后跳转到报告页面！',
                {
                    closeBtn: 0,
                    btn: ['交卷'],
                    yes: function () {
                        self.submit(cate, function () {
                            window.location.href = config.getExamResultLink(self.paperId);
                        });
                    },
                    success: function () {
                        var intval = setInterval(function () {
                            i--;
                            $('#sec_10').html(i);
                            if (i === 0) {
                                clearInterval(intval);
                                self.submit(cate, function () {
                                    window.location.href = config.getExamResultLink(self.paperId);
                                });
                            }
                        }, 1000);
                    }
                }
            );
        } else {
            layerPop.showTwoLine('第一部分答题时间结束，你可以交卷答听解或休息一会', '<b id="sec_10">10</b>秒后跳转到休息页面！',
                {
                    closeBtn: 0,
                    btn: ['休息一会', '交卷'],
                    yes: function () {
                        self.submit(cate, function () {
                            window.location.href = self.baseUrl + '/rest?paper_id=' + self.paperId;
                        });
                    },
                    btn2: function () {
                        self.submit(cate, function () {
                            window.location.reload();
                        });
                    },
                    success: function () {
                        var interval = setInterval(function () {
                            i--;
                            $('#sec_10').html(i);
                            if (i === 0) {
                                clearInterval(interval);
                                self.submit(cate, function () {
                                    window.location.href = self.baseUrl + '/rest?paper_id=' + self.paperId;
                                });
                            }
                        }, 1000);
                    }
                }
            );
        }
    },
    /**
     * 提交做题记录
     */
    submit: function (cate, cb) {
        var url = this.baseUrl + '/submit';
        var part;
        if (cate === 'listening') {
            part = 'two';
        } else {
            part = 'one';
        }
        unitCall.callJson('post', url, {paper_id: this.paperId, part: part}, {
            success: function (ret) {
                if (ret.code === 500) {
                    alert('提交失败，请刷新页面后再次提交');
                } else {
                    cb();
                }
            }
        });
    },
    /**
     * 启动计时器
     */
    startTimer: function (secs) {
        var self = this;
        var remain = util.timeRemainStr(secs);
        self.dom.timeRemain.html(remain);
        secs--;
        setInterval(function () {
            if (secs === 0) {
                self.autoSubmit(self.currentCate);
            }
            remain = util.timeRemainStr(secs);
            self.dom.timeRemain.html(remain);
            secs--;
        }, 1000);
    },
    /**
     * 更新已答题数
     */
    incAnswerNum: function () {
        var value;
        if (this.currentCate === 'listening') {
            value = ++this.choseNum.part2;
        } else {
            value = ++this.choseNum.part1;
        }
        this.setAnswerNum(value);
    },
    /**
     * 设置当前分类
     * @param cate
     * @param data
     */
    setCate: function (cate) {
        this.currentCate = cate;
        if (cate === 'listening') {
            $('#part_title').html('');
        }
    },
    /**
     * 初始化已回答题数
     * @param num
     */
    initAnswerNum: function () {
        var num = parseInt(this.dom.answer_num.html(), 10);
        if (this.currentCate === 'listening') {
            this.choseNum.part2 = num;
        } else {
            this.choseNum.part1 = num;
        }
    },
    /**
     * 设置已回答题数
     * @param num
     */
    setAnswerNum: function (num) {
        this.dom.answer_num.html(num);
    },
    /**
     * 获取已回答数
     */
    getAnswerNum: function () {
        return parseInt(this.dom.answer_num.html(), 10);
    },
    /**
     * 获取总题数
     */
    getTotalNum: function () {
        return parseInt(this.dom.part_total.html(), 10);
    },
    /**
     * 修改答题卡显示状态
     */
    changeCardStatus: function (questionId, status) {
        var $el = $('#card_' + questionId);
        switch (status) {
            case 'chose':
                if (!$el.hasClass('now_choose')) {
                    $el.addClass('now_choose');
                    this.incAnswerNum();
                }
                break;
            default :

        }
    },
    getDom: function () {
        this.dom.part_total = $('#part_total');
        this.dom.answer_num = $('#answer_num');
        this.dom.detail = $('#detail_content');
        this.dom.timeRemain = $('#time_remain');
        this.dom.questionCard = $('#question_num');
    }
};
