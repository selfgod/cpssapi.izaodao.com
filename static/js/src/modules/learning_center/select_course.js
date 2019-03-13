require('./css/cpss_major_v5.css');
require('./css/cpss_themes_v5.css');
require('./css/cpss_course_v5.css');
require('./css/radio_button_v5.css');
require('./css/evaluation_v5.css');
var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var switch_curricular = require('./switch_curricular');
var select_major = require('./select_major');
var select_reservation = require('./select_reservation');
var select_schedule = require('./select_schedule');
var oral_assessment = require('./oral_assessment');
var new_guide_tip = require('./new_guide_tip');
module.exports = {
    init: function () {
        select_major.init();
        select_reservation.init();
        select_schedule.init();
        oral_assessment.init();
    },
    initMainDom: function () {
        $('.selectCourseClass').addClass('active_v5').siblings('.learn_nav').removeClass('active_v5');
        var switch_curricular_html = require('./template/switch_curricular.tpl');
        $(_options.learnMianClass).html(switch_curricular_html({
            option_class: 'cpss_select_curricular',
            major_link: '/study/#/chooseClass?activeTab=main&activeModule=main',
            oral_link: '/study/#/learnHome',
            elective_link: '/study/#/chooseClass?activeTab=elective&activeModule=elective',
            special_link: '/study/#/chooseClass?activeTab=special&activeModule=special',
            custom_link: '/study/#/learnHome'
        }));
    },
    handleFunc: function (curricular, args) {
        var self = this;
        new_guide_tip._chooseClassGuideClose();
        utils.scrollTop();
        if (_.indexOf(_options.curricularMap, curricular) !== -1) {
            $(_options.learnMianClass).attr('ga-location', $('.selectCourseClass').data('ga_name') + '_' + _options.curricularZhMap[curricular]);
            self.initMainDom();
            $('#cpss_' + curricular).addClass('off').removeClass('link').siblings().addClass('link').removeClass('off');
            if (curricular === 'major') {
                if (args && args.plan > 0 && args.stage > 0) {
                    select_schedule.scheduleMian(args.plan, args.stage);
                } else {
                    select_major.requestFunc();
                }
            } else if (curricular === 'oral' && args && args.lesson > 0) {
                oral_assessment.requestFunc(args.lesson);
            } else {
                select_reservation.requestFunc(curricular);
            }
        }
    }
};
