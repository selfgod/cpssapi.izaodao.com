var $ = require('jquery');
var utils = require('./util.js');
var _highLight = 'title_current';
var _currentPage, _totalPage, _preDom, _nextDom, _listDom;
var _preStopClass = 'prevStop';
var _nextStopClass = 'nextStop';
var _type = 'week';
var _lodeId = '#slide2';

module.exports = {
    scheduleId: null,
    initialized: false,
    loadRankPanel: function (type) {
        var self = this;
        var params = {};
        var url = utils.buildURL('learning', 'rankCategory/' + type);
        if (self.scheduleId) {
            params = {
                schedule: self.scheduleId
            };
        }
        utils.callWithLoading('get', url, params, {
            success: function (data) {
                $(_lodeId).html(data);
                self.updateData(type);
            }
        }, {}, _lodeId);
    },
    updatePager: function () {
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
    updateData: function (type) {
        _listDom = $('.studentlist');
        if (_listDom.length > 0) {
            _currentPage = _listDom.data('current');
            _totalPage = _listDom.data('total');
        } else {
            _currentPage = 1;
            _totalPage = 0;
        }
        _preDom = $(_lodeId + ' .prev_zxf');
        _nextDom = $(_lodeId + ' .next_zxf');
        _type = type;
    },
    init: function (scheduleId) {
        if (this.initialized) {
            this.updateData('week');
            this.updatePager();
            return;
        } else {
            this.initialized = true;
        }
        var self = this;
        if (scheduleId) {
            self.scheduleId = scheduleId;
        }
        self.updateData('week');
        self.updatePager();
        var body = $('body');
        //切换周学榜，总学榜
        body.on('click', '#category_switch b', function () {
            var element = $(this);
            _type = element.data('name');
            if (!element.hasClass('current_v5')) {
                element.addClass('current_v5').siblings().removeClass('current_v5');
                if (_type === 'total') {
                    $('#weekly_desc').hide();
                    self.loadRankPanel(_type);
                } else {
                    $('#weekly_desc').show();
                    self.loadRankPanel(_type);
                }
            }
        });
        //翻页
        body.on('click', _lodeId + ' a.prev_zxf,' + _lodeId + ' a.next_zxf', function () {
            var element = $(this), page, params;
            if (element.hasClass(_preStopClass) || element.hasClass(_nextStopClass)) {
                return;
            }
            // rankList
            var url = utils.buildURL('learning', 'rankList');
            if (element.hasClass('prev_zxf')) {
                page = _currentPage - 1;
            } else {
                page = _currentPage + 1;
            }

            params = {
                page: page,
                type: _type
            };
            if (self.scheduleId) {
                params.schedule = self.scheduleId;
            }

            utils.callWithLoading('get', url, params, {
                success: function (data) {
                    _currentPage = page;
                    self.updatePager();
                    _listDom.html(data);
                }
            }, {}, '.studentlist');
        });
    }
};
