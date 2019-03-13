var $ = require('jquery');
var utils = require('public/util.js');
var learn_base = require('./learn_base');
var _listDom = '.evaluate_list_v521';
var _lodeId = '#my_assess';
var _preStopClass = 'prevStop';
var _nextStopClass = 'nextStop';
var _preDom, _nextDom;
var _type = 'next';
var _currentId, _totalPage, _currentPage = 0;
module.exports = {
    dom: 'body',
    obj: '.learning_report',
    init: function (dom, obj) {
        var self = this;
        if (dom) this.dom = dom;
        if (obj) this.obj = obj;
        self.loadMyGrade();
        $(self.dom).on('click', '.closeTip', function () {
            $('.showTip').hide();
        });
        //切换选项卡
        $(self.dom).on('click', '.learn_report b', function () {
            if (!$(this).hasClass('current_v5')) {
                $(this).addClass('current_v5').siblings().removeClass('current_v5');
                $(self.dom + ' .learn_report_com').hide();
                var id = $(this).data('id');
                if (id === 'my_assess') {
                    _currentPage = 1;
                    _currentId = 0;
                    self.lodeTeacherComment();
                }
                $('#' + id).show();
            }
            self.loadMyGrade();
        });
        //翻页
        $('body').on('click', _lodeId + ' a.prev_zxf,' + _lodeId + ' a.next_zxf', function () {
            var element = $(this);
            if (element.hasClass(_preStopClass) || element.hasClass(_nextStopClass)) {
                return;
            }
            if (element.hasClass('prev_zxf')) {
                _type = 'prev';
                _currentPage--;
            } else {
                _type = 'next';
                _currentPage++;
            }
            self.lodeTeacherComment();
        });
    },
    //更新上下页可选状态
    changePager: function () {
        if (_currentPage === 1) {
            _preDom.addClass(_preStopClass);
        } else {
            _preDom.removeClass(_preStopClass);
        }
        if (_currentPage === _totalPage || _totalPage === 0) {
            _nextDom.addClass(_nextStopClass);
        } else {
            _nextDom.removeClass(_nextStopClass);
        }
    },
    //生成dom
    requestFunc: function () {
        var learn_report = require('./template/learn_report.hbs');
        if (learn_base.info) {
            $(this.dom + ' ' + this.obj).html(learn_report({
                study_score: learn_base.info.study_score,
                study_score_total: learn_base.info.study_score_total,
                score_link: JP_DOMAIN + 'main.php/User/SpacecpProfile/index/pagem/xfjl',
                score_total_link: JP_DOMAIN + 'main.php/User/SpacecpProfile/index/pagem/xfjl',
                how_get_score_link: JP_DOMAIN + 'misc.php?mod=faq#id=29&messageid=99',
                shop_link: JP_DOMAIN + 'index/DuiHuan',
                browseNum: learn_base.info.notBrowseNum,
                commentTotal: learn_base.info.commentCount
            }));
            _totalPage = learn_base.info.commentCount;
            _preDom = $(_lodeId + ' .prev_zxf');
            _nextDom = $(_lodeId + ' .next_zxf');
        }
    },
    //加载教师评价
    lodeTeacherComment: function () {
        var self = this;
        var comment, sliceComment;
        var teacher_comment = require('./template/teacher_comment.hbs');
        if (_totalPage > 0) {
            var url = utils.buildURL('learn', 'teacher_comment');
            utils.callWithLoading('get', url, {id: _currentId, type: _type}, {
                success: function (res) {
                    if (res.code === 200) {
                        _currentId = res.data.id;
                        if (res.data.browse === 1) {
                            var browse = parseInt($('#not_browse').text(), 10);
                            if (browse - 1 > 0) {
                                $('#not_browse').text(browse - 1);
                            } else {
                                $('#not_browse').hide();
                            }
                        }
                        self.changePager();
                        comment = res.data.comment;
                        sliceComment = comment;
                        if (comment.length > 60) {
                            sliceComment = comment.slice(0, 60) + '...';
                        }
                        $(_listDom).html(teacher_comment({
                            teacher_avatar: res.data.teacher_avatar,
                            course: res.data.course,
                            time: res.data.time,
                            comment: comment,
                            sliceComment: sliceComment,
                            teacher_name: res.data.teacher_name
                        }));
                    }
                }
            }, {dataType: 'json', loading: {top: -15}}, _listDom);
        }
    },
    loadMyGrade: function () {
        var japanGradeContent = require('./template/japan_grade.hbs');
        var url = utils.buildURL('learn', 'my_grade');
        utils.callWithLoading('get', url, '', {
            success: function (res) {
                if (res.code === 200) {
                    $('#my_grade').html(japanGradeContent({
                        grade_id: res.data.grade_id,
                        is_pass_max: res.data.is_pass_max
                    }));
                } else {
                    $('#my_grade').html(japanGradeContent({grade_id: 0}));
                }
            }
        });
    }
};
