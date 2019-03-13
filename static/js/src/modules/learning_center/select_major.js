var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _config = require('./options');
var plan_stage = require('./plan_stage');
var learning_popup = require('./learning_popup');
module.exports = {
    init: function () {
        this.bindDom();
    },
    bindDom: function () {
        var self = this;
        $(_config.learnMianClass).on('click', '.switch_plan p', function () {
            if (!$(this).hasClass('current_teb_v5')) {
                $(this).addClass('current_teb_v5').siblings().removeClass('current_teb_v5');
                plan_stage.selectCourseSwitchPlan($(this).attr('data-id'));
            }
        });
        $(_config.learnMianClass).on('click', '.select_plan', function () {
            var plan_id = $(this).data('id');
            plan_stage.selectPlan(plan_id);
        });
        //可解锁的阶段事件
        $(_config.learnMianClass).on('click', '.cpss_lock_open', function () {
            var plan_id = parseInt($(_config.learnMianClass + ' .current_plan_id').data('id'), 10);
            var plan_stage_id = parseInt($(this).parent('li').data('id'), 10);
            plan_stage.unLockPlanStage(plan_id, plan_stage_id, 1);
        });
        //不可解锁的阶段事件
        $(_config.learnMianClass).on('click', '.plan_stage_lock_able', function () {
            var plan_name = $(_config.learnMianClass + ' .switch_plan .current_teb_v5').text();
            learning_popup.cannotUnlockPrompt(plan_name);
        });
        //已解锁的阶段事件
        $(_config.learnMianClass).on('click', '.plan_stage_unlocked', function () {
            var plan_id = parseInt($(_config.learnMianClass + ' .current_plan_id').data('id'), 10);
            var plan_stage_id = parseInt($(this).parent('li').attr('data-id'), 10);
            plan_stage.unlockedPlanStage(plan_id, plan_stage_id);
        });
        //
        $(_config.learnMianClass).on('click', '.current_plan', function () {
            var plan_id = $(this).data('id');
            plan_stage.selectPlan(plan_id);
        });
    },
    requestFunc: function () {
        $(_config.learnMianClass + ' .cpss_layout').html('');
        plan_stage.selectCourseMajorMain();
    }
};
