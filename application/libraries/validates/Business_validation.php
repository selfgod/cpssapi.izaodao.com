<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Base_val.php';

class Business_validation extends Base_val
{
    public function __construct()
    {
        parent::__construct();
        $this->ci->load->model('learning_system/learning_task_model');
        $this->ci->load->model('public/member_model');
        $this->ci->load->model('public/schedule_model');
        $this->ci->load->model('public/goods_model');
        $this->ci->load->model('public/plan_model');
        $this->ci->load->model('public/business_validation_model');
    }

    public function validationTasks()
    {
        return array(
            'get_schedule' => array(
                'validateIsJoined',
                'validatePlanStageMatch',
                'validatePlanStageExpire',
                'validatePlanExpire'
            ),
            'major_check_in' => array(
                'validateCheckedIn',
                'validateIsJoined',
//                'validatePlanStageMatch',
//                'validatePlanStageExpire',
//                'validatePlanExpire'
            ),
            'check_in' => array(
                'validateCheckedIn',
                'validateReserve'
            ),
            'exercise_complete' => array(
                'validateExercise',
                'validateIsJoined',
                //'validatePlanStageMatch',
                //'validatePlanStageExpire',
                //'validatePlanExpire'
            ),
            'zdt_unit_test' => array(
                'validateZdtTime',
                'validateZdtMaxNum'
            ),
            'join_plan' => array(
                'validateUserLevel',
                'validateCanJoinPlan'
            ),
            'planAndStage' => array(
                'validatePlan',
                'validatePlanStage'
            ),
            'planStageLock' => array(
                'validatePlan',
                'validatePlanStageLock'
            ),
            'join_schedule' => array(
                'validateNoJoined',
                'validatePlanStageMatch',
                'validatePlanStage',
                'validatePlan'
            ),
            'validate_major' => array(
                'validateMajorApply',
                'validateMajorJoinPlan'
            ),
            'major_download' => array(
                'validateIsJoined',
                'validatePlanStageMatch',
                'validatePlanStageExpire',
                'validatePlanExpire'
            ),
            'upgrade' => array(
                'validateUserSuspend',
                'validateGoodsActivate'
            ),
            'accounting' => array(
                'validateUserSuspend',
                'validateGoodsActivate',
                'validateIsUpgrading'
            ),
            'intention' => array(
                'validateUserSuspend',
                'validateGoodsActivate',
                'validatePurchased',
                'validateUpgradeRules'
            ),
            'suspend' => array(
                'validateUserSuspend',
            ),
            'activate' => array(
                'validateUserSuspend',
            ),
            'is_join_schedule' => array(
                'validateIsJoined'
            ),
            'goods_type_mode' => array(
                'validateGoodsTypeMode'
            ),
            'my_schedule' => array(
                'validateScheduleExpire'
            ),
            'learn_details' => array(
                //'validateScheduleExpire',
                'validateUserSuspend'
            )
        );
    }

