<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_model extends MY_Model
{
    /**
     * 获取用户已购商品信息
     * @param $uid
     * @param $goods_id
     * @return mixed
     */
    public function getGoodsInfo($uid, $goods_id)
    {
        return parent::$main_db->select('sug.is_activate, sug.activate_time, sug.expire, sug.unlimit_expire,
                sug.buy_info, sug.deal_price, sg.upgrade_cat, sg.name, sg.upgradeable, sg.identity,
                sug.upgrade_intention')->from('sty_user_goods sug')->join('sty_goods sg', 'sg.id = sug.goods_id', 'left')->where(array(
            'sug.uid' => $uid,
            'sug.goods_id' => $goods_id,
            'sug.is_del' => 0,
            'sg.is_del' => 0
        ))->limit(1)->get()->row_array();
    }

    /**
     * 用户某上课模式信息
     * @param $uid
     * @param int $type 1主修、2口语、3选修、4专修、5定制
     * @param bool $activate 是否只查找已激活未过期的商品
     * @return
     */
    public function getUsergoodsType($uid, $type = 0, $activate = 1)
    {
        $date = date('Y-m-d H:i:s');
        $query = parent::$main_db->select('sugi.goods_id,sug.goods_name,sugi.count,sugi.remain_count,sugi.unlimit_count, sugi.type,
            sug.is_activate,sug.activate_time,sug.expire,sug.unlimit_expire, sug.buy_info, sug.create_time, sug.id,
            sug.deal_price, sug.upgrade_from')->from('sty_user_goods AS sug')->join('sty_user_goods_info AS sugi', 'sug.uid=sugi.uid AND sug.goods_id=sugi.goods_id', 'left')->where(array(
            'sugi.uid' => $uid,
            'sug.uid' => $uid,
            'sugi.is_del' => 0,
            'sug.is_del' => 0
        ));

        if (intval($type) > 0) {
            $query->where('sugi.type', $type);
        }
        if (intval($activate) == 1) {
            $query->where('sug.is_activate', 1);
            $query->where("(sug.unlimit_expire=1 OR sug.expire>='" . $date . "')");
        }
        if (intval($activate) == -1) {
            $query->where('sug.is_activate', 0);
        }
        $query->order_by('sug.expire DESC');
        return $query->get()->result_array();
    }

    /**
     * 用户商品详细
     * @param $uid
     * @param null $start_time
     * @return mixed
     */
    public function getUserGoodsDetail($uid, $start_time = NULL)
    {
        $date = !empty($start_time) ? $start_time : date('Y-m-d H:i:s');
        return $this->selectUserGoodsObj($uid, [
            'is_activate' => 1,
            '_string' => "(unlimit_expire=1 OR expire>='" . $date . "')"
        ], ['buy_info'], ['expire DESC']);
    }

    /**
     * 用户已经购买的商品
     * @param $uid
     * @return mixed
     */
    public function userPurchasedGoods($uid)
    {
        $goodsList = $this->selectUserGoodsObj($uid);
        $purchased = [];
        foreach ($goodsList as $goods) {
            $purchased[] = $goods['goods_id'];
        }

        return $purchased;
    }

    /**
     * @param $uid
     * @param $type
     * @param array $result
     * @return int num 为 -1 代表不限次数
     */
    public function getUserAbleNum($uid, $type, $result = array())
    {
        if (empty($result)) {
            $result = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, [
                'ugi.type' => $type,
                'ug.is_activate' => 1,
                '_string' => "(ug.unlimit_expire = 1 OR ug.expire > '" . date('Y-m-d H:i:s') . "')"
            ], [
                'ugi.unlimit_count',
                'ugi.remain_count'
            ], ['ug.expire DESC']);
        }
        $unlimit_count = $num = 0;
        if (!empty($result)) {
            foreach ($result as $item) {
                if ($item['unlimit_count'] == 1) {
                    $unlimit_count = 1;
                    break;
                } else {
                    $num += intval($item['remain_count']);
                }
            }
        }
        if ($unlimit_count == 1) {
            $num = -1;
        } else {
            $num = $num > 0 ? $num : 0;
        }
        return $num;
    }

    /**
     * 获取课程体系有效期
     * @param $uid
     * @param $type
     * @param array $result
     * @return array
     */
    public function getUserCatgoryExpire($uid, $type, $result = array())
    {
        if (empty($result)) {
            $result = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, ['ugi.type' => $type], [
                'ug.unlimit_expire',
                'ug.expire'
            ], ['ug.expire DESC']);
        }
        $data = array('unlimit_expire' => 0, 'expire' => NULL, 'goods_id' => 0);
        if (!empty($result)) {
            foreach ($result as $item) {
                if ($item['unlimit_expire'] == 1) {
                    $data['unlimit_expire'] = 1;
                    break;
                } else {
                    if ($data['expire'] == NULL || (intval($item['expire']) && $item['expire'] > $data['expire'])) {
                        $data['expire'] = $item['expire'];
                        $data['goods_id'] = $item['goods_id'];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 激活商品
     *
     * @param $uid
     * @param $goodsId
     * @return array
     */
    public function activateGoods($uid, $goodsId)
    {
        $result = ['code' => 500, 'data' => [], 'msg' => '激活失败'];
        if (($isValid = $this->validation->validate('activate', ['uid' => $uid])) !== TRUE) {
            $result['msg'] = $isValid['msg'];
            return $result;
        }
        $this->load->library('thrift');
        $res = $this->thrift->service('Goods')->activateGoods($uid, $goodsId, 1);
        if($res->code === 200){
            $result['code'] = 200;
            $result['msg'] = '激活成功';
        }else{
            $result['code'] = $res->code;
            $result['msg'] = $res->msg;
            $result['data'] = $res->data;
        }
        return $result;
    }

    /**
     * 更新学管师数据
     * @param $uid
     * @param null $goods_id
     */
    public function save_teach_task($uid, $goods_id = NULL)
    {
        $where = [];
        $nowData = date('Y-m-d H:i:s');
        if (intval($goods_id) > 0) {
            $where['sug.goods_id'] = $goods_id;
        }
        $where['sug.is_activate'] = 1;
        $where['sg.business_type'] = 1;
        //商品信息
        $goods = $this->getUserGoodsDetial($uid, $where);
        if (!empty($goods)) {
            $save = [
                'goods_id' => $goods[0]['goods_id'],
                'activate_time' => $goods[0]['activate_time'],
                'expire' => $goods[0]['expire'],
                'unlimit_expire' => $goods[0]['unlimit_expire'],
                'longest_schedule_id' => 0
            ];
            $this->load->model('public/schedule_model');
            $schedule = $this->schedule_model->getUserScheduleDetial($uid, ['ss.class_mode' => 1], [], ['ss.end_time DESC'], [1]);
            if (!empty($schedule)) {
                $save['longest_schedule_id'] = $schedule['id'];
            }
            //未分配的学管师信息
            $teach = parent::$main_db->select('goods_id, longest_schedule_id')
                ->from('sty_teach_task_class')
                ->where([
                    'uid' => $uid,
                    'is_assign' => 0
                ])
                ->get()->row_array();
            if (!empty($teach)) {
                $save['modify_time'] = $nowData;
                if (intval($save['goods_id']) !== intval($teach['goods_id']) || intval($save['longest_schedule_id']) !== intval($teach['longest_schedule_id'])) {
                    return parent::$main_db->set($save)->where(['uid' => $uid, 'is_assign' => 0])->update('sty_teach_task_class');
                }
            } else {
                $save['uid'] = $uid;
                $save['create_time'] = $nowData;
                return parent::$main_db->insert('sty_teach_task_class', $save);
            }
        }
        return FALSE;
    }

    /**
     * 更新计划有效期
     * @param $uid
     * @param $plan_id
     * @param $now
     * @param $isUnlimit
     * @param $expire_time
     */
    public function updatePlanExpire($uid, $plan_id, $now, $isUnlimit, $expire_time)
    {
        $query = parent::$main_db->select('id, expire')->from('sty_user_plan')->where(array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'is_del' => 0
        ))->limit(1)->get();
        if ($userPlan = $query->row_array()) {
            $fields = array('modify_time' => $now);
            if ($isUnlimit) {
                //无限有效期商品
                $fields['unlimit_expire'] = 1;
            } elseif ($expire_time > $userPlan['expire']) {
                $fields['expire'] = $expire_time;
            } else {
                return;
            }
            parent::$main_db->set($fields)->where(array('id' => $userPlan['id']))->update('sty_user_plan');
        }
    }

    /**
     * 更新计划阶段有效期, 同时设置默认解锁阶段
     * @param $uid
     * @param $plan_id
     * @param $now
     * @param $plan_stage
     * @param $isUnlimit
     * @param $expire_time
     */
    public function updatePlanStageExpire($uid, $plan_id, $now, $plan_stage, $isUnlimit, $expire_time)
    {
        $query = parent::$main_db->select('id, expire, order, unlock_able, is_unlocked')->from('sty_user_plan_stage')->where(array(
            'uid' => $uid,
            'plan_id' => $plan_id,
            'is_del' => 0
        ))->where_in('plan_stage_id', $plan_stage)->get();
        $defaultUnlock = 0;
        $minOrder = $isUnlocked = FALSE;
        foreach ($query->result_array() as $planStage) {
            $fields = [];
            if ($isUnlimit) {
                $fields['unlimit_expire'] = 1;
            } elseif ($expire_time > $planStage['expire']) {
                $fields['expire'] = $expire_time;
            }
            if ($planStage['unlock_able'] == '0') {//之前不可解锁
                $fields['unlock_able'] = 1;
            }
            if (!empty($fields)) {
                $fields['modify_time'] = $now;
                parent::$main_db->set($fields)->where(array('id' => $planStage['id']))->update('sty_user_plan_stage');

                if ($minOrder === FALSE) {
                    $minOrder = intval($planStage['order']);
                    $defaultUnlock = $planStage['id'];
                    $isUnlocked = $planStage['is_unlocked'];
                } elseif (intval($planStage['order']) < $minOrder) {
                    $minOrder = intval($planStage['order']);
                    $defaultUnlock = $planStage['id'];
                    $isUnlocked = $planStage['is_unlocked'];
                }
            }

        }
        //将当前计划阶段中可解锁的排序最小的计划阶段解锁
        if (!empty($defaultUnlock) && $isUnlocked === '0') {
            parent::$main_db->set('is_unlocked', 1)->where('id', $defaultUnlock)->update('sty_user_plan_stage');
        }
    }

    /**
     * 获取用户已购买商品的次数统计
     * @param $uid
     * @return array
     */
    public function getUserGoodsCount($uid)
    {
        $goodsCount = array();
        $result = $this->getUserGoodsStatistical($uid, NULL, TRUE);
        foreach ($result as $goods) {
            if (!empty($goods['unlimit_count'])) {
                $goodsCount[$goods['type']]['unlimit'] = TRUE;
            } else {
                !isset($goodsCount[$goods['type']]['total']) && $goodsCount[$goods['type']]['total'] = 0;
                !isset($goodsCount[$goods['type']]['remain']) && $goodsCount[$goods['type']]['remain'] = 0;
                $goodsCount[$goods['type']]['total'] += $goods['count'];
                $goodsCount[$goods['type']]['remain'] += $goods['remain_count'];
            }
        }
        return $goodsCount;
    }

    /**
     * 获取用户已购产品列表
     * @param $uid
     * @return array
     */
    public function getPurchaseList($uid)
    {
        $goods = array();
        $now = time();
        $this->load->model('teaching_center/leave_model');
        $isSuspending = $this->leave_model->isSuspending($uid);
        $this->load->model('public/member_model');
        $hasValidOrder = $this->member_model->hasValidOrder($uid);
        $learnInfo = $this->member_model->getLearnInfo($uid);
        $result = $this->getUsergoodsType($uid, 0, 0);
        $totalTime = [];
        $this->load->library('thrift');
        foreach ($result as $item) {
            $goods[$item['goods_id']]['name'] = $item['goods_name'];
            $goods[$item['goods_id']]['is_activate'] = $item['is_activate'];
            $goods[$item['goods_id']]['unlimit_expire'] = !empty($item['unlimit_expire']);
            if ($item['is_activate'] === '1') {
                $goods[$item['goods_id']]['activate_time'] = strtotime($item['activate_time']);
                $goods[$item['goods_id']]['expire'] = strtotime($item['expire']);
            }
            $goods[$item['goods_id']]['create_time'] = strtotime($item['create_time']);
            $buyInfo = json_decode($item['buy_info'], TRUE);
            if (empty($buyInfo)) {
                $buyInfo = unserialize($item['buy_info']);
            }
            $goods[$item['goods_id']]['buy_info'] = $buyInfo;
            $goods[$item['goods_id']]['upgrade_from'] = $item['upgrade_from'];

            if (!empty($item['unlimit_count'])) {
                $goods[$item['goods_id']]['count_list'][$item['type']]['is_unlimit'] = 1;
            } else {
                $goods[$item['goods_id']]['count_list'][$item['type']]['total'] = intval($item['count']);
                $goods[$item['goods_id']]['count_list'][$item['type']]['remain'] = intval($item['remain_count']);
                $goods[$item['goods_id']]['count_list'][$item['type']]['used'] = intval($item['count']) - intval($item['remain_count']);
            }
            if (!isset($totalTime[$item['goods_id']])) {
                $totalTime[$item['goods_id']] = $this->thrift->service('Goods')->getRenewalTimeLen($item['id'], []);
            }
        }
        $this->load->helper('date');
        $this->load->model('public/protocol_model');
        $formal = FALSE;
        $goodsBaseInfo = $this->goodsBaseInfo(array_keys($goods));
        if (!empty($goodsBaseInfo)) {
            foreach ($goodsBaseInfo as $gbi) {
                if (intval($gbi['business_type']) !== 2 && intval($gbi['business_type']) !== 5) {
                    $formal = TRUE;
                    break;
                }
            }
            $this->load->model('public/common_model');
            $days = intval($this->common_model->getBaseSetValue('extension_card_days'));
            foreach ($goodsBaseInfo as $info) {
                $goods[$info['id']]['signed'] = 0;
                $goods[$info['id']]['protocol'] = MASTER_DOMAIN . 'main.php/Course/Protocol/index/goods_id/' . $info['id'] . '.html';
                if (!empty($goods[$info['id']]['upgrade_from'])) {
                    //升级信息
                    $from = $this->goodsBaseInfo($goods[$info['id']]['upgrade_from']);
                    $goods[$info['id']]['upgrade_from'] = $from['name'];
                }
                if ($goods[$info['id']]['is_activate'] == '0') {
                    //未激活
                    $goods[$info['id']]['status'] = 0;
                    $autoActivate = $goods[$info['id']]['create_time'] + $days * 24 * 3600;
                    $goods[$info['id']]['remain'] = diff_days($now, $autoActivate);

                    $buyInfo = $goods[$info['id']]['buy_info'];
                    $expire_num = intval($buyInfo['valid']['num']);
                    $expire_unit = intval($buyInfo['valid']['unit']);
                    $goods[$info['id']]['last_day'] = $this->getGoodsLastDate($expire_num, $expire_unit, 'Y/m/d', $totalTime[$info['id']]);
                    //休学中不可激活
                    $goods[$info['id']]['can_activate'] = $isSuspending ? FALSE : TRUE;
                } elseif ($now > $goods[$info['id']]['expire'] && empty($goods[$info['id']]['unlimit_expire'])) {
                    //已过期
                    $goods[$info['id']]['status'] = 1;
                } else {
                    //已激活
                    $goods[$info['id']]['status'] = 2;
                    //没休学且商品可升级并且不是达人卡学员才显示按钮
                    if (!$isSuspending && $info['upgradeable'] == '1' && $hasValidOrder && intval($learnInfo['user_identity']) !== 5) {
                        $goods[$info['id']]['upgradeable'] = 1;
                    }
                    if (empty($goods[$info['id']]['unlimit_expire'])) {
                        $goods[$info['id']]['remain'] = remain_days($now, $goods[$info['id']]['expire']);
                    }
                }
                //正式课
                $goods[$info['id']]['formal'] = TRUE;
                if ($formal === TRUE && (intval($info['business_type']) === 2 || intval($info['business_type']) === 5)) {
                    $goods[$info['id']]['formal'] = FALSE;
                }
                $goods[$info['id']]['can_exp'] = intval($info['business_type']) === 1 && intval($info['able_renewal']) === 1;
            }
        }
        if (!empty($goods)) {
            //设置签协议状态
            $userProtocol = $this->protocol_model->getUserProtocol($uid, array_keys($goods));
            foreach ($userProtocol as $protocol) {
                $goods[$protocol['goods_id']]['signed'] = $protocol['checkActive'];
            }
        }
        return $goods;
    }

    /**
     * 某个课程体系的总次数统计
     * @param $uid
     * @param $currType 1主修、2口语、3选修、4专修、5定制
     * @return array
     */
    public function curricularTotalAnalyze($uid, $currType)
    {
        $goodsList = $this->getUserGoodsStatistical($uid, $currType);
        $goodsIds = $data = $summary = ['total' => 0, 'used' => 0, 'remain' => 0];
        $hasUnlimitCount = FALSE;
        foreach ($goodsList as $index => $goods) {
            $goodsIds[$goods['goods_id']] = $index;
            $goodsList[$index]['name'] = $goods['goods_name'];
            $goodsList[$index]['expire'] = strtotime($goods['expire']);
            $goodsList[$index]['create_time'] = strtotime($goods['create_time']);
            $goodsList[$index]['used'] = intval($goods['count']) - intval($goods['remain_count']);
            if (!empty($goods['unlimit_count'])) {
                $hasUnlimitCount = TRUE;
            } else {
                $summary['total'] += $goods['count'];
                $summary['remain'] += $goods['remain_count'];
                $summary['used'] += $goods['count'] - $goods['remain_count'];
            }
        }
        $data['list'] = $goodsList;
        $data['summary'] = $summary;
        $data['hasUnlimitCount'] = $hasUnlimitCount;

        return $data;
    }

    /**
     * 某个课程体系的详细次数统计
     * @param $uid
     * @param $currType
     * @param $limit
     * @param $pageNum
     * @return array
     */
    public function curricularDetailAnalyze($uid, $currType, $limit, $pageNum)
    {
        $offset = ($pageNum - 1) * $limit;
        $selectField = '';
        parent::$main_db->join('sty_schedule ss', 'ss.id=sugca.schedule_id', 'left')->where(array(
            'sugca.uid' => $uid,
            'sugca.curricular_type' => $currType,
            'sugca.is_del' => 0,
            'sugca.type' => 1//只显示使用记录
        ));
        if ($currType !== 1) {//非主修课
            parent::$main_db->join('sty_schedule_lesson as ssle', 'ssle.id = sugca.schedule_lesson_id', 'left');
            $selectField = ', ssle.teacher_id, ssle.name AS lesson_name, ssle.alias';
        }

        $totalCount = parent::$main_db->count_all_results('sty_user_goods_count_audit AS sugca', FALSE);
        $data = parent::$main_db->select('sugca.action, sugca.num, sugca.description,
                sugca.create_time, ss.id AS schedule_id, ss.name' . $selectField)->order_by('sugca.create_time', 'DESC')->limit($limit, $offset)->get()->result_array();
        $result = $teachers = $scheduleIds = [];
        if (!empty($data)) {
            $this->load->model('public/schedule_model');
            foreach ($data as $index => $audit) {
                if (!empty($audit['schedule_id'])) {//老数据迁移之后没有schedule_id
                    //获取教师信息
                    if ($currType === 1) {//主修
                        if (!isset($teachers[$audit['schedule_id']])) {
                            $teachers[$audit['schedule_id']] = $this->schedule_model->getScheduleTeachers($audit['schedule_id']);
                        }
                        $data[$index]['teachers'] = array();
                        foreach ($teachers[$audit['schedule_id']] as $teacher) {
                            $data[$index]['teachers'][] = $teacher['name'];
                        }
                    } else {//非主修
                        if (!isset($teachers[$audit['teacher_id']])) {
                            $data[$index]['teachers'] = [];
                            $teacher_info = $this->schedule_model->getTeacherInfo($audit['teacher_id']);
                            if (!empty($teacher_info)) {
                                $teachers[$audit['teacher_id']] = $teacher_info;
                                $data[$index]['teachers'][] = $teachers[$audit['teacher_id']]['name'];
                            }
                        } else {
                            $data[$index]['teachers'][] = $teachers[$audit['teacher_id']]['name'];
                        }
                        //有别名使用别名
                        if (isset($audit['alias']) && !empty($audit['alias'])) {
                            $data[$index]['name'] = $audit['name'] . '：' . $audit['alias'];
                        } else {
                            $data[$index]['name'] = $audit['name'] . '：' . $audit['lesson_name'];
                        }
                    }
                }

                $data[$index]['create_time'] = strtotime($audit['create_time']);
                $data[$index]['num'] = intval($audit['num']);
                $data[$index]['desc'] = $audit['description'];
                $data[$index]['action_name'] = $audit['action'];
            }
        }
        $result['data'] = $data;
        $result['total_page'] = intval(ceil($totalCount / $limit));
        $result['current_page'] = $pageNum;
        return $result;
    }

    /**
     * 转换次数变化原因字符串
     * @param $original
     * @param $number
     * @return string
     */
    public function translateDesc($original, $number)
    {
        $number = abs(intval($number));
        $str = get_options('curricular_audit_desc', $original);
        if ($str) {
            return sprintf($str, $number);
        } else {
            return $original;
        }
    }

    /**
     * 次数使用明细操作说明
     * @param $action
     * @return bool|mixed|string
     */
    public function translateAction($action)
    {
        $str = get_options('curricular_action_desc', $action);
        return $str ? $str : '';
    }

    /**
     * 获取用户已购商品中未激活和已激活未过期的商品
     * @param $uid
     * @param null $type 1主修、2口语、3选修、4专修、5定制
     * @param bool $isActivate
     * @return mixed
     */
    public function getUserGoodsStatistical($uid, $type = NULL, $isActivate = FALSE)
    {
        $now = date('Y-m-d H:i:s');
        $where = array('sugi.uid' => $uid, 'sugi.is_del' => 0, 'sug.is_del' => 0);
        if ($type !== NULL) {
            $where['sugi.type'] = $type;
        }

        parent::$main_db->select('sugi.goods_id, sugi.remain_count, sugi.unlimit_count, sugi.type, sug.goods_name, sugi.count,
            sug.is_activate, sug.expire, sug.unlimit_expire, sug.create_time')->from('sty_user_goods_info AS sugi')->join('sty_user_goods AS sug', 'sug.uid = sugi.uid AND sug.goods_id = sugi.goods_id', 'left')->where($where)->order_by('sug.is_activate DESC, sug.expire ASC');

        if ($isActivate) {
            parent::$main_db->where("sug.is_activate = 1 AND (sug.expire > '$now' OR sug.unlimit_expire = 1)");
        } else {
            parent::$main_db->where("(sug.is_activate = 0 OR (sug.is_activate = 1 AND (sug.expire > '$now' OR sug.unlimit_expire = 1)))");
        }

        return parent::$main_db->get()->result_array();
    }

    /**
     * 获取商品基本信息
     * @param array|int $goodsIds 商品id
     * @param bool $isOpen 是否获取已上架的
     * @return array
     */
    public function goodsBaseInfo($goodsIds, $isOpen = FALSE)
    {
        $idsArray = FALSE;
        if (is_array($goodsIds)) {
            $idsArray = TRUE;
        }
        if (empty($goodsIds)) {
            return array();
        }
        parent::$main_db->select('id, name, discount_price, upgrade_cat, upgradeable, business_type, able_renewal')->from('sty_goods')->where('is_del', 0);

        if ($isOpen) {
            parent::$main_db->where('is_open', 1);
        }

        if ($idsArray) {
            return parent::$main_db->where_in('id', $goodsIds)->get()->result_array();
        } else {
            return parent::$main_db->where('id', $goodsIds)->get()->row_array();
        }
    }

    /**
     * 根据有效期先后扣除次数
     * @param $uid
     */
    public function deductNum($uid, $type, $schedule_id = 0, $lesson_id = 0, $msg = NULL, $start_time = NULL)
    {
        $expire = NULL;
        $goods_id = 0;
        $unlimit_count = 0;
        $goodsInfo = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, [
            'ugi.type' => $type,
            'ug.is_activate' => 1,
            '_string' => "(ug.unlimit_expire = 1 OR ug.expire > '" . date('Y-m-d H:i:s') . "')"
        ], [
            'ug.expire',
            'ug.unlimit_expire',
            'ugi.unlimit_count',
            'ugi.remain_count',
            'ugi.count'
        ], ['ug.expire DESC']);
        if (!empty($goodsInfo)) {
            foreach ($goodsInfo as $item) {
                if ($item['unlimit_count'] == 1) {
                    $goods_id = 0;
                    $unlimit_count = 0;
                    break;
                } else {
                    if (($expire == NULL || $item['expire'] < $expire) && ($start_time == NULL || $item['expire'] > $start_time)) {
                        if ($item['remain_count'] > 0) {
                            $expire = $item['expire'];
                            $goods_id = intval($item['goods_id']);
                            $unlimit_count = intval($item['unlimit_count']);
                        }
                    }
                }
            }
        }
        if ($goods_id > 0 && $unlimit_count != 1) {
            //更新
            $save = parent::$main_db->set('remain_count', 'remain_count-1', FALSE)->set('modify_time', date('Y-m-d H:i:s'))->where(array(
                'uid' => $uid,
                'goods_id' => $goods_id,
                'type' => $type,
                'is_del' => 0
            ))->where('remain_count>', 0)->update('sty_user_goods_info');
            if ($save) {
                if (intval($type) === 1) {
                    $msg = $msg ? $msg : 'MAJOR_REMOVE';
                } else {
                    $msg = $msg ? $msg : 'NOMAJOR_REMOVE';
                }
                if ($msg == 'USER_NOT_ENOUGH_ADD') {
                    $modify_user = 0;
                } else {
                    $modify_user = $uid;
                }
                $num = -1;
                $data = array(
                    'uid' => $uid,
                    'goods_id' => $goods_id,
                    'type' => 1,
                    'curricular_type' => $type,
                    'num' => $num,
                    'create_time' => date('Y-m-d H:i:s'),
                    'modify_user' => $modify_user
                );
                $data['action'] = get_options('curricular_action_desc', $msg);
                $data['description'] = sprintf(get_options('curricular_audit_desc', $msg), abs($num));
                $data['schedule_id'] = $schedule_id;
                $data['schedule_lesson_id'] = $lesson_id;
                $this->goodsNumChangeRecord($data);
            }
        }
    }

    /**
     * 归还次数
     */
    public function giveBackNum($uid, $type, $schedule_id = 0, $lesson_id = 0, $msg = NULL)
    {
        $goods_info = array();
        $goodsInfo = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, [
            'ugi.type' => $type,
            'ug.is_activate' => 1,
            '_string' => "(ug.unlimit_expire = 1 OR ug.expire > '" . date('Y-m-d H:i:s') . "')"
        ], [
            'ug.expire',
            'ug.unlimit_expire',
            'ugi.unlimit_count',
            'ugi.remain_count',
            'ugi.count'
        ], ['ug.expire DESC']);
        if (!empty($goodsInfo)) {
            foreach ($goodsInfo as $item) {
                if ($item['unlimit_count'] == 1) {
                    $goods_info = array();
                    break;
                } else {
                    $goods_info[$item['goods_id']] = $item;
                }
            }
        }
        $give_back_num = FALSE;
        if (!empty($goods_info)) {
            $audit = $this->goodsNumChangeInfo($uid, $type, $schedule_id, $lesson_id);
            if (!empty($audit)) {
                $goods_id = $audit['goods_id'];
                if (isset($goods_info[$goods_id]) && !empty($goods_info[$goods_id])) {
                    if ($goods_info[$goods_id]['unlimit_count'] != 1 && $goods_info[$goods_id]['count'] > $goods_info[$goods_id]['unlimit_count'] && $goods_info[$goods_id]['unlimit_count'] >= 0) {
                        $give_back_num = TRUE;
                    }
                }
            }
        }
        if ($give_back_num) {
            $save = parent::$main_db->set('remain_count', 'remain_count+1', FALSE)->set('modify_time', date('Y-m-d H:i:s'))->where(array(
                'uid' => $uid,
                'goods_id' => $goods_id,
                'type' => $type,
                'is_del' => 0
            ))->where('remain_count>=', 0)->update('sty_user_goods_info');

            if ($save) {
                if (intval($type) === 1) {
                    $msg = $msg ? $msg : 'MAJOR_ADD';
                } else {
                    $msg = $msg ? $msg : 'NOMAJOR_ADD';
                }
                if ($msg == 'USER_NOT_ENOUGH_ADD') {
                    $modify_user = 0;
                } else {
                    $modify_user = $uid;
                }
                $num = 1;
                $data = array(
                    'uid' => $uid,
                    'goods_id' => $goods_id,
                    'type' => 1,
                    'curricular_type' => $type,
                    'num' => $num,
                    'create_time' => date('Y-m-d H:i:s'),
                    'modify_user' => $modify_user
                );
                $data['action'] = get_options('curricular_action_desc', $msg);
                $data['description'] = sprintf(get_options('curricular_audit_desc', $msg), $num);
                $data['schedule_id'] = $schedule_id;
                $data['schedule_lesson_id'] = $lesson_id;
                $this->goodsNumChangeRecord($data);
            }
        }
    }

    /**
     * 次数变更记录
     * @param $inserts
     * @return bool
     */
    public function goodsNumChangeRecord($inserts)
    {
        if (!empty($inserts)) {
            return parent::$main_db->insert('sty_user_goods_count_audit', $inserts);
        }
        return FALSE;
    }

    /**
     * 是否扣除用户阶段课程次数
     * @param $uid
     * @param $schedule_id
     * @return mixed
     */
    public function deductUserScheduleNum($uid, $schedule_id)
    {
        return parent::$main_db->from('sty_user_goods_count_audit')->where(array(
            'uid' => $uid,
            'schedule_id' => $schedule_id,
            'schedule_lesson_id' => 0,
            'curricular_type' => 1,
            'is_del' => 0
        ))->count_all_results();
    }

    /**
     * 得到最后一次添加的goods_id
     * @param $uid
     * @param $type
     * @param $schedule_id
     * @param $lesson_id
     * @return mixed
     */
    public function goodsNumChangeInfo($uid, $type, $schedule_id, $lesson_id)
    {
        return parent::$main_db->select('goods_id')->from('sty_user_goods_count_audit')->where(array(
            'uid' => $uid,
            'action' => 0,
            'curricular_type' => $type,
            'schedule_id' => $schedule_id,
            'schedule_lesson_id' => $lesson_id,
            'is_del' => 0
        ))->order_by('create_time DESC')->limit(1)->get()->row_array();
    }

    /**
     * 计算可用商品的最大有效期
     * @param array $activation_able
     * @return array
     */
    public function getAblegoodsExpire($activation_able = array())
    {
        $data = array();
        if (!empty($activation_able)) {
            $data['unlimit_expire'] = 0;
            $data['max_expire'] = '';
            foreach ($activation_able as $item) {
                if ($item['unlimit_expire'] == 1) {
                    $data['unlimit_expire'] = 1;
                    $data['max_expire'] = '';
                    break;
                }
                if (($data['max_expire'] == '' || $item['expire'] > $data['max_expire'])) {
                    $data['max_expire'] = $item['expire'];
                }
            }
        }
        return $data;
    }

    /**
     * 更新商品
     * @param $uid
     * @param $goods_id
     * @param $data
     * @return mixed
     */
    public function updateUserGoods($uid, $goods_id, $data)
    {
        return parent::$main_db->update('sty_user_goods', $data, array(
            'uid' => $uid,
            'goods_id' => $goods_id,
            'is_activate' => 1,
            'is_del' => 0
        ));
    }

    /**
     * 插入商品有效期变更记录
     * @param $data
     * @return mixed
     */
    public function addUserGoodsExpireChange($data)
    {
        return parent::$main_db->insert('sty_user_goods_expire_change', $data);
    }

    /**
     * 获取商品的初始课程体系总次数
     * @param array $ids
     */
    public function goodsOriginalCount(array $ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        return parent::$main_db->select('goods_id, category, class_num, class_num_nolimit')->from('sty_goods_project')->where_in('goods_id', $ids)->where(array(
            'is_open' => 1,
            'is_del' => 0
        ))->get()->result_array();
    }

    /**
     * 根据商品的有效期获取商品的到期日期
     * @param $num
     * @param $unit
     * @param $format
     * @param $renewal
     * @return string
     */
    public function getGoodsLastDate($num, $unit, $format, $renewal = 0)
    {
        $now = new DateTime();
        $expire_str = "+{$num} " . get_options('expire_unit', $unit);
        //按天设定有效期
        $date = $now->modify($expire_str);
        if ($renewal > 0) {
            return $date->modify("+{$renewal} month")->modify('-1 day')->format($format);
        } else {
            return $date->modify('-1 day')->format($format);
        }
    }

    /**
     * @param $uid
     * @return mixed
     * 获取用户购买的正式课商品
     */
    public function getUserGoodsDetial($uid, array $w = [])
    {
        $this->load->model('public/common_model');
        $where = ['sug.uid' => $uid, 'sug.is_del' => 0, 'sg.is_del' => 0];
        $field = 'sug.goods_id, sug.expire, sug.unlimit_expire, sug.activate_time, sug.show_tip, sg.name, sg.business_type';
        if (!empty($w)) $where = array_merge($where, $w);
        $join = ['joinTable' => 'sty_goods AS sg', 'joinCond' => 'sg.id = sug.goods_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_user_goods AS sug', [
            'field' => $field,
            'where' => $where
        ], [$join]);
    }

    /**
     * 用户一对一vip课程
     * @return mixed
     */
    public function getUserVipCourse($uid)
    {
        return parent::$minor_db->select('coursename,sdate,edate')->from('zd_my_courses_vip')->where(['uid' => $uid])->order_by('edate DESC')->get()->result_array();
    }

    /**
     * 未激活商品数
     * @param $uid
     * @return mixed
     */
    public function unActivationGoods($uid)
    {
        return $this->selectUserGoodsObj($uid,
            ['is_activate' => 0],
            ['buy_info']
        );
    }

    /**
     * 用户商品有效期和次数信息
     * @param $uid
     * @param $catgory
     * @return array
     */
    public function getUserGoodsExpireAndNumInfo($uid, $catgory)
    {
        $data = $activate = $un_activate = $un_expire = $expire_arr = array();
        $is_activate = $is_unexpire = $unlimit_expire = $unlimit_count = $remain_count = $goods_id = 0;
        $expire = NULL;
        $nowtime = date('Y-m-d H:i:s');
        $lab = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, ['ugi.type' => $catgory], [
            'ug.is_activate',
            'ug.unlimit_expire',
            'ug.expire',
            'ugi.unlimit_count',
            'ugi.remain_count',
            'ugi.count'
        ], ['ug.expire DESC']);
        if (!empty($lab)) {
            foreach ($lab as $v) {
                if ($v['is_activate'] == 1) {
                    $is_activate = 1;
                    if ($v['unlimit_expire'] == 1 || $v['expire'] >= $nowtime) {
                        $activate[] = $v;
                    } else {
                        $un_expire[] = $v;
                    }
                } else {
                    $un_activate[] = $v;
                }
            }
            $num = 0;
            if ($is_activate == 1) {
                //已激活
                if (!empty($activate)) {
                    $num = $this->getUserAbleNum($uid, $catgory, $activate);
                    $expire_arr = $this->getUserCatgoryExpire($uid, $catgory, $activate);
                } else {
                    $is_unexpire = 1;
                    $expire_arr = $this->getUserCatgoryExpire($uid, $catgory, $un_expire);
                }
            } else {
                //未激活
                $num = $this->getUserAbleNum($uid, $catgory, $lab);
            }
            if (!empty($expire_arr)) {
                if ($expire_arr['unlimit_expire'] == 1) {
                    $unlimit_expire = 1;
                } else {
                    $expire = $expire_arr['expire'];
                    $goods_id = $expire_arr['goods_id'];
                }
            }
            if (intval($num) === -1) {
                $unlimit_count = 1;
            } else {
                $remain_count = $num;
            }
            $data['is_activate'] = $is_activate;
            $data['is_unexpire'] = $is_unexpire;
            $data['unlimit_expire'] = $unlimit_expire;
            $data['unlimit_count'] = $unlimit_count;
            $data['remain_count'] = $remain_count;
            $data['expire'] = $expire;
            $data['goods_id'] = $goods_id;
        }
        return $data;
    }

    /**
     * 用户商品状态
     * @param $uid
     * @param null $catgory
     * @return array hasGoods 有购买商品 hasExpire 有有效期 hasUnActivate 存在未激活商品 allUnActivate 全部未激活
     */
    public function getUserGoodsStatus($uid, $catgory = NULL)
    {
        $data = ['hasGoods' => FALSE, 'hasExpire' => FALSE, 'hasUnActivate' => FALSE, 'allUnActivate' => TRUE];
        if (!empty($catgory) && intval($catgory) > 0) {
            $goods = $this->selectUserGoodsJoinUserGoodsInfoObj($uid, ['ugi.type' => $catgory], [
                'ug.is_activate',
                'ug.unlimit_expire',
                'ug.expire'
            ]);
        } else {
            $goods = $this->selectUserGoodsObj($uid);
        }
        if (!empty($goods)) {
            $data['hasGoods'] = TRUE;
            foreach ($goods as $item) {
                if (intval($item['is_activate']) === 1) {
                    $data['allUnActivate'] = FALSE;
                    if (intval($item['unlimit_expire']) === 1 || $item['expire'] > date('Y-m-d H:i:s')) {
                        $data['hasExpire'] = TRUE;
                        break;
                    }
                } else {
                    $data['hasUnActivate'] = TRUE;
                }
            }
        }
        return $data;
    }

    /**
     * 查询user_goods表对象
     * @param $uid
     * @param array $w
     * @param array $f
     * @param array $o
     * @return mixed
     */
    public function selectUserGoodsObj($uid, array $w = [], array $f = [], array $o = [])
    {
        $this->load->model('public/common_model');
        $field = ['goods_id', 'is_activate', 'expire', 'unlimit_expire'];
        $where = ['uid' => $uid, 'is_del' => 0];
        if (!empty($w)) {
            $where = array_merge($where, $w);
        }
        if (!empty($f)) {
            $field = array_merge($field, $f);
        }
        $result = $this->common_model->selectObjData('sty_user_goods', [
            'where' => $where,
            'field' => $field,
            'order' => $o
        ]);
        return $result;
    }


    /**
     * 加入阶段课程时激活流程控制
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @return array
     */
    public function activationProcess($uid, $plan_id, $plan_stage_id, $joined = FALSE)
    {
        $res = ['code' => 0];
        $this->load->model('public/plan_stage_model');
        $plan_stage = $this->plan_stage_model->getUserStageInfo($uid, $plan_id, $plan_stage_id);
        if (!empty($plan_stage)) {
            $res['code'] = 200;
            if (!(intval($plan_stage['unlimit_expire']) === 1 || $plan_stage['expire'] > date('Y-m-d H:i:s'))) {
                //已过期
                $res['code'] = 221;
                //未激活商品数
                $un_goods = $this->unActivationGoods($uid);
                $count = intval(count($un_goods));
                $res['num'] = $count;
                $res['expire_str'] = '';
                if ($count > 1 && $joined === FALSE) {
                    $res['code'] = 283;
                }
                if ($count === 1) {
                    $buyInfo = json_decode($un_goods[0]['buy_info'], TRUE);
                    if (empty($buyInfo)) {
                        $buyInfo = unserialize($un_goods[0]['buy_info']);
                    }
                    $res['goods_id'] = $un_goods[0]['goods_id'];
                    //当前计划阶段是否存在于未激活商品中
                    if (!empty($buyInfo) && isset($buyInfo['plan_stage'][$plan_id]) && !empty($buyInfo['plan_stage'][$plan_id])) {
                        if (in_array($plan_stage_id, $buyInfo['plan_stage'][$plan_id])) {
                            $num = intval($buyInfo['valid']['num']);
                            $unit = intval($buyInfo['valid']['unit']);
                            $expire_unit = get_options('expire_unit');
                            if ($num > 0 && $unit > 0 && isset($expire_unit[$unit])) {
                                $end_time = strtotime('+' . $num . ' ' . $expire_unit[$unit]) - 86400;
                                $res['expire_str'] = date('Y/m/d') . '-' . date('Y/m/d', $end_time);
                            }
                            $res['code'] = 283;
                        }
                    }
                }
            }
            if ($res['code'] === 200) {
                $res['planStage'] = $plan_stage;
            }
        }
        return $res;
    }

    /**
     * 查询表user_goods join user_goods_info 对象
     * @param $uid
     * @param array $w
     * @param array $f
     * @param array $order
     * @param array $limit
     * @return mixed
     */
    public function selectUserGoodsJoinUserGoodsInfoObj($uid, array $w = [], array $f = [], array $order = [], array $limit = [])
    {
        $this->load->model('public/common_model');
        $where = ['ug.uid' => $uid, 'ugi.uid' => $uid, 'ug.is_del' => 0, 'ugi.is_del' => 0];
        $field = ['ug.goods_id'];
        if (!empty($w)) $where = array_merge($where, $w);
        if (!empty($f)) $field = array_merge($field, $f);
        $join = ['joinTable' => 'sty_user_goods_info as ugi', 'joinCond' => 'ugi.goods_id = ug.goods_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_user_goods as ug', [
            'field' => $field,
            'where' => $where,
            'order' => $order,
            'limit' => $limit
        ], [$join]);
    }
}
