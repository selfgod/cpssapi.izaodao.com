<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/goods_model');
        $this->load->library('thrift');
    }

    public function validateLeave($uid)
    {
        $result = array('code' => 0);
        if ($this->isLeaveing($uid)) {
            //正在请假
            $result['code'] = 232;
        } elseif ($this->isSuspending($uid)) {
            //正在休学
            $result['code'] = 233;
        } else {
            $result['code'] = 200;
        }
        return $result;
    }

    /**
     * 请假结束处理
     * @param $uid
     */
    public function leaveEnd($uid)
    {
        $now_time = date('Y-m-d H:i:s');
        $leave = $this->getEndLeave($uid);
        if (!empty($leave)) {
            $data['status'] = 2;
            $data['modify_time'] = $now_time;
            $where['uid'] = $uid;
            $where['status'] = 1;
            $where['end_time<='] = $now_time;
            $where['is_del'] = 0;
            parent::$main_db->update('sty_user_leave', $data, $where);
        }
    }

    /**
     * 验证休学
     * @param $uid
     */
    public function validateSuspend($uid)
    {
        $this->load->model('public/lesson_model');
        $result = array('code' => 0, 'data' => array());
        if ($this->isSuspending($uid, FALSE)) {
            //休学过
            $result['code'] = 239;
        } else {
            $this->load->model('public/member_model');
            $learnInfo = $this->member_model->getLearnInfo($uid);
            if (intval($learnInfo['official_class']) !== 1) {
                $result['code'] = 289;
            } else {
                //验证有效期
                $able_goods = $this->goods_model->getUserGoodsDetail($uid);
                if (!empty($able_goods)) {
                    //验证是否存在已预约未完成的课程
                    if ($this->lesson_model->existUserReservationUnfinish($uid)) {
                        $result['code'] = 241;
                    } else {
                        $result['code'] = 200;
                        $result['data'] = $able_goods;
                    }
                } else {
                    //不存在已激活未过期的商品
                    $result['code'] = 240;
                }
            }
        }
        return $result;
    }

    /**
     * 验证开始结束时间
     * @param $start_time
     * @param $end_time
     * @param $max
     * @param int $min
     * @return bool
     */
    public function validateStartEndTime($start_time, $end_time, $max, $min = 0)
    {
        $res = FALSE;
        $now_time = date('Y-m-d');
        if (!empty(strtotime($start_time)) && !empty(strtotime($end_time)) && $start_time >= $now_time && intval($max) > 0 && intval($min) >= 0) {
            if (strtotime($end_time) >= strtotime($start_time)) {
                if (strtotime($start_time . ' +' . (intval($max) - 1) . ' days') >= strtotime($end_time)) {
                    $res = TRUE;
                }
                if (intval($min) > 0) {
                    if (strtotime($start_time . ' +' . (intval($min) - 1) . ' days') >= strtotime($end_time)) {
                        $res = FALSE;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 请假
     * @param $uid
     * @return array
     */
    public function leaveDetail($uid)
    {
        $data = array();
        $this->leaveEnd($uid);
        $result = $this->validateLeave($uid);
        $data['doing'] = 'submit';
        if ($result['code'] == 232) {
            $data['doing'] = 'leave_doing';
        } else if ($result['code'] == 233) {
            $data['doing'] = 'suspend_doing';
        }
        $data['leave_list'] = $this->getUserLeaveDetail($uid);
        return $data;
    }

    /**
     * 提交请假
     * @param $uid
     * @param $start_time
     * @param $end_time
     * @param $reason
     */
    public function leaveSubmit($uid, $start_time, $end_time, $reason)
    {
        $result = $this->validateLeave($uid);
        if ($result['code'] == 200) {
            $result = array();
            if (!$start_time || !$end_time || !$reason || !strtotime($start_time) || !strtotime($end_time)) {
                $result['code'] = 234;
            } else {
                if ($this->validateStartEndTime($start_time, $end_time, 15)) {
                    //提交
                    if ($this->addLeave($uid, $start_time, $end_time, $reason)) {
                        $result['code'] = 200;
                    } else {
                        $result['code'] = 236;
                    }
                } else {
                    $result['code'] = 235;
                }
            }
        }
        return $result;
    }

    /**
     * 销假
     * @param $uid
     * @param $leave_id
     * @return array
     */
    public function cancelLeave($uid, $leave_id)
    {
        $result = array('code' => 0);
        $now_time = date('Y-m-d H:i:s');
        $leave = $this->getUserLeaveInfo($uid, $leave_id);
        if (!empty($leave)) {
            if ($leave['start_time'] > $now_time) {
                $data['status'] = 0;
            } else {
                $data['status'] = 3;
                $data['end_time'] = date('Y-m-d 23:59:59');
            }
            $data['modify_time'] = $now_time;
            if ($this->updateLeave($uid, $leave_id, $data)) {
                $result['code'] = 200;
            } else {
                $result['code'] = 237;
            }
        } else {
            $result['code'] = 238;
        }
        return $result;
    }

    /**
     * 休学
     * @param $uid
     * @return array
     */
    public function suspendDetail($uid)
    {
        $data = array();
        $result = $this->validateSuspend($uid);
        $data['un_suspend'] = 0;
        $data['un_suspend_prompt'] = '';
        $data['suspend_info'] = array();
        if ($result['code'] == 239) {
            $data['suspend_info'] = $this->getUserSuspendInfo($uid);
        }
        if ($result['code'] == 289) {
            $data['un_suspend'] = 1;
            $data['un_suspend_prompt'] = '你还没有正在学习中的课程，不用休学哦~';
        }
        if ($result['code'] == 240) {
            $data['un_suspend'] = 1;
            $data['un_suspend_prompt'] = '你还没有正在学习中的课程，不用休学哦~';
        }
        if ($result['code'] == 241) {
            $data['un_suspend'] = 1;
            $data['un_suspend_prompt'] = '你现在有已预约未完成的课程，不能休学！';
        }
        return $data;
    }

    /**
     * 提交休学
     * @param $uid
     * @param $start_time
     * @param $end_time
     * @param $reason
     * @return array
     */
    public function suspendSubmit($uid, $start_time, $end_time, $reason)
    {
        $result = $this->validateSuspend($uid);
        if ($result['code'] == 200) {
            $activation_able = $result['data'];
            $result = array();
            if (!$start_time || !$end_time || !$reason || !strtotime($start_time) || !strtotime($end_time)) {
                $result['code'] = 234;
            } else {
                $able_goods_expire = $this->goods_model->getAblegoodsExpire($activation_able);
                if (!empty($able_goods_expire)) {
                    if ($able_goods_expire['unlimit_expire'] == 1 || ($able_goods_expire['unlimit_expire'] == 0 && !empty($able_goods_expire['max_expire']) && $able_goods_expire['max_expire'] > date('Y-m-d 00:00:00', strtotime($start_time)))) {
                        if ($this->validateStartEndTime($start_time, $end_time, 180, 15)) {
                            //提交休学
                            /*parent::$main_db->trans_start();
                            $this->addSuspend($uid, $start_time, $end_time, $reason);
                            parent::$main_db->update('message_user',[
                                'suspend_end' => 1,
                                'updatedate' => time()
                            ],['uid' => $uid]);
                            parent::$main_db->trans_complete();*/
                            $suspend = $this->thrift->service('User')->updateUserSuspend([
                                'uid' => $uid,
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                                'reason' => $reason
                            ]);
                            if ($suspend === TRUE) {
                                $result['code'] = 200;
                            } else {
                                $result['code'] = 236;
                            }
                        } else {
                            //休学时间不得少于15天，不得大于180天
                            $result['code'] = 243;
                        }
                    } else {
                        //请在商品有效期内开始休学
                        $result['code'] = 242;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 停止休学
     * @param $uid
     * @return array
     */
    public function suspendStop($uid)
    {
        $this->load->model('public/member_model');
        $result = array('code' => 0);
        $endSuspend = $this->thrift->service('User')->updateUserEndSuspend($uid);
        if($endSuspend === TRUE){
            $result['code'] = 200;
        }else{
            $result['code'] = 500;
        }
/*        $now_time = date('Y-m-d H:i:s');
        $suspend = $this->getUserSuspendInfo($uid);
        if (!empty($suspend)) {
            $suspend_id = $suspend['id'];
            $change_expire = FALSE;
            $suspend_end = 2;
            if ($suspend['start_time'] > $now_time) {
                $save['status'] = 0;
                $suspend_end = 0;
            } elseif ($suspend['end_time'] <= $now_time) {
                $save['status'] = 2;
                $save['end_time'] = $suspend['end_time'];
                $change_expire = TRUE;
            } else {
                $save['status'] = 3;
                $save['end_time'] = date('Y-m-d 23:59:59');
                $change_expire = TRUE;
            }
            $save['modify_time'] = $now_time;

            parent::$main_db->trans_start();
            $this->updateSuspend($uid, $suspend_id, $save);
            parent::$main_db->update('message_user',[
                'suspend_end' => $suspend_end,
                'updatedate' => time()
            ],['uid' => $uid]);
            if ($change_expire) {
                //更新商品有效期
                if ($change_expire) {
                    $this->updateGoodsExpire($uid, $suspend['start_time'], $save['end_time']);
                    $this->member_model->goodsExpireMessageUserUpdate($uid);
                }
            }
            parent::$main_db->trans_complete();

            if (parent::$main_db->trans_status() !== FALSE) {
                $this->thrift->service('User')->updateUserLearnInfoSuspend($uid,2);
                $result['code'] = 200;
            } else {
                $result['code'] = 500;
            }
        }*/
        return $result;
    }

    /**
     * 更新商品有效期
     * @param $uid
     * @param $start_time
     * @param $end_time
     */
    public function updateGoodsExpire($uid, $start_time, $end_time)
    {
        $now_time = date('Y-m-d H:i:s');
        $able = $this->goods_model->getUserGoodsDetail($uid, $start_time);
        if (!empty($able)) {
            $goods_plan_stage = array();
            foreach ($able as $item) {
                if ($item['unlimit_expire'] == 0) {
                    if (!empty($item) && intval($item['expire'])) {
                        $after_expire = $this->getSuspendAfterExpire($start_time, $end_time, $item['expire']);
                        if (!empty($after_expire) && $after_expire > $item['expire']) {
                            //更新商品
                            $this->goods_model->updateUserGoods($uid, $item['goods_id'], array('expire' => $after_expire, 'modify_time' => $now_time));
                            //更新商品有效期变更记录
                            $this->goods_model->addUserGoodsExpireChange(array('uid' => $uid, 'goods_id' => $item['goods_id'], 'expire' => $item['expire'], 'after_expire' => $after_expire, 'description' => '停止休学，商品有效期从' . $item['expire'] . '变化到' . $after_expire));
                            $buy_arr = json_decode($item['buy_info'], TRUE);
                            if (empty($buy_arr)) {
                                $buy_arr = unserialize($item['buy_info']);
                            }
                            $goods_plan_stage[$item['goods_id']]['plan_stage'] = !empty($buy_arr) ? $buy_arr['plan_stage'] : array();
                            $goods_plan_stage[$item['goods_id']]['after_expire'] = $after_expire;
                        }
                    }
                }
            }
            if (!empty($goods_plan_stage)) {
                $this->saveGoodsPlanStageExpire($uid, $goods_plan_stage);
            }
        }
    }

    /**
     * 更新用户计划 计划阶段有效期
     * @param $uid
     * @param $goods_plan_stage
     */
    public function saveGoodsPlanStageExpire($uid, $goods_plan_stage)
    {
        $now_time = date('Y-m-d H:i:s');
        $this->load->model('public/plan_model');
        $this->load->model('public/plan_stage_model');
        if (!empty($goods_plan_stage)) {
            $plan = array();
            $plan_stage = array();
            foreach ($goods_plan_stage as $item) {
                if (!intval($item['after_expire'])) {
                    continue;
                }
                if (!empty($item['plan_stage'])) {
                    foreach ($item['plan_stage'] as $k => $v) {
                        if (!isset($plan[$k])) {
                            $plan[$k]['plan_id'] = $k;
                        }
                        if (!isset($plan[$k]['expire']) || empty($plan[$k]['expire']) || $item['after_expire'] > $plan[$k]['expire']) {
                            $plan[$k]['expire'] = $item['after_expire'];
                        }
                        if (!empty($v)) {
                            foreach ($v as $vv) {
                                if (!isset($plan_stage[$vv])) {
                                    $plan_stage[$vv]['plan_id'] = $k;
                                    $plan_stage[$vv]['plan_stage_id'] = $vv;
                                }
                                if (!isset($plan_stage[$vv]['expire']) || empty($plan_stage[$vv]['expire']) || $item['after_expire'] > $plan_stage[$vv]['expire']) {
                                    $plan_stage[$vv]['expire'] = $item['after_expire'];
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($plan)) {
                $user_plan = $this->plan_model->getUserExistExpirePlan($uid);
                foreach ($plan as $pv) {
                    if (isset($user_plan[$pv['plan_id']]) && intval($pv['expire']) && $pv['expire'] > $user_plan[$pv['plan_id']]['expire']) {
                        //更新计划
                        $this->plan_model->updateUserPlan($uid, $pv['plan_id'], array('expire' => $pv['expire'], 'modify_time' => $now_time));
                    }
                }
            }
            if (!empty($plan_stage)) {
                foreach ($plan_stage as $sv) {
                    if (intval($sv['expire'])) {
                        $user_plan_stage_find = $this->plan_stage_model->getUserStageInfo($uid, $sv['plan_id'], $sv['plan_stage_id']);
                        if (!empty($user_plan_stage_find) && $user_plan_stage_find['unlimit_expire'] == 0 && $user_plan_stage_find['unlock_able'] == 1 && intval($user_plan_stage_find['expire']) && $sv['expire'] > $user_plan_stage_find['expire']) {
                            //更新计划阶段
                            $this->plan_stage_model->updateUserPlanStage($uid, $sv['plan_id'], $sv['plan_stage_id'], array('expire' => $sv['expire'], 'modify_time' => $now_time));
                        }
                    }
                }
            }
        }
    }

    /**
     * 计算休学后有效期
     * @param $start_time
     * @param $end_time
     * @param $expire
     * @return false|string
     */
    public function getSuspendAfterExpire($start_time, $end_time, $expire)
    {
        $this->load->helper('date');
        $after_expire = '';
        if (intval($start_time) && intval($end_time) && intval($expire) && $end_time > $start_time && $expire > $start_time) {
            if ($end_time >= $expire) {
                $diff = diff_days(strtotime($start_time), strtotime($expire)) + 1;
                $after_expire = date('Y-m-d 23:59:59', strtotime($end_time . ' +' . $diff . ' days'));
            }
            if ($end_time < $expire) {
                $diff = diff_days(strtotime($start_time), strtotime($end_time)) + 1;
                $after_expire = date('Y-m-d 23:59:59', strtotime($expire . ' +' . $diff . ' days'));
            }
        }
        return $after_expire;
    }

    /**
     * 是否正在请假
     * @param $uid
     * @return mixed
     */
    public function isLeaveing($uid)
    {
        $query = parent::$main_db->from('sty_user_leave')->where(array('uid' => $uid, 'status' => 1, 'is_del' => 0))->where('end_time>', date('Y-m-d H:i:s'))->count_all_results();
        if ($query) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 插入请假记录
     * @param $uid
     * @param $start_time
     * @param $end_time
     * @param $reason
     * @return mixed
     */
    public function addLeave($uid, $start_time, $end_time, $reason)
    {
        return parent::$main_db->insert('sty_user_leave', array('uid' => $uid, 'start_time' => date('Y-m-d 00:00:00', strtotime($start_time)), 'end_time' => date('Y-m-d 23:59:59', strtotime($end_time)), 'reason' => $reason, 'status' => 1));
    }

    /**
     * 请假详细信息
     * @param $uid
     * @return mixed
     */
    public function getUserLeaveDetail($uid)
    {
        return parent::$main_db->select('id, start_time, end_time, reason, status')->from('sty_user_leave')->where(array('uid' => $uid, 'is_del' => 0))->where('status>', 0)->order_by('create_time', 'DESC')->get()->result_array();
    }

    /**
     * 获取某条请假信息
     * @param $uid
     * @param $leave_id
     * @return mixed
     */
    public function getUserLeaveInfo($uid, $leave_id)
    {
        return parent::$main_db->select('id, start_time, end_time, reason, status')->from('sty_user_leave')->where(array('id' => $leave_id, 'uid' => $uid, 'status' => 1, 'is_del' => 0))->where('end_time>', date('Y-m-d H:i:s'))->get()->row_array();
    }

    /**
     * 更新请假数据
     * @param $uid
     * @param $leave_id
     * @param $data
     * @return mixed
     */
    public function updateLeave($uid, $leave_id, $data)
    {
        return parent::$main_db->update('sty_user_leave', $data, array('id' => $leave_id, 'uid' => $uid, 'status' => 1, 'is_del' => 0));
    }

    /**
     * @param $uid
     * @param bool $ongoing TRUE 正在休学 FALSE 休学过
     * @return bool
     */
    public function isSuspending($uid, $ongoing = TRUE)
    {
        $now_time = date('Y-m-d H:i:s');
        $query = parent::$main_db->from('sty_user_suspend')->where(array('uid' => $uid, 'is_del' => 0));
        if ($ongoing) {
            $query->where(['status' => 1])->where('start_time<=', $now_time)->where('end_time>', $now_time);
        }
        if ($query->count_all_results()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 插入休学记录
     * @param $uid
     * @param $start_time
     * @param $end_time
     * @param $reason
     * @return mixed
     */
    public function addSuspend($uid, $start_time, $end_time, $reason)
    {
        return parent::$main_db->insert('sty_user_suspend', array('uid' => $uid, 'start_time' => date('Y-m-d 00:00:00', strtotime($start_time)), 'end_time' => date('Y-m-d 23:59:59', strtotime($end_time)), 'reason' => $reason, 'status' => 1));
    }

    /**
     * 休学详细
     * @param $uid
     * @return mixed
     */
    public function getUserSuspendInfo($uid)
    {
        return parent::$main_db->select('id, start_time, end_time, reason, status')->from('sty_user_suspend')
            ->where(array('uid' => $uid, 'is_del' => 0))
            ->get()->row_array();
    }

    /**
     * 更新休学数据
     * @param $uid
     * @param $leave_id
     * @param $data
     * @return mixed
     */
    public function updateSuspend($uid, $suspend_id, $data)
    {
        return parent::$main_db->update('sty_user_suspend', $data, array('id' => $suspend_id, 'uid' => $uid, 'status' => 1, 'is_del' => 0));
    }

    /**
     * 结束请假
     * @return mixed
     */
    public function getEndLeave($uid)
    {
        return parent::$main_db->select('id')->from('sty_user_leave')->where(array('uid' => $uid, 'status' => 1, 'end_time<=' => date('Y-m-d H:i:s'), 'is_del' => 0))->order_by('end_time', 'ASC')->get()->result_array();
    }

}