    /**
     * 验证加入的阶段课程是否过期
     */
    public function validateScheduleExpire()
    {
        $uid = $this->data['uid'];
        $scheduleId = $this->data['schedule_id'];
        $joinSchedule = $this->ci->schedule_model->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            'schedule_id' => $scheduleId,
            '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')",
        ], [], [], [1]);
        if (!empty($joinSchedule)) {
            return 200;
        }
        return 221;
    }

    /**
     * 验证用户是否已经购买过该商品
     * @return int
     */
    public function validatePurchased()
    {
        $uid = $this->data['uid'];
        $goodsId = $this->data['targetGoods'];
        $purchased = $this->ci->goods_model->userPurchasedGoods($uid);
        if (!in_array($goodsId, $purchased)) {
            return 200;
        } else {
            return 266;
        }
    }

    /**
     * 验证是否已经选择升级方案
     * @return int
     */
    public function validateIsUpgrading()
    {
        $userGoods = $this->data['goods'];
        if (!empty($userGoods) && !empty($userGoods['upgrade_intention'])) {
            return 200;
        } else {
            return 265;
        }
    }

    /**
     * 验证商品是否符合升级规则
     * 新商品优惠价高于原商品成交价
     * 符合可升级商品分类规则
     * 规定日期后，有可查订单的商品(暂定2016/9/6 零点)
     */
    public function validateUpgradeRules()
    {
        $originGoods = $this->data['originGoods'];
        $targetGoods = $this->data['targetGoods'];
        $uid = $this->data['uid'];

        if (empty($originGoods) || empty($targetGoods)) {
            return 234;
        }
        if ($originGoods['upgradeable'] == '0' || $targetGoods['upgradeable'] == '0') {
            return 264;
        }
        if (intval($originGoods['scrap_value']) >= intval($targetGoods['discount_price'])) {
            return 264;
        }
        if (empty($originGoods['upgrade_cat']) || empty($targetGoods['upgrade_cat'])) {
            return 264;
        }
        if ($this->ci->member_model->hasValidOrder($uid) === FALSE) {
            return 264;
        }
        $this->ci->load->model('public/common_model');
        $upgradeCateKey = get_options('sys_category', 'upgrade');
        $tiers = $this->ci->common_model->getCategoryTier($upgradeCateKey);
        if ($originGoods['upgrade_cat'] === $targetGoods['upgrade_cat'] ||
            $tiers[$originGoods['upgrade_cat']]['tier'] < $tiers[$targetGoods['upgrade_cat']]['tier']
        ) {
            return 200;
        } else {
            return 264;
        }
    }

    /**
     * 验证商品已激活未过期
     */
    public function validateGoodsActivate()
    {
        $goods = $this->data['goods'];
        if (!empty($goods)) {
            $now = time();
            $isActivate = $goods['is_activate'] === '1';
            $isUnExpire = $goods['unlimit_expire'] === '1' || strtotime($goods['expire']) >= $now;
            if ($isActivate && $isUnExpire) {
                return 200;
            } else {
                return 263;
            }
        } else {
            return 234;
        }
    }

    /**
     * 验证用户是否正在休学
     * @return int
     */
    public function validateUserSuspend()
    {
        $uid = $this->data['uid'];
        $this->ci->load->model('teaching_center/leave_model');
        if ($this->ci->leave_model->isSuspending($uid)) {
            return 262;
        } else {
            return 200;
        }
    }

    /**
     * 判断商品是否有效
     * @param $goods
     * @param $current
     * @return bool
     */
    public function invalidGoods($goods, $current)
    {
        $expire = strtotime($goods['expire']);
        if ((intval($goods['remain_count']) > 0 || intval($goods['unlimit_count']) === 1) &&
            intval($goods['unlimit_expire']) === 1 || $expire > $current
        ) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 验证用户之前是否已经完成一课一练或单元测试
     * 按时完成类型的单元测试，验证当前时间是否是开课半小时之后
     * @return int
     */
    public function validateExercise()
    {
        //practice or unit
        $type = $this->data['type'];
        $uid = $this->data['uid'];
        $exercise_id = $this->data['exercise_id'];
        $schedule_lesson_id = $this->data['schedule_lesson_id'];
        $ret = $this->ci->business_validation_model->validateExerciseCompleted($uid, $exercise_id, $type);
        if ($ret) {
            return 257;
        }
        $scheduleLesson = $this->ci->learning_task_model->getScheduleLessonInfo($schedule_lesson_id);
        if ($scheduleLesson) {
            if (($type === 'practice' && intval($scheduleLesson['test_id']) !== $exercise_id) ||
                ($type === 'unit' && intval($scheduleLesson['unit_id']) !== $exercise_id)
            ) {
                return 261;
            }
        } else {
            return 500;
        }

        if ($type === 'unit') {
            //按时完成类型的单元测试，验证完成时间
            $this->ci->load->model('learning_system/exercise_model');
            if ($this->ci->exercise_model->isOnTimeUnitTest($exercise_id)) {
                if ($scheduleLesson) {
                    $current = time();
                    $startTime = strtotime($scheduleLesson['start_time']);
                    if ($current < $startTime - 1800) {
                        return 258;
                    }
                } else {
                    return 260;
                }
            }
        }
        return 200;
    }

    /**
     * 验证早道田考场开考时间
     */
    public function validateZdtTime()
    {
        $paper = $this->data['paper'];
        if (intval($paper['exam_type']) === 1 && !empty($paper['open_date_start'])) {
            $now = date('Y/m/d');
            $startTime = date('Y-m-d') . ' ' . $paper['open_time_start'];
            $endTime = date('Y-m-d') . ' ' . $paper['open_time_end'];
            $nowsec = time();
            if ($nowsec > strtotime($paper['open_date_end'] . ' ' . $paper['open_time_end'])) {
                return 200;
            } elseif ($now >= $paper['open_date_start'] && $now <= $paper['open_date_end']) {
                if ($nowsec >= strtotime($startTime) && $nowsec <= strtotime($endTime)) {
                    return 200;
                }
            }
            return 500;
        } else {
            return 200;
        }
    }

    /**
     * 验证早道田考场人数限制
     */
    public function validateZdtMaxNum()
    {
        $paper = $this->data['paper'];
        $uid = $this->data['uid'];
        if (intval($paper['max_num']) > 0) {
            if ($this->ci->business_validation_model->isExaming($paper['id'], $uid)) {
                return 200;
            }
            if ($this->ci->business_validation_model->isOverZdtMaxNum($paper['id'], $uid)) {
                return 300;
            }
        }
        return 200;
    }

    /**
     * 验证用户之前是否已报到
     * 或当前报到时间是否是下课前半小时之后
     * @return int
     */
    public function validateCheckedIn()
    {
        $uid = $this->data['uid'];
        $schedule_lesson_id = $this->data['schedule_lesson_id'];
        $ret = $this->ci->business_validation_model->validateCheckedIn($uid, $schedule_lesson_id);
        if ($ret) {
            return 255;
        }
        $scheduleLesson = $this->ci->learning_task_model->getScheduleLessonInfo($schedule_lesson_id);
        if ($scheduleLesson) {
            $current = time();
            $endTime = strtotime($scheduleLesson['end_time']);
            if ($current < $endTime - 1800) {
                return 256;
            }
        } else {
            return 500;
        }

        return 200;
    }

    /**
     * 验证用户是否加入阶段课程
     * @return bool
     */
    public function validateIsJoined()
    {
        $uid = $this->data['uid'];
        $schedule_id = $this->data['schedule_id'];
        $ret = $this->ci->business_validation_model->validateUserJoinedSchedule($uid, $schedule_id);
        if ($ret) {
            return 200;
        } else {
            return 250;
        }
    }

    /**
     * 验证用户是否可加入阶段课程
     * @return bool
     */
    public function validateNoJoined()
    {
        $uid = $this->data['uid'];
        $schedule_id = $this->data['schedule_id'];
        $ret = $this->ci->business_validation_model->validateUserNoJoinedSchedule($uid, $schedule_id);
        if ($ret) {
            return 200;
        } else {
            return 210;
        }
    }

    /**
     * 验证计划，计划阶段，阶段课程是否匹配
     * @return bool
     */
    public function validatePlanStageMatch()
    {
        $plan_id = $this->data['plan_id'];
        $plan_stage_id = $this->data['plan_stage_id'];
        $schedule_id = $this->data['schedule_id'];

        $ret = $this->ci->business_validation_model->validatePlanStageMatch($plan_id, $plan_stage_id, $schedule_id);
        if ($ret) {
            return 200;
        } else {
            return 251;
        }
    }

    /**
     * 验证计划阶段是否解锁
     * @return mixed
     */
    public function validatePlanStage()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $plan_stage_id = $this->data['plan_stage_id'];

        $ret = $this->ci->business_validation_model->validatePlanStage($uid, $plan_id, $plan_stage_id);
        if ($ret) {
            return 200;
        } else {
            return 252;
        }
    }

    /**
     * 验证计划阶段有效期
     * @return mixed
     */
    public function validatePlanStageExpire()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $plan_stage_id = $this->data['plan_stage_id'];
        $ret = $this->ci->business_validation_model->validatePlanStageExpire($uid, $plan_id, $plan_stage_id);
        if ($ret) {
            return 200;
        } else {
            return 252;
        }
    }

    /**
     * 是否可解锁
     * @return mixed
     */
    public function validatePlanStageLock()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $plan_stage_id = $this->data['plan_stage_id'];

        $ret = $this->ci->business_validation_model->validatePlanStage($uid, $plan_id, $plan_stage_id, TRUE);
        if ($ret) {
            return 200;
        } else {
            return 209;
        }
    }

    /**
     * 验证是否预约
     */
    public function validateReserve()
    {
        $uid = $this->data['uid'];
        $lessonId = $this->data['schedule_lesson_id'];
        if ($this->ci->business_validation_model->validateReserved($uid, $lessonId) === TRUE) {
            return 200;
        } else {
            return 223;
        }
    }

    /**
     * 验证计划是否加入
     * @return mixed
     */
    public function validatePlan()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $ret = $this->ci->business_validation_model->validatePlan($uid, $plan_id);
        if ($ret) {
            return 200;
        } else {
            return 253;
        }
    }

    /**
     * 验证计划有效期
     * @return mixed
     */
    public function validatePlanExpire()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $ret = $this->ci->business_validation_model->validatePlanExpire($uid, $plan_id);
        if ($ret) {
            return 200;
        } else {
            return 253;
        }
    }

    /**
     * 验证计划有效期, 是否可加入
     * @return mixed
     */
    public function validateCanJoinPlan()
    {
        $uid = $this->data['uid'];
        $plan_id = $this->data['plan_id'];
        $ret = $this->ci->business_validation_model->validatePlan($uid, $plan_id, 0);
        if ($ret) {
            return 200;
        } else {
            return 206;
        }
    }

    /**
     * 验证是否存在激活的商品
     * @return int
     */
    public function validateActivateGoods()
    {
        $uid = $this->data['uid'];
        if ($this->ci->business_validation_model->existActivateGoods($uid)) {
            return 200;
        }
        return 205;
    }

    /**
     * 验证是否存在未激活的单品课
     * @return int
     */
    public function validateUnactivateItem()
    {
        $uid = $this->data['uid'];
        if ($this->ci->business_validation_model->existUnactivateItem($uid)) {
            return 200;
        }
        return 226;
    }

    /**
     * 验证是否有 VIP或SVIP权限
     * @return int
     */
    public function validateUserLevel()
    {
        $uid = $this->data['uid'];
        $message = $this->ci->member_model->getUserMessage($uid);
        if (!empty($message) && intval($message['user_level']) > 1) {
            return 200;
        }
        return 225;
    }

    /**
     * 验证主修课程体系是否报名
     * @return int
     */
    public function validateMajorApply()
    {
        $uid = $this->data['uid'];
        if ($this->ci->business_validation_model->existClassModel($uid, 1)) {
            return 200;
        }
        return 202;
    }

    /**
     * 是否存在已加入的计划
     * @return int
     */
    public function validateMajorJoinPlan()
    {
        $uid = $this->data['uid'];
        if ($this->ci->business_validation_model->existJoinPlan($uid)) {
            return 200;
        }
        return 203;
    }

    /**
     * 验证此课程体系商品是否存在
     * @return int
     */
    public function validateGoodsTypeMode()
    {
        $uid = $this->data['uid'];
        $type = $this->data['type'];
        if ($this->ci->business_validation_model->existGoodsTypeMode($uid, $type)) {
            return 200;
        }
        return 202;
    }
}
