var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var _liveScheduleId = '#live_schedule_detial';
var _liveScheduleMain = function (date) {
    var url = utils.buildURL('learn', 'live_schedule');
    var params = {};
    if (date) {
        params.date = date;
    }
    utils.callWithLoading('get', url, params, {
        success: function (res) {
            if (res) {
                $(_options.learnMianClass + ' .lc_left_main .layout_w700_v5').html(res);
            }
        }
    }, {dataType: 'html', loading: {top: -270}}, _options.learnMianClass + ' .lc_left_main .layout_w700_v5');
};
module.exports = {
    init: function () {
        this.bindDom();
    },
    bindDom: function () {
        var self = this;
    },
    liveScheduleMain: function (date) {
        _liveScheduleMain(date);
    },
    liveScheduleDay: function (date) {
        var self = this;
        var url = utils.buildURL('learn', 'live_schedule_day');
        utils.callWithLoading('get', url, {date: date}, {
            success: function (res) {
                if (res) {
                    $(_options.learnMianClass + ' ' + _liveScheduleId).html(res);
                }
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' ' + _liveScheduleId);
    }
};
