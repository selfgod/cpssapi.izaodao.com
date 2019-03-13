var utils = require('./util');
var popup = require('./popup');
module.exports = {
    scoreLimit: null,
    /**
     * 是否到达每日得分上限
     * @param callback
     */
    isOverLimit: function (callback) {
        var self = this;
        if (this.scoreLimit === null) {
            var url = utils.buildURL('api', 'user/info');
            utils.callJson('get', url, null, {
                success: function (ret) {
                    self.scoreLimit = ret.data.score_limit;
                    if (!self.scoreLimit) {
                        callback();
                    } else {
                        popup.scoreLimit(callback);
                    }
                }
            }, {async: false});
        } else if (!this.scoreLimit) {
            callback();
        } else {
            popup.scoreLimit(callback);
        }
    }
};
