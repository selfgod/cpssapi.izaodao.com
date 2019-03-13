var $ = require('jquery');
var util = require('public/util.js');
var unitCall = require('./unit_call');
require('public/css/loading.css');
var jwplayer = require('jwplayer');
module.exports = {
    paperId: 0,
    url: '',
    /**
     * 切换分类后的回调
     */
    cb: {},
    dom: {},
    navFixed: false,
    cate: ['word', 'grammar', 'reading', 'listening'],
    init: function (url, cb) {
        var self = this;
        this.paperId = $('#paper_id').val();
        this.url = url;
        this.cb = cb;
        this.getDom();
        this.fixCate();
        $('.js_cate_tab').on('click', function () {
            var $el = $(this);
            var cate = $el.data('cate');
            if ($el.hasClass('exam_v4_nav1') && !$el.hasClass('exam_v4_nav1_1')) {
                //点击题目部分分类
                $el.removeClass('exam_v4_nav1').addClass('exam_v4_nav1_1').siblings().addClass('exam_v4_nav1').removeClass('exam_v4_nav1_1');
                //同步切换答题卡内容
                $('#question_nums div').each(function () {
                    var cur = $(this);
                    if (cur.data('cate') === cate) {
                        cur.addClass('exam_v4_answer_nav1_1').removeClass('exam_v4_answer_nav1');
                    } else {
                        cur.addClass('exam_v4_answer_nav1').removeClass('exam_v4_answer_nav1_1');
                    }
                });
                self.load(cate);
            } else if ($el.hasClass('exam_v4_answer_nav1') && !$el.hasClass('exam_v4_answer_nav1_1')) {
                //点击答题卡部分分类
                $el.removeClass('exam_v4_answer_nav1').addClass('exam_v4_answer_nav1_1').siblings().addClass('exam_v4_answer_nav1').removeClass('exam_v4_answer_nav1_1');
                //同步切换题目内容
                $('.js_main').each(function () {
                    var cur = $(this);
                    if (cur.data('cate') === cate) {
                        cur.addClass('exam_v4_nav1_1').removeClass('exam_v4_nav1');
                    } else {
                        cur.addClass('exam_v4_nav1').removeClass('exam_v4_nav1_1');
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
        var defaultCate = $('#default_cate').val();
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
        unitCall.callJson('get', this.url, {paper_id: this.paperId, cate: cate}, {
            success: function (ret) {
                if (cate !== 'listening') {
                    $('#exam_v4_sound').hide();//隐藏听力播放器
                } else {
                    $('#exam_v4_sound').show();
                    self.showPlayer(ret.data.media_file, ret.data.poly_id);
                }
                util.scrollTop();
                self.cb(cate, ret.data);
            }
        });
    },
    /**
     * 窗口上下滚动时固定分类导航和右侧的答题卡
     */
    fixCate: function () {
        var self = this;
        $(window).scroll(function () {
            var top = $(window).scrollTop();
            if (!self.navFixed && top > 90) {
                self.navFixed = true;
                $('.exam_v4_navi1').removeClass('pos_r').addClass('pos_f');
                $('.ykyc_v4_con_answer_done').removeClass('pos_r').addClass('pos_f').css('top', '59px');
            } else if (self.navFixed && top <= 90) {
                self.navFixed = false;
                $('.exam_v4_navi1').removeClass('pos_f').addClass('pos_r');
                $('.ykyc_v4_con_answer_done').removeClass('pos_f').addClass('pos_r').css('top', '0px');
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
        $('#question_nums .exam_v4_answer_nav1').each(function () {
            var $el = $(this);
            if ($el.data('cate') === cate) {
                $el.trigger('click');
            }
        });
    },
    /**
     * 显示听力进度条
     */
    showPlayer: function (mediaFile, polyId) {
        if (mediaFile.length > 0) {
            $('#exam_v4_sound').html('<div style="text-align: left;"><div id=\'player_2\'></div></div>');
            jwplayer.key = 'wtMuHL5+u6ED0d7wAvEOJzXgj6m1ZPMBcf6hQw==';
            jwplayer('player_2').setup({
                file: mediaFile,
                width: 325,
                height: 32,
                displayheight: 32,
                autostart: false,
                wmode: 'transparent'
            });
        } else {
            var html = '<object width="312" height="40" id="polyvplayer' + polyId + '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
            html += '<param value="http://player.polyv.net/videos/player.swf" name="movie">';
            html += '<param value="always" name="allowscriptaccess"><param value="transparent" name="wmode">';
            html += '<param value="vid=' + polyId + '" name="flashvars">';
            html += '<param value="false" name="allowFullScreen">';
            html += '<embed width="312" height="40" flashvars="vid=' + polyId + '"';
            html += ' allowfullscreen="true" name="polyvplayer' + polyId + '"';
            html += ' wmode="Transparent" allowscriptaccess="always" type="application/x-shockwave-flash" src="http://player.polyv.net/videos/player.swf"></object>';
            $('#exam_v4_sound').html(html);
        }
    }
};
