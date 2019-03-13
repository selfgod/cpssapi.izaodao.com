<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_model extends MY_Model
{

    /**
     * 获取用户阶段基本信息
     * @param $uid
     * @param $plan_id
     */
    public function getUserPlanInfo($uid, $plan_id)
    {
        return parent::$main_db->select('plan_id, expire,unlimit_expire,is_join')
            ->from('sty_user_plan')
            ->where(array('uid' => $uid, 'plan_id' => $plan_id, 'is_del' => 0))
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 获取存在有效期的用户计划
     * @param $uid
     * @return array
     */
    public function getUserExistExpirePlan($uid)
    {
        $data = array();
        $plan = parent::$main_db->select('plan_id,expire')
            ->from('sty_user_plan')
            ->where(array('uid' => $uid, 'unlimit_expire' => 0, 'is_del' => 0))
            ->where('expire IS NOT NULL')
            ->get()->result_array();
        if (!empty($plan)) {
            foreach ($plan as $item) {
                $data[$item['plan_id']] = $item;
            }
        }
        return $data;
    }

    /**
     * 用户加入阶段的信息
     * @param $uid
     * @param $plan_id
     */
    public function getUserJoinPlanInfo($uid, $plan_id)
    {
        return parent::$main_db->select('sup.plan_id,sup.expire,sup.unlimit_expire,sp.name')
            ->from('sty_user_plan AS sup')
            ->join('sty_plan AS sp', 'sp.id=sup.plan_id', 'left')
            ->where(array(
                'sup.uid' => $uid,
                'sup.plan_id' => $plan_id,
                'sup.is_del' => 0,
                'sp.is_del' => 0
            ))
            ->limit(1)->get()->row_array();
    }

    /**
     * 获取未激活单品卡的计划信息
     * @param $uid
     * @return mixed
     */
    public function getUnactivateItemPlan($uid)
    {
        return parent::$main_db->select('plan_id,unlimit_expire,expire')
            ->from('sty_user_plan')->where(array(
                'uid' => $uid,
                'is_del' => 0
            ))->where('(unlimit_expire=0 AND expire IS NULL)')->order_by('id DESC')->limit(1)->get()->row_array();
    }

    /**
     * 获取用户拥有的计划
     * @param $uid
     * @param bool $is_join 拥有已加入计划
     * @return mixed
     */
    public function getUserHavePlan($uid, array $w = [])
    {
        $this->load->model('public/common_model');
        $where = ['sup.uid' => $uid, 'sup.is_del' => 0, 'sp.is_del' => 0];
        $field = 'sup.plan_id, sup.expire, sup.is_join, sup.unlimit_expire, sp.name, sp.title, sp.cover_img, sp.description, sp.business_type';
        if (!empty($w)) $where = array_merge($where, $w);
        $join = ['joinTable' => 'sty_user_plan AS sup', 'joinCond' => 'sup.plan_id = sp.id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_plan AS sp', [
            'field' => $field,
            'where' => $where,
            'order' => ['sup.create_time DESC']
        ], [$join]);
    }

    /**
     * 用户加入计划的操作
     * @param $uid
     * @param $plan_id
     * @return int
     */
    public function userJoinPlanOperation($uid, $plan_id)
    {
        if (!$uid || !$plan_id) return 500;
        parent::$main_db->trans_start();
        $this->userJoinPlan($uid, $plan_id);
        $this->updateCurrentPlan($uid, $plan_id);
        parent::$main_db->trans_complete();
        if (parent::$main_db->trans_status() !== FALSE) {
            return 200;
        }
        return 500;
    }

    /**
     * 用户加入计划
     * @param $uid
     * @param $plan_id
     * @return int
     */
    public function userJoinPlan($uid, $plan_id)
    {
        $data['is_join'] = 1;
        $data['modify_time'] = date('Y-m-d H:i:s');
        $where['uid'] = $uid;
        $where['plan_id'] = $plan_id;
        $where['is_del'] = 0;
        return parent::$main_db->update('sty_user_plan', $data, $where);
    }

    /**
     * 更新当前计划
     * @param $uid
     * @param $plan_id
     * @return mixed
     */
    public function updateCurrentPlan($uid, $plan_id, $current_plan_id = NULL)
    {
        $this->load->model('public/member_model');
        $isUpdate = 0;
        $res = '';
        if (intval($current_plan_id) > 0 && intval($current_plan_id) != $plan_id) {
            $isUpdate = 1;
        }
        if ($isUpdate == 0) {
            $current = $this->member_model->getUserMessage($uid);
            if (!empty($current) && intval($current['current_plan_id']) != intval($plan_id)) {
                $isUpdate = 1;
            }
        }
        if ($isUpdate == 1) {
            $res = parent::$main_db->update('message_user', array('current_plan_id' => $plan_id), array('uid' => $uid));
        }
        if ($res || $isUpdate == 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 更新用户计划
     * @param $uid
     * @param $plan_id
     * @param $data
     * @return mixed
     */
    public function updateUserPlan($uid, $plan_id, $data)
    {
        return parent::$main_db->update('sty_user_plan', $data, array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'is_del' => 0
        ));
    }

    /**
     * 获取用户已经加入的所有计划id
     * @param $uid
     * @return mixed
     */
    public function getUserJoinedPlanIds($uid)
    {
        return parent::$main_db->select('plan_id, expire, unlimit_expire')
            ->from('sty_user_plan')
            ->where([
                'uid' => $uid,
                'is_del' => 0
            ])->get()->result_array();
    }

    /**
     * 获取有效期内的所有计划
     * @param $uid
     * @return array
     */
    public function getExpirePlan($uid)
    {
        $result = array();
        $now = time();
        $plans = $this->getUserJoinedPlanIds($uid);
        foreach ($plans as $plan) {
            $expire = strtotime($plan['expire']);
            if ($expire > $now || $plan['unlimit_expire'] == 1) {
                $result[] = $plan['plan_id'];
            }
        }
        return $result;
    }

    /**
     * 计划数据缓存
     * @param $plan_id
     * @param bool $cache
     * @return array
     */
    public function getPlanCacheData($plan_id, $cache = FALSE)
    {
        $this->load->driver('cache');
        $key = sprintf(get_options('baseDataCacheKey', 'plan'), $plan_id);
        if ($cache === TRUE && BASE_DATA_CACHE_OPEN === TRUE) {
            $cacheData = $this->cache->redis->hGetAll($key);
            if (!empty($cacheData)) return $cacheData;
        }
        $data = parent::$main_db->from('sty_plan')->where([
            'id' => $plan_id
        ])->limit(1)->get()->row_array();
        if (!empty($data)) {
            if (BASE_DATA_CACHE_OPEN === TRUE) $this->cache->redis->hMset($key, $data);
            return $data;
        }
        return [];
    }

}