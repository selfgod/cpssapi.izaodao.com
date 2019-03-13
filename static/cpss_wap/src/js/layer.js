var $ = require('jquery');

module.exports = {
    show: function (content) {
        $('#popup').html(content);
        $('#popup').show();
        $('.wap_cpss_popup_bg').show();
    },
    hide: function () {
        $('.wap_cpss_popup_bg').hide();
        $('#popup').hide();
    },
    show2: function (content) {
        $('#popup2').html(content);
        $('#popup2').show();
        $('.wap_cpss_popup_bg2').show();
    },
    hide2: function () {
        $('.wap_cpss_popup_bg2').hide();
        $('#popup2').hide();
    }
};
