<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_stage_model extends MY_Model
{
    /**
     * 获取用户计划下的阶段信息
     * @param $uid
     * @param $plan_id
     * @return mixed
     */
    public function getPlanStageInfo($uid, $plan_id)
    {
        return parent::$main_db
            ->select('plan_stage_id,expire,unlimit_expire,select_schedule_id,is_unlocked,unlock_able,order')
            ->from('sty_user_plan_stage')
            ->where(
                array(
                    'uid' => $uid,
                    'plan_id' => $plan_id,
                    'is_del' => 0
                )
            )
            ->order_by('order', 'ASC')
            ->get()->result_array();
    }

    /**
     * 用户开通某阶段信息
     * @param $uid
     * @param $plan_stage_id
     * @return mixed
     */
    public function getUserStageInfo($uid, $plan_id, $plan_stage_id)
    {
        return parent::$main_db
            ->select('plan_id,plan_stage_id,expire,unlimit_expire,select_schedule_id,is_unlocked,unlock_able,order,is_join_schedule')
            ->from('sty_user_plan_stage')
            ->where(
                array(
                    'uid' => $uid,
                    'plan_id' => $plan_id,
                    'plan_stage_id' => $plan_stage_id,
                    'is_del' => 0
                )
            )
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 查询阶段信息
     * @param $plan_stage_id
     * @return mixed
     */
    public function getStageInfo($plan_stage_id)
    {
        return parent::$main_db->select('ss.name')
            ->from('sty_stage AS ss')
            ->join('sty_plan_stage AS sps', 'sps.stage_id=ss.id', 'left')
            ->where(
                array(
                    'sps.id' => $plan_stage_id,
                    'sps.is_del' => 0,
                    'ss.is_del' => 0
                )
            )
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 通过ids 获取计划阶段信息
     * @param array $plan_stage_ids
     * @return array
     */
    public function getIdsPlanStageInfo($plan_stage_ids = array())
    {
        $plan_stage = array();
        if (!empty($plan_stage_ids)) {
            $query = parent::$main_db
                ->select('id,stage_id,level,order')
                ->from('sty_plan_stage')
                ->where_in('id', $plan_stage_ids)
                ->where('is_del', 0)
                ->get()->result_array();
            if (!empty($query)) {
                $plan_stage = $query;
            }
        }
        return $plan_stage;
    }

    /**
     * 根据ids获取阶段相关信息
     * @param array $stage_ids
     * @return array
     */
    public function getIdsStageInfo($stage_ids = array())
    {
        $stage_info = array();
        if (!empty($stage_ids)) {
            $query = parent::$main_db
                ->select('id,name,icon,icon_off')
                ->from('sty_stage')
                ->where_in('id', $stage_ids)
                ->get()->result_array();
            if (!empty($query)) {
                $stage_info = $query;
            }
        }
        return $stage_info;
    }

    /**
     * 获取当前计划阶段前的阶段信息
     * @param $uid
     * @param $plan_id
     * @param $order
     * @return mixed
     */
    public function getBeforOrderPlanStageInfo($uid, $plan_id, $order)
    {
        $order = intval($order) > 0 ? intval($order) : 0;
        return parent::$main_db->select('plan_stage_id,order,is_unlocked')
            ->from('sty_user_plan_stage')
            ->where(
                array(
                    'uid' => $uid,
                    'plan_id' => $plan_id,
                    'unlock_able' => 1,
                    'is_del' => 0
                )
            )
            ->where('order<', $order)
            ->limit(1)
            ->order_by('order', 'DESC')
            ->get()->row_array();
    }

    /**
     * 获取当前计划阶段的下一个阶段信息
     * @param $uid
     * @param $planId
     * @param int $order 当前阶段排序
     */
    public function getNextPlanStage($uid, $planId, $order)
    {
        $order = intval($order) > 0 ? intval($order) : 0;
        return parent::$main_db->select('id, plan_stage_id, order, is_unlocked, unlock_able')
            ->from('sty_user_plan_stage')
            ->where([
                'uid' => $uid,
                'plan_id' => $planId,
                'is_del' => 0,
                'order >' => $order
            ])
            ->limit(1)
            ->order_by('order', 'ASC')
            ->get()->row_array();
    }

    /**
     * 解锁计划阶段
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @return mixed
     */
    public function unLockUserPlanStage($uid, $plan_id, $plan_stage_id)
    {
        return parent::$main_db->update('sty_user_plan_stage', array(
            'is_unlocked' => 1,
            'modify_time' => date('Y-m-d H:i:s')
        ), array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id,
            'unlock_able' => 1,
            'is_unlocked' => 0,
            'is_del' => 0
        ));
    }

    /**
     * 解锁计划阶段
     * @param $id
     * @return mixed
     */
    public function unlockUserPlanStageById($id)
    {
        return parent::$main_db->where('id', $id)
            ->update('sty_user_plan_stage', ['is_unlocked' => 1]);
    }

    /**
     * 更新用户在当前计划阶段下选择的阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $select_schedule_id
     * @return mixed
     */
    public function updateUserPlanStageSelectSchedule($uid, $plan_id, $plan_stage_id, $select_schedule_id, $join_schedule = FALSE)
    {
        $data['select_schedule_id'] = $select_schedule_id;
        $data['modify_time'] = date('Y-m-d H:i:s');
        if ($join_schedule) {
            $data['is_join_schedule'] = 1;
        }
        return parent::$main_db->update('sty_user_plan_stage', $data, array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id,
            'is_del' => 0
        ));
    }

    /**
     * 更新计划阶段
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $data
     * @return mixed
     */
    public function updateUserPlanStage($uid, $plan_id, $plan_stage_id, $data)
    {
        return parent::$main_db->update('sty_user_plan_stage', $data, array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id,
            'is_del' => 0
        ));
    }

    /**
     * 如果当前阶段下出勤率达到80%自动解锁下一计划阶段
     * 只要有其中一个阶段课程的总出勤率达到80%，就算。
     * @param $uid
     * @param $planId
     * @param $planStageId
     * @return bool|mixed
     */
    public function autoUnlockStage($uid, $planId, $planStageId)
    {
        $planStages = $this->getIdsPlanStageInfo([$planStageId]);
        $order = 0;
        if (!empty($planStages)) {
            $order = $planStages[0]['order'];
        }
        $nextStage = $this->getNextPlanStage($uid, $planId, $order);
        if (empty($nextStage)) {
            return FALSE;
        }
        if ($nextStage['unlock_able'] == '0') {
            //下一个阶段不可解锁
            return FALSE;
        }
        if ($nextStage['is_unlocked'] == '1') {
            //下一个阶段已解锁
            return FALSE;
        }
        //解锁
        return $this->unlockUserPlanStageById($nextStage['id']);
    }

    /**
     * 获取选择计划的计划阶段数据
     * @param $uid
     * @param $plan_id
     * @return array
     */
    public function getSelectPlanStageData($uid, $plan_id)
    {
        $result = [];
        $this->load->model('public/plan_model');
        $plan_info = $this->plan_model->getUserPlanInfo($uid, $plan_id);
        if (!empty($plan_info)) {
            //更新计划当前状态
            $this->plan_model->updateCurrentPlan($uid, $plan_id);
            $result['current_plan_info'] = $plan_info;
            //计划下阶段
            $stage = $this->getCurrentPlanStageData($uid, $plan_id);
            if (!empty($stage)) {
                $result = array_merge($result, $stage);
                $result['headimgurl'] = avatar($uid, 'small');
            }
        }
        return $result;
    }

    /**
     * 获取当前计划下的阶段数据
     * @param $uid
     * @param $plan_id
     */
    public function getCurrentPlanStageData($uid, $plan_id)
    {
        $data = array();
        $plan_stage = array();
        $stage_lab = $this->getPlanStageInfo($uid, $plan_id);
        if (!empty($stage_lab)) {
            $plan_stage_ids = array();
            $max_plan_stage_id = 0;
            $max_order = 0;
            foreach ($stage_lab as $key => $val) {
                $plan_stage_ids[] = $val['plan_stage_id'];
                $val['is_finish'] = 0;
                if ($this->planStageAllFinish($uid, $plan_id, $val['plan_stage_id'])) {
                    $val['is_finish'] = 1;
                }
                $plan_stage[$key] = $val;
                if ($val['unlock_able'] == 1 && $val['is_unlocked'] == 1) {
                    if ($val['order'] > $max_order) {
                        $max_order = $val['order'];
                        $max_plan_stage_id = $val['plan_stage_id'];
                    }
                }
            }
            $stage_data = $this->getStageIdsInfo($plan_stage_ids);
            $data['plan_stage'] = $plan_stage;
            $data['stage_data'] = $stage_data;
            $data['max_plan_stage_id'] = $max_plan_stage_id;
        }
        return $data;
    }

    /**
     * 获取阶段id数组信息
     * @param array $plan_stage_ids
     * @return array
     */
    public function getStageIdsInfo($plan_stage_ids = array())
    {
        $stage_data = array();
        if (!empty($plan_stage_ids)) {
            $plan_stage = $this->getIdsPlanStageInfo($plan_stage_ids);
            if (!empty($plan_stage)) {
                $stage_ids = array();
                foreach ($plan_stage as $val) {
                    if (!in_array($val['stage_id'], $stage_ids)) $stage_ids[] = $val['stage_id'];
                }

                $stage_info = array();
                if (!empty($stage_ids)) {
                    $stage_arr = $this->getIdsStageInfo($stage_ids);
                    if (!empty($stage_arr)) {
                        foreach ($stage_arr as $sv) {
                            $sv['level'] = '';
                            $stage_info[$sv['id']] = $sv;
                        }
                        foreach ($plan_stage as $vv) {
                            if (isset($stage_info[$vv['stage_id']])) {
                                $stage_info[$vv['stage_id']]['level'] = $vv['level'];
                                $stage_data[$vv['id']] = $stage_info[$vv['stage_id']];
                            }
                        }
                    }
                }
            }
        }
        return $stage_data;
    }

    /**
     * 解锁阶段
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     */
    public function unlockPlanStage($uid, $plan_id, $plan_stage_id, $show_popup)
    {

        $valid = array('uid' => $uid, 'plan_id' => $plan_id, 'plan_stage_id' => $plan_stage_id);
        $result = $this->validation->validate('planStageLock', $valid);
        if ($result === TRUE) {
            $result = [];
            $result['code'] = 0;
            $plan_stage = $this->getUserStageInfo($uid, $plan_id, $plan_stage_id);
            if (intval($show_popup) === 1) {
                //验证前面的计划阶段是否全部完成
                $beforePlanStage = $this->getBeforOrderPlanStageInfo($uid, $plan_id, intval($plan_stage['order']));
                if (!empty($beforePlanStage)) {
                    if (intval($beforePlanStage['is_unlocked']) !== 1 || $this->planStageAllFinish($uid, $plan_id, $beforePlanStage['plan_stage_id']) !== TRUE) {
                        $result['code'] = 215;
                        return $result;
                    }
                }
            }
            $unlock = $this->unLockUserPlanStage($uid, $plan_id, $plan_stage_id);
            if ($unlock) {
                $result['code'] = 200;
            } else {
                $result['code'] = 500;
            }
        }
        return $result;
    }

    /**
     * 计划阶段是否全部完成
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     */
    public function planStageAllFinish($uid, $plan_id, $plan_stage_id)
    {
        $finish = FALSE;
        //加入的阶段课程
        $this->load->model('public/schedule_model');
        $this->load->model('public/lesson_model');
        $join_schedule_ids = $this->schedule_model->getUserPlanStageJoinSchedule($uid, $plan_id, $plan_stage_id);
        if (!empty($join_schedule_ids)) {
            foreach ($join_schedule_ids as $item) {
                $report_lession_count = intval($this->lesson_model->userReportCount($uid, $item));
                $lession_count = intval($this->lesson_model->getScheduleLessonCount($item));
                if ($report_lession_count > 0 && $lession_count > 0 && ($report_lession_count / $lession_count) * 100 >= 80) {
                    $finish = TRUE;
                    break;
                }
            }
        }
        return $finish;
    }

    /**
     * 点击已解锁的计划阶段
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     */
    public function clickUnlockPlanStage($uid, $plan_id, $plan_stage_id)
    {

        $valid = array('uid' => $uid, 'plan_id' => $plan_id, 'plan_stage_id' => $plan_stage_id);
        $result = $this->validation->validate('planAndStage', $valid);
        if ($result === TRUE) {
            $result = [];
            $plan_stage = $this->getUserStageInfo($uid, $plan_id, $plan_stage_id);
            if (!empty($plan_stage)) {
                $result['code'] = 200;
                $result['links'] = 'selectSchedule?plan=' . $plan_id . '&stage=' . $plan_stage_id;
                /*if (intval($plan_stage['select_schedule_id']) > 0) {
                    if ($plan_stage['unlimit_expire'] == 1 || (intval($plan_stage['expire']) > 0 && $plan_stage['expire'] > date('Y-m-d H:i:s'))) {
                        $result['links'] = '/learningsystem/schedule/' . intval($plan_stage['select_schedule_id']) . '?plan=' . $plan_id . '&stage=' . $plan_stage_id;
                    }
                }*/
            }
        }
        return $result;
    }

    /**
     * 计划阶段数据缓存
     * @param $plan_stage_id
     * @param bool $cache
     * @return array
     */
    public function getPlanStageCacheData($plan_stage_id, $cache = FALSE)
    {
        $this->load->driver('cache');
        $key = sprintf(get_options('baseDataCacheKey', 'planStage'), $plan_stage_id);
        if ($cache === TRUE && BASE_DATA_CACHE_OPEN === TRUE) {
            $cacheData = $this->cache->redis->hGetAll($key);
            if (!empty($cacheData)) return $cacheData;
        }
        $data = parent::$main_db->from('sty_plan_stage')->where([
            'id' => $plan_stage_id
        ])->limit(1)->get()->row_array();
        if (!empty($data)) {
            if (BASE_DATA_CACHE_OPEN === TRUE) $this->cache->redis->hMset($key, $data);
            return $data;
        }
        return [];
    }

    /**
     * 阶段子数据缓存
     * @param $stage_id
     * @param bool $cache
     * @return array
     */
    public function getStageCacheData($stage_id, $cache = FALSE)
    {
        $this->load->driver('cache');
        $key = sprintf(get_options('baseDataCacheKey', 'stage'), $stage_id);
        if ($cache === TRUE && BASE_DATA_CACHE_OPEN === TRUE) {
            $cacheData = $this->cache->redis->hGetAll($key);
            if (!empty($cacheData)) return $cacheData;
        }
        $data = parent::$main_db->from('sty_stage')->where([
            'id' => $stage_id
        ])->limit(1)->get()->row_array();
        if (!empty($data)) {
            if (BASE_DATA_CACHE_OPEN === TRUE) $this->cache->redis->hMset($key, $data);
            return $data;
        }
        return [];
    }
}

