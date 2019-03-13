var $ = require('jquery');
var service_tool = require('./template/service_tool.hbs');
module.exports = {
    dom: 'body',
    obj: '.learning_tool',
    init: function (dom, obj) {
        var self = this;
        if (dom) self.dom = dom;
        if (obj) self.obj = obj;
        $(self.dom).on('click', '.learn_tool b', function () {
            if (!$(this).hasClass('current_v5')) {
                $(this).addClass('current_v5').siblings().removeClass('current_v5');
                var data_id = $(this).data('id');
                $(self.dom + ' .class_tool').hide();
                $('#' + data_id).show();
            }
        });
    },
    requestFunc: function () {
        $(this.dom + ' ' + this.obj).html(service_tool({
            learn_guidance: JP_DOMAIN + 'misc.php?mod=faq#id=118&messageid=121',
            group_link: JP_DOMAIN + 'misc.php?mod=faq#id=29&messageid=100',
            course_guide: JP_DOMAIN + 'misc.php?mod=faq#id=118&messageid=120',
            activation_link: '/purchased',
            record_download: JP_DOMAIN + 'misc.php?mod=faq#id=21&messageid=24',
            use_textbook: JP_DOMAIN + 'misc.php?mod=faq#id=28&messageid=45',
            class_software: '//service.izaodao.com/download.do?appId=7b198387d5714e75894500a14ec5ac5e&platform=1',
            app_link: MAIN_DOMAIN + 'T/App/zdwx',
            wsyt_link: MAIN_DOMAIN + 'T/App/wsyt',
            practice_link: JP_DOMAIN + 'main.php/tiku/practice',
            yfk_link: MAIN_DOMAIN + 'T/App/yfk',
            cidao_link: MAIN_DOMAIN + 'T/App/cidao'
        }));
    }
};
