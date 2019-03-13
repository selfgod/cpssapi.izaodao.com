var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('../exam/unit_call');
require('public/css/loading.css');
module.exports = {
    paperId: 0,
    url: '',
    /**
     * 切换分类后的回调
     */
    cb: {},
    dom: {},
    navFixed: false,
    cate: ['word', 'grammar', 'reading'],
    init: function (url, cb) {
        var self = this;
        this.resultId = $('#result_id').val();
        this.url = url;
        this.cb = cb;
        this.getDom();
        this.fixCate();
        $('.js_cate_tab').on('click', function () {
            var $el = $(this);
            //初始的cate是数据和数据对比 default_cate
            var cate = $el.data('cate');
            if ($el.hasClass('subject_1') && !$el.hasClass('current')) {
                //点击题目部分分类
                $el.removeClass('subject_1').addClass('current').siblings().addClass('subject_1').removeClass('current');
                //同步切换答题卡内容
                $('#question_nums ul li').each(function () {
                    var cur = $(this);
                    if (cur.data('cate') === cate) {
                        cur.addClass('current_small').removeClass('card_1');
                    } else {
                        cur.addClass('card_1').removeClass('current_small');
                    }
                });
                self.load(cate);
            } else if ($el.hasClass('card_1') && !$el.hasClass('current_small')) {
                //点击答题卡部分分类
                $el.removeClass('card_1').addClass('current_small').siblings().addClass('card_1').removeClass('current_small');
                //同步切换题目内容
                $('.js_main').each(function () {
                    var cur = $(this);
                    if (cur.data('cate') === cate) {
                        cur.addClass('current').removeClass('subject_1');
                    } else {
                        cur.addClass('subject_1').removeClass('current');
                    }
                });
                self.load(cate);
            }
        });
        this.loadDefault();
    },
    getDom: function () {
        this.dom.main = $('#detail_content');
        this.dom.card = $('#question_num');
    },
    /**
     * 加载默认分类数据
     */
    loadDefault: function () {
        //默认cate
        var defaultCate = 'word';//$('#default_cate').val();
        this.load(defaultCate);
    },
    /**
     * 加载数据
     * @param cate
     * @param isDefault bool 是否是默认分类
     */
    load: function (cate) {
        var self = this;
        util.showLoading('#detail_content');
        util.showLoading('#question_num', {top: '0'});
        //ajax请求cate下数据
        // console.log(this.url);
        unitCall.callJson('get', this.url, {cate: cate, result_id: this.resultId}, {
            success: function (ret) {
                // var s = JSON.parse(ret.data);
                // console.log(s);
                util.scrollTop();
                self.cb(cate, ret.data);
            }
        });
    },
    /**
     * 窗口上下滚动时固定分类导航和右侧的答题卡
     */
    fixCate: function () {
        $(window).scroll(function () {
            if ($(window).scrollTop() > 100) {
                $('.exam_navi').addClass('posFix');
                $('.contant_right').addClass('posFix rightFix');
            } else {
                $('.exam_navi').removeClass('posFix');
                $('.contant_right').removeClass('posFix rightFix');
            }
        });
    },
    /**
     * 获取下一个分类
     * @param cate
     */
    nextCate: function (cate) {
        var index = this.cate.indexOf(cate);
        if (index === -1) {
            return this.cate[0];
        } else {
            return this.cate[index + 1];
        }
    },
    /**
     * 触发分类按钮点击
     * @param cate
     */
    triggerClick: function (cate) {
        $('#question_nums .card_1').each(function () {
            var $el = $(this);
            if ($el.data('cate') === cate) {
                $el.trigger('click');
            }
        });
    }
};
