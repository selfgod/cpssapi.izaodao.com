var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('../exam/unit_call');
var layerPop = require('public/layer');
var config = require('public/config');
var qualityHbs = require('./template/qualify.hbs');
var publicJs = require('./public');

module.exports = {
    defaultCate: '',
    part1Num: '',
    part2Num: '',
    resultId: 0,
    dom: {},
    baseUrl: '',
    currentCate: '',
    /**
     * 已答题数
     */
    choseNum: 0,
    init: function (baseUrl, succ) {
        var self = this;
        self.defaultCate = $('#default_cate').val();
        self.baseUrl = baseUrl;
        self.resultId = $('#result_id').val();
        // var remain;
        self.getDom();
        //选择某个答案
        self.dom.detail.on('click', '.main_radio', function () {
            var $el = $(this);
            var questionId = $el.parents('.main_question_id').data('qid'); //问题id
            var answerId = $el.val(); //选择答案 id
            self.changeCardStatus(questionId, 'chose'); //答题卡
            unitCall.callJson('post', self.baseUrl + '/makeChoice', {
                question_id: questionId,
                answer_id: answerId
            }, {
                success: function (ret) {
                    if (ret.code == 200) {
                        self.changeCardStatus(questionId, 'chose'); //答题卡
                    }
                },
                error: function () {
                    $el.attr('checked', false);
                }
            });
        });
        // //继续答题按钮
        this.dom.questionCard.on('click', '.continue_btn', function () {
            var $el = $(this);
            var cate = $el.data('cate');
            if ($el.hasClass('submit_page')) {
                //交卷
                self.manuallySubmit(cate, true);
            } else {
                var switchCate = require('./switch_cate');
                switchCate.triggerClick(switchCate.nextCate(cate));
            }
        });
        //升级重新测生成考题
        this.dom.main.on('click', '.generateExam', function () {
            var $el = $(this);
            var grade_id = $el.data('grade');
            publicJs.generateExam(grade_id);
        });
        succ();
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
     * 更新已答题数
     */
    incAnswerNum: function () {
        var value;
        value = ++this.choseNum;
        this.setAnswerNum(value);
    },
    /**
     * 设置已回答题数
     * @param num
     */
    setAnswerNum: function (num) {
        if (num > self.getTotalNum) num = self.getTotalNum;
        this.dom.answer_num.html(num);
    },
    /**
     * 手动交卷
     */
    manuallySubmit: function (cate, isFinal) {
        var self = this;
        var remainNum = self.getTotalNum() - self.getAnswerNum();
        if (remainNum < 0) {
            remainNum = 0;
        }
        var titleFirst = '还有' + remainNum + '道题没有作答';
        var titleSub = '';
        var is_done = 0;
        var btn_str = '';
        if (remainNum === 0) {
            titleFirst = '您已答完所有题目是否马上交卷?';
            is_done = 1;
        }
        if (is_done) {
            titleSub = '交卷后将不能再次回看或更改';
            btn_str = ['再检查一下', '交卷'];
        } else {
            titleSub = '答完所有的题才能交卷哦！';
            btn_str = ['继续答题'];
        }
        layerPop.showTwoLine(titleFirst, titleSub,
            {
                btn: btn_str,
                btn2: function () {
                    self.submit(cate, function (data) {
                        console.log(data);
                        if (data.code == 200) {
                            var ob = data.data;
                            // ob.grade_type = parseInt(ob.grade_type);
                            if (ob.is_pass == 1) { //合格跳转main 1定级  2升级
                                window.location.href = '/grade/main';
                            } else {
                                ob.current_grade = ob.target_grade - 1; //当前级别
                                $('#main').html(qualityHbs(ob));
                            }
                        } else {
                            alert(data.msg);
                        }
                    });
                }
            });
    },
    /**
     * 提交做题记录
     */
    submit: function (cate, cb) {
        var url = this.baseUrl + '/submit';
        unitCall.callJson('post', url, {cate: cate}, {
            success: function (ret) {
                if (ret.code === 500) {
                    alert('提交失败，请刷新页面后再次提交');
                } else {
                    cb(ret);
                }
            }
        });
    },
    /**
     * 初始化已回答题数
     * @param num
     */
    initAnswerNum: function () {
        var num = parseInt(this.dom.answer_num.html(), 10);
        this.choseNum = num;
    },
    /**
     * 获取总题数
     */
    getTotalNum: function () {
        return parseInt(this.dom.part_total.html(), 10);
    },
    /**
     * 获取已回答数
     */
    getAnswerNum: function () {
        return parseInt(this.dom.answer_num.html(), 10);
    },
    /**
     * 修改答题卡显示状态
     */
    changeCardStatus: function (questionId, status) {
        var $el = $('#card_' + questionId);
        switch (status) {
            case 'chose':
                if (!$el.hasClass('current')) {
                    $el.addClass('current');
                    this.incAnswerNum();
                }
                break;
            default :
        }
    },
    getDom: function () {
        this.dom.part_total = $('#part_total');
        this.dom.answer_num = $('#answer_num');//已答题数
        this.dom.detail = $('#detail_content'); //题
        this.dom.timeRemain = $('#time_remain');
        this.dom.questionCard = $('#question_num'); //答题卡
        this.dom.main = $('#main'); //答题卡
    },
    /**
     * 提交
     * @param params
     * @param action
     * @param cb
     * @constructor
     */
    SubmitAction: function (params, url, cb) {
        unitCall.callJson('post', url, {}, {
            success: function (ret) {
                // console.log(ret);
                // if (ret.code == 200) {
                cb(ret);
                // }
            }
        });
    }
};
