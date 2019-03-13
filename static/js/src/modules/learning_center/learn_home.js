require('./css/schedule_v5_5.1.0.css');
require('./css/tool_v5_5.1.0.css');
require('./css/rank_list_w240.css');
require('./css/calendar_v5_5.1.0.css');
var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var learn_base = require('./learn_base');
var live_schedule = require('./live_schedule');
var learning_tool = require('./learning_tool');
var learning_report = require('./learning_report');
//var select_course_tip = require('./select_course_tip');
var new_guide_tip = require('./new_guide_tip');
module.exports = {
    init: function () {
        live_schedule.init();
        learning_tool.init(_options.learnMianClass, '.learning_tool');
        learning_report.init(_options.learnMianClass, '.learning_report');
        //select_course_tip.init();
    },
    initMainDom: function () {
        $('.learnHomeClass').addClass('active_v5').siblings('.learn_nav').removeClass('active_v5');
        var learn_home_html = require('./template/learn_home.tpl');
        learning_report.loadMyGrade();
        $(_options.learnMianClass).html(learn_home_html());
    },
    requestFunc: function () {
        utils.scrollTop();
        $(_options.learnMianClass).attr('ga-location', $('.learnHomeClass').data('ga_name'));
        this.initMainDom();
        //主体内容
        new_guide_tip._chooseClassGuide();
        live_schedule.requestFunc();
        //学习工具
        learning_tool.requestFunc();
        if (learn_base.info.major.hasGoods ||
            learn_base.info.oral.hasGoods ||
            learn_base.info.elective.hasGoods ||
            learn_base.info.special.hasGoods ||
            learn_base.info.custom.hasGoods) {
            //学习报告
            learning_report.requestFunc();
            //购买商品后选课提示层
            //if (!learn_base.info.suspend) select_course_tip.requestFunc();
            //排行榜
            this.rankListData();
        }
        $('.layout_w700_v5').addClass('h1093 mb00');
    },
    rankListData: function () {
        var url = utils.buildURL('learning', 'rank');
        var rankList = require('public/rank_list');
        utils.call('get', url, {action: 'learn'}, {
            success: function (res) {
                $(_options.learnMianClass + ' .report_rank').html(res);
                rankList.init();
            }
        }, {dataType: 'html'});
    }

};
