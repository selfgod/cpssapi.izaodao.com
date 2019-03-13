var $ = require('jquery');
var utils = require('public/util.js');
module.exports = {
    info: {},
    init: function () {
        this.learnInfoFunc();
    },
    learnInfoFunc: function () {
        var self = this;
        var url = utils.buildURL('api/user/learn_info');
        utils.call('get', url, {}, {
            success: function (obj) {
                if (obj.code === 200) self.info = obj.data;
            }
        }, {dataType: 'json', async: false});
    }
};
