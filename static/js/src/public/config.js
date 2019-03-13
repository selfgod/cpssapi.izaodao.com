var $ = require('jquery');
module.exports = {
    masterDomain: null,
    roomInfoLink: 'help.php?op=yy',
    scoreRecordLink: 'main.php/User/SpacecpProfile/index/pagem/xfjl',
    courseLink: 'main.php/Course/Details/',
    orderLink: 'main.php/Order/Order/',
    payLink: 'main.php/Order/Details/info?oid=',
    recordLesson: '',
    yyHelpLink: JP_DOMAIN + 'misc.php?mod=faq#id=118&messageid=121',
    zdTalkHelpLink: JP_DOMAIN + 'misc.php?mod=faq#id=118&messageid=131',
    scoreShopLink: JP_DOMAIN + '/Index/DuiHuan',
    getLink: function (name) {
        if (!this.masterDomain) {
            this.masterDomain = $('#master_domain').val();
        }
        return this.masterDomain + this[name];
    },
    /**
     * 单元测试结果页链接
     * @param paperId
     * @returns {string}
     */
    getExamResultLink: function (paperId) {
        return '/exam/unitTest/result?paper_id=' + paperId;
    },
    /**
     * 单元测试回顾页链接
     * @param paperId
     * @returns {string}
     */
    getExamReviewLink: function (paperId) {
        return '/exam/unitTest/review?paper_id=' + paperId;
    }
};
