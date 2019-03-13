<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_validation_model extends MY_Model
{
    /**
     * 验证用户所购商品是否存在某上课模式
     * @param $uid
     * @param int $type 1:主修,2:口语,3:选修,4:专修,5定制
     * @return mixed array
     */
    public function existGoodsTypeMode($uid, $type)
    {
        $result = FALSE;
        $type_mode = parent::$main_db
            ->from('sty_user_goods_info')
            ->where(
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'is_del' => 0
                )
            )
            ->count_all_results();
        if ($type_mode) $result = TRUE;
        return $result;
    }

    /**
     * 是否存在激活的商品
     * @param $uid
     * @return bool
     */
    public function existActivateGoods($uid)
    {
        $result = FALSE;
        $activate = parent::$main_db
            ->from('sty_user_goods')
            ->where(
                array(
                    'uid' => $uid,
                    'is_activate' => 1,
                    'is_del' => 0
                )
            )
            ->count_all_results();
        if ($activate)
            $result = TRUE;
        return $result;
    }

    /**
     * 是否存在未激活的单品卡
     * @param $uid
     * @return bool
     */
    public function existUnactivateItem($uid)
    {
        $result = FALSE;
        $unactivate = parent::$main_db
            ->from('sty_user_goods')
            ->where(
                array(
                    'uid' => $uid,
                    'type_mode' => 2,
                    'is_activate' => 0,
                    'is_del' => 0
                )
            )
            ->count_all_results();
        if ($unactivate)
            $result = TRUE;
        return $result;
    }

    /**
     * 是否存在已加入的计划
     * @param $uid
     * @return bool
     */
    public function existJoinPlan($uid)
    {
        $result = FALSE;
        $join = parent::$main_db
            ->from('sty_user_plan')
            ->where(
                array(
                    'uid' => $uid,
                    'is_del' => 0
                )
            )
            ->count_all_results();
        if ($join)
            $result = TRUE;
        return $result;
    }

    /**
     * 验证计划，计划阶段，阶段课程是否匹配
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     * @return bool
     */
    public function validatePlanStageMatch($plan_id, $plan_stage_id, $schedule_id)
    {
        $sql = sprintf('SELECT EXISTS(
              SELECT * FROM `sty_schedule_stage_item` WHERE `plan_id` = %d 
              AND `plan_stage_id` = %d AND `schedule_id` = %d AND `is_del` = 0
              ) as found',
            $plan_id, $plan_stage_id, $schedule_id);

        $query = parent::$main_db->query($sql);
        $row = $query->row();
        if (!empty($row->found)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 验证计划是否解锁
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @return bool
     */
    public function validatePlanStage($uid, $plan_id, $plan_stage_id, $lock = FALSE)
    {
        $query = parent::$main_db->select('expire, unlimit_expire')
            ->from('sty_user_plan_stage')
            ->where(array(
                'uid' => $uid,
                'plan_id' => $plan_id,
                'plan_stage_id' => $plan_stage_id,
                'is_del' => 0
            ));
        if ($lock) {
            $query->where('unlock_able', 1)->where('is_unlocked', 0);
        } else {
            $query->where('is_unlocked', 1);
        }
        if ($query->limit(1)->get()->row_array()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 验证计划阶段有效期
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @return bool
     */
    public function validatePlanStageExpire($uid, $plan_id, $plan_stage_id)
    {
        $query = parent::$main_db->select('expire, unlimit_expire')
            ->from('sty_user_plan_stage')
            ->where(array(
                'uid' => $uid,
                'plan_id' => $plan_id,
                'plan_stage_id' => $plan_stage_id,
                'is_unlocked' => 1,
                'is_del' => 0
            ));
        if ($expire = $query->limit(1)->get()->row_array()) {
            $current = date('Y-m-d H:i:s');
            if (intval($expire['unlimit_expire']) === 1 ||
                $current <= $expire['expire']
            ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 验证计划是否加入
     * @param $uid
     * @param $plan_id
     * @return bool
     */
    public function validatePlan($uid, $plan_id)
    {
        $query = parent::$main_db->select('expire, unlimit_expire')
            ->from('sty_user_plan')
            ->where(array(
                'uid' => $uid,
                'plan_id' => $plan_id,
                'is_del' => 0
            ))
            ->limit(1)
            ->get();

        if ($query->row_array()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 验证计划有效期
     * @param $uid
     * @param $plan_id
     * @return bool
     */
    public function validatePlanExpire($uid, $plan_id)
    {
        $query = parent::$main_db->select('expire, unlimit_expire')
            ->from('sty_user_plan')
            ->where(array(
                'uid' => $uid,
                'plan_id' => $plan_id,
                'is_del' => 0
            ))
            ->limit(1)
            ->get();
        if ($expire = $query->row_array()) {
            $current = date('Y-m-d H:i:s');
            if (intval($expire['unlimit_expire']) === 1 ||
                $current <= $expire['expire']
            ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 验证用户是否已经报到过
     * @param $uid
     * @param $schedule_lesson_id
     * @return bool
     */
    public function validateCheckedIn($uid, $schedule_lesson_id)
    {
        $query = parent::$main_db->select('id')
            ->from('sty_user_schedule_lesson')
            ->where(array(
                'uid' => $uid,
                'schedule_lesson_id' => $schedule_lesson_id,
                'check_in_status' => 1,
                'is_del' => 0
            ))
            ->limit(1)
            ->get();

        if ($checked_in = $query->row_array()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 验证用户是否已经预约过
     * @param $uid
     * @param $schedule_lesson_id
     * @return bool
     */
    public function validateReserved($uid, $schedule_lesson_id)
    {
        $count = parent::$main_db->from('sty_user_schedule_lesson')
            ->where([
                'uid' => $uid,
                'schedule_lesson_id' => $schedule_lesson_id,
                'reservation_status' => 1,
                'is_del' => 0
            ])->count_all_results();
        if ($count > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 验证用户是否已经完成一课一练或单元测试
     * @param $uid
     * @param $exerciseId
     * @param $type string practice | unit
     * @return bool
     */
    public function validateExerciseCompleted($uid, $exerciseId, $type)
    {
        $query = parent::$main_db->select('id')
            ->from('sty_user_schedule_lesson_score')
            ->where(array(
                'uid' => $uid,
                'sub_id' => $exerciseId,
                'is_del' => 0
            ))
            ->limit(1);

        if ($type === 'practice') {
            $query->where('type', 1);
        } elseif ($type === 'unit') {
            $query->where('type', 2);
        } else {
            return FALSE;
        }
        $result = $query->get();
        if ($completed = $result->row_array()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 验证用户是否加入阶段课程
     * @param $uid
     * @param $schedule_id
     * @return bool false 没加入此阶段课程|true 已加入
     */
    public function validateUserJoinedSchedule($uid, $schedule_id)
    {
        $count = parent::$main_db->from('sty_user_schedule')
            ->where([
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'is_del' => 0
            ])->count_all_results();
        if ($count > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 验证用户可加入阶段课程
     * @param $uid
     * @param $schedule_id
     * @return bool true 没加入此阶段课程|false 已加入
     */
    public function validateUserNoJoinedSchedule($uid, $schedule_id)
    {
        $sql = sprintf('SELECT EXISTS(
              SELECT * FROM `sty_user_schedule` WHERE `uid` = %d AND `schedule_id` = %d AND `is_del` = 0
              ) as found',
            $uid, $schedule_id);
        $query = parent::$main_db->query($sql);

        $row = $query->row();
        if (!empty($row->found)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 验证题库考场是否超过人数限制
     * @param $paperId
     * @param $uid
     * @return bool
     */
    public function isOverZdtMaxNum($paperId, $uid)
    {
        $key = get_options('baseDataCacheKey', 'zdt_max_num') . $paperId;
        $this->load->driver('cache');
        if ($this->cache->redis->sPop($key) === FALSE) {
            return TRUE;
        } else {
            $pkey = get_options('baseDataCacheKey', 'zdt_paper_examing') . $paperId;
            $this->cache->redis->sAdd($pkey, $uid);
            return FALSE;
        }
    }

    /**
     * 判断用户是否正在考试
     * @param $paperId
     * @param $uid
     * @return bool
     */
    public function isExaming($paperId, $uid)
    {
        $key = get_options('baseDataCacheKey', 'zdt_paper_examing') . $paperId;
        $this->load->driver('cache');
        return $this->cache->redis->sIsMember($key, $uid) ? TRUE : FALSE;
    }
}
