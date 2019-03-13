var $ = require('jquery');
var util = require('../../public/util');

module.exports = {
    roomId: 0,
    roomName: '',
    roomPwd: '',
    mode: 1,
    planId: 0,
    planStageId: 0,
    id: 0,
    /**
     * 更新阶段课程基本信息
     */
    update: function () {
        var element = $('#switch_schedule .new_liveClass_current');
        this.roomId = element.data('roomid');
        this.roomName = element.data('roomname');
        this.roomPwd = element.data('roompwd');
        this.mode = parseInt(element.data('mode'), 10);
        this.planId = $('#plan_id').val();
        this.planStageId = $('#plan_stage_id').val();
        this.id = element.data('id');
    },
    /**
     * 学习系统内发送请求方法
     * @param method
     * @param url
     * @param data
     * @param callbacks
     * @param options
     */
    load: function (method, url, data, callbacks, options) {
        util.call(method, url, this.getBaseParam(data), callbacks, options);
    },
    /**
     * 加载详情页面
     * @param url
     * @param params
     * @param callback
     * @param loading
     */
    loadDetail: function (url, params, callback, loading) {
        url = this.buildUrl(url);
        var handler = {
            success: function (data) {
                if (callback) {
                    callback(null, data);
                } else {
                    $('#detail_content').html(data);
                }
            }
        };

        if (loading) {
            this.loadWithLoading('get', url, params, handler, {}, '#detail_content');
        } else {
            this.load('get', url, params, handler);
        }
    },
    /**
     * 带加载loading图标
     */
    loadWithLoading: function (method, url, data, callbacks, options, selector) {
        util.callWithLoading(method, url, this.getBaseParam(data), callbacks, options, selector);
    },
    /**
     * 生成学习系统内url
     * @param action
     * @param params
     * @returns {*}
     */
    buildUrl: function (action, params) {
        return util.buildURL('learningsystem', action, params);
    },
    /**
     * 获取基础参数
     * @param data
     * @returns {*|{}}
     */
    getBaseParam: function (data) {
        var param = data || {};
        param.schedule = this.id;
        param.plan = this.planId;
        param.stage = this.planStageId;
        return param;
    }
};
