var $ = require('jquery');
var _ = require('lodash');
var utils = require('./util');
var layerPop = require('./layer');
var reasonHbs = require('./template/del_schedule_reason.hbs');
var _showPopup = function (data, callback) {
    layerPop.layer.msg(data.msg, {
        time: 1000
    }, function () {
        callback(data);
    });
};
var _submit = function (data, callback) {
    data = data || {};
    var params;
    var url = utils.buildURL('learningsystem', 'schedule/delete');
    params = {
        nextId: 0,
        del: data.id,
        reason: data.reason
    };
    if (data.joinSchedule) {
        params.joinSchedule = 1;
    }
    utils.call('post', url, params, {
        success: function (response) {
            if (response.code === 200) {
                response.data.msg = '删除成功！';
            } else {
                response.data.msg = '删除失败！';
            }
            _showPopup(response.data, callback);
        }
    }, {dataType: 'json'});
};
var _bind = function () {
    var self = this;
    var $body = $('body');
    var result = {};
    //点击删除理由提交按钮
    $body.on('click', '.del_schedule_submit_reason', function () {
        var reason = $('.del_reason').val() || '';
        $('.reason_prompt').text('');
        if (reason.length < 10) {
            // language=JQuery-CSS
            $('.reason_prompt').text('删除理由不足10个字，无法提交');
            return false;
        }
        layerPop.layer.close(self.layerIndex);
        result.reason = reason;
        _submit(_.assign(self.params, result), self.callback);
    });
};
module.exports = {
    isInit: false,
    params: {},
    layerIndex: {},
    callback: null,
    show: function (params, callback) {
        this.params = params || {};
        this.callback = callback;
        if (!this.isInit) {
            this.isInit = true;
            _.bind(_bind, this)();
        }
        this.layerIndex = layerPop.show(reasonHbs(), {
            area: ['460px', '280px']
        });
    }
};
