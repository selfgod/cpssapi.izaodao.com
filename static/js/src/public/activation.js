var _ = require('lodash');
var layerPop = require('./layer');
var popup = require('./popup');
var commonPrompt = require('public/template/common_prompt.hbs');

module.exports = {
    notAllowActivateTimePopup: function (allow_activate_time) {
        layerPop.show(commonPrompt({
            title: '不在激活时间范围内',
            sub_title: '请在' + allow_activate_time + '起激活'
        }), {
            btn: ['知道了']
        });
    }
};
