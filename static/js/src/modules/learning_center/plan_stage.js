var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var router = require('public/router');
var _options = require('./options');
var select_schedule = require('./select_schedule');
var learning_popup = require('./learning_popup');
module.exports = {
    selectCourseMajorMain: function () {
        var url = utils.buildURL('learn', 'select_course_major');
        utils.callWithLoading('get', url, {}, {
            success: function (res) {
                if (res) $(_options.learnMianClass + ' .cpss_layout').html(res);
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' .cpss_layout');
    },
    selectCourseSwitchPlan: function (plan_id) {
        var url = utils.buildURL('learn', 'select_course_switch_plan');
        utils.callWithLoading('get', url, {plan: plan_id}, {
            success: function (res) {
                if (res) $(_options.learnMianClass + ' #switch_plan_stage').html(res);
            }
        }, {dataType: 'html'}, _options.learnMianClass + ' #switch_plan_stage');
    },
    selectPlan: function (plan_id) {
        var url = utils.buildURL('learn', 'select_plan');
        utils.callWithLoading('post', url, {plan: plan_id}, {
            success: function (res) {
                if (res.code === 200) {
                    router.goHashUrl('selectCourse/major');
                }
            }
        }, {dataType: 'json'});
    },
    unLockPlanStage: function (plan_id, plan_stage_id, show_popup) {
        var self = this;
        var url = utils.buildURL('learn', 'unlock_plan_stage');
        utils.call('post', url, {
            plan: plan_id,
            stage: plan_stage_id,
            show_popup: show_popup
        }, {
            success: function (obj) {
                if (obj.code === 215) {
                    learning_popup.unlockBeforePrompt(_.bind(self.unLockPlanStage, self, plan_id, plan_stage_id, 0));
                } else if (obj.code === 200) {
                    learning_popup.unlockSuccess(plan_id, plan_stage_id, _.bind(self.selectCourseMajorMain, self));
                } else {
                    learning_popup.operationFail();
                }
            }
        }, {dataType: 'json'});
    },
    unlockedPlanStage: function (plan_id, plan_stage_id) {
        var url = utils.buildURL('learn', 'click_unlock_plan_stage');
        utils.call('post', url, {plan: plan_id, stage: plan_stage_id}, {
            success: function (obj) {
                if (obj.code === 200) {
                    router.goHashUrl(obj.links);
                } else {
                    learning_popup.operationFail();
                }
            }
        }, {dataType: 'json'});
    }
};
