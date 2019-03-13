var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
module.exports = {
    dom: 'body',
    obj: '.adslot_banner',
    init: function (dom, obj) {
        if (dom) this.dom = dom;
        if (obj) this.obj = obj;
    },
    requestFunc: function () {
        var self = this;
        var url = utils.buildURL('learn', 'adslot_banner');
        utils.call('get', url, {}, {
            success: function (res) {
                if (res) {
                    $(self.dom + ' ' + self.obj).html(res);
                }
            }
        });
    }
};
