<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/common_model');
    }

    public static $staff_uids = [];

    /**
     * 查询用户信息
     * @param $uid
     * @return mixed
     */
    public function getUserMemberInfo($uid)
    {
        return parent::$minor_db
            ->select('uid, username, open_id, avatar, version')
            ->from('jh_common_member')
            ->where('uid', $uid)
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 查询用户信息
     * @param $name
     * @return mixed
     */
    public function getUserMemberInfoByName($name)
    {
        return parent::$minor_db
            ->select('uid, username')
            ->from('jh_common_member')
            ->where('username', $name)
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 查询用户信息
     * @param $openId
     * @return mixed
     */
    public function getUserMemberInfoByOpenId($openId)
    {
        return parent::$minor_db
            ->select('uid, username, mobile')
            ->from('jh_common_member')
            ->where('open_id', $openId)
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 获取用户加密后的密码
     * @param $uid
     * @return string
     */
    public function getUserPwd($uid)
    {
        $ucdb = $this->load->database('uc', TRUE);
        $user = $ucdb->select('password')
            ->from('uc_members')
            ->where('uid', $uid)
            ->get()->row_array();
        if (empty($user)) {
            return '';
        } else {
            return $user['password'];
        }
    }

    /**
     * 获取用户相关联信息
     * @param $uid
     * @return mixed
     */
    public function getUserMessage($uid)
    {
        $this->load->library('thrift');
        return $this->thrift->service('User')->getUserMessageInfo($uid);
    }


    /**
     * 用户积分相关信息
     */
    public function getUserMemberCount($uid)
    {
        return parent::$minor_db->select('studyscoreTotal,studyscore')
            ->from('jh_common_member_count')
            ->where('uid', $uid)
            ->limit(1)
            ->get()->row_array();
    }

    /**
     * 获取班主任信息
     * @param $uid
     * @return array
     */
    public function getUserTeacherInfo($uid)
    {
        $result = parent::$minor_db
            ->select('jcm.uid, jcm.username, jcm.mobile, jcmp.qq')
            ->from('jh_common_member_profile_stuff AS jcmps')
            ->join('jh_common_member AS jcm', 'jcm.username=jcmps.stuffname', 'left')
            ->join('jh_common_member_profile AS jcmp', 'jcmp.uid=jcm.uid', 'left')
            ->where('jcmps.uid', $uid)
            ->where('jcmps.type', 'sa')
            ->limit(1)
            ->get()->row_array();
        $result['uid'] = isset($result['uid']) && !empty($result['uid']) ? intval($result['uid']) : 318417;
        $result['username'] = isset($result['username']) && !empty($result['username']) ? addslashes($result['username']) : '小道';
        $result['mobile'] = isset($result['mobile']) && !empty($result['mobile']) ? addslashes($result['mobile']) : $this->common_model->getBaseSetValue('content_phone');
        $result['qq'] = isset($result['qq']) && !empty($result['qq']) ? addslashes($result['qq']) : '800118811';
        return $result;
    }

    /**
     * 批量获取用户名，用户级别
     * @param array $ids
     * @return mixed
     */
    public function getNameLevel(array $ids)
    {
        $names = parent::$minor_db->select('m.uid, m.username, mu.user_level')
            ->from('jh_common_member m')
            ->join('zd_netschool.message_user mu', 'm.uid=mu.uid', 'left')
            ->where_in('m.uid', $ids)
            ->get()->result_array();
        return $names;
    }

    /**
     * 获取用户的真是姓名和手机
     * @param $uid
     */
    public function realNamePhone($uid)
    {
        return parent::$minor_db->select('jcmp.realname, jcm.mobile')
            ->from('jh_common_member_profile jcmp')
            ->join('jh_common_member jcm', 'jcmp.uid=jcm.uid', 'left')
            ->where(array(
                'jcmp.uid' => $uid
            ))
            ->get()->row_array();
    }

    /**
     * 获取用户基本信息
     * @param $uid
     * @return mixed
     */
    public function getBaseInfo($uid)
    {
        $result['currency'] = $this->getCurrency($uid);
        $result['isTeacher'] = $this->isTeacher($uid);
        $result['unPaidNum'] = $this->getUnpaidOrder($uid);
        $result['score'] = $this->getScore($uid);
        $result['sms'] = $this->getNewSms($uid);
        $result['avatar'] = avatar($uid, 'small');
        $result['score_limit'] = intval($this->overScoreLimit($uid));
        $result['coupon'] = $this->getCoupon($uid);
        return $result;
    }

    /**
     * 获取用户未读消息个数
     * @param $uid
     * @return int
     */
    public function getNewSms($uid)
    {
        $this->load->library('thrift');
        $obj = $this->thrift->service('User')->getUnreadMessageCount($uid,$this->passport->sessionId());
        return $obj->data["num"];
    }

    /**
     * 获取用户早元早点
     * @param $uid
     * @return array
     */
    public function getCurrency($uid)
    {
        $result = parent::$minor_db->select('extcredits2 AS point, extcredits8 AS yuan')
            ->from('jh_common_member_count')
            ->where('uid', $uid)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($result)) {
            $result = array(
                'point' => 0,
                'yuan' => 0
            );
        }
        return $result;
    }

    /**
     * 判断用户是否是教师
     * @param $uid
     * @return bool
     */
    public function isTeacher($uid)
    {
        $teacherGroup = get_options('teacher_groups');
        $result = parent::$minor_db->select('cpgroupid')
            ->from('jh_common_zdmis_admincp_member')
            ->where('uid', $uid)
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($result)) {
            $groupId = $result['cpgroupid'];
            if (in_array($groupId, $teacherGroup)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * 获取用户三天内未支付订单数量
     * @param $uid
     * @return mixed
     */
    public function getUnpaidOrder($uid)
    {
        //只统计最近三天的未支付订单
        $start = time() - 3600;
        $result = parent::$minor_db->from('zd_class.netschool_pay_order as npo')
            ->join('zd_class.netschool_pay_order_info as npi', 'npo.po_sid = npi.po_sid', 'left')
            ->where(
                array(
                    'npo.po_uid' => $uid,
                    'npo.status' => 0,
                    'npo.po_submittime >=' => $start,
                    'npo.order_type' => 2,
                    'npi.is_known' => 0,
                    'npi.is_del' => 0,
                )
            )->count_all_results();
        return $result;
    }

    /**
     * 获取用户可用学分，总学分
     * @param $uid
     * @return mixed
     */
    public function getScore($uid)
    {
        $result = parent::$minor_db->select('studyscoreTotal as total, studyscore as available')
            ->from('jh_common_member_count')
            ->where('uid', $uid)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($result)) {
            $result = array(
                'total' => 0,
                'available' => 0
            );
        }
        return $result;
    }

    /**
     * 增加用户可用学分，总学分
     * @param $uid
     * @param $score
     */
    public function incScore($uid, $score)
    {
        return parent::$minor_db->set('studyscoreTotal', 'studyscoreTotal + ' . intval($score), FALSE)
            ->set('studyscore', 'studyscore + ' . intval($score), FALSE)
            ->where('uid', $uid)
            ->update('jh_common_member_count');
    }

    /**
     * 增加用户奖学金
     * @param $uid
     * @param $score
     * @return bool
     */
    public function incScholarship($uid, $score)
    {
        if (!$this->scholarshipAccess($uid)) {
            return TRUE;
        }
        //获取奖学金方案
        $planID = get_options('scholarship_plan');
        $plan = parent::$main_db->select('title, score2cash')
            ->from('scholarship_plan')
            ->where('planID', $planID)
            ->get()->row_array();
        if (empty($plan)) {
            return FALSE;
        }

        $currentScore = $this->getScore($uid);
        $total = $currentScore['total'];
        $planRule = unserialize($plan['score2cash']);
        foreach ($planRule as $rule) {
            $ruleScore = intval($rule['score']);
            if ($total < $ruleScore && $total + $score >= $ruleScore) {
                $user = $this->getUserMemberInfo($uid);
                $data = [
                    'UID' => $uid,
                    'planID' => $planID,
                    'username' => $user['username'],
                    'planTile' => $plan['title'],
                    'score' => $ruleScore,
                    'cash' => $rule['cash'],
                    'dateline' => time(),
                ];
                parent::$main_db->insert('scholarship_score', $data);
                parent::$minor_db->set('extcredits7', 'extcredits7 + ' . intval($rule['cash']), FALSE)
                    ->where('uid', $uid)
                    ->update('jh_common_member_count');
                break;
            } elseif ($total + $score < $ruleScore) {
                break;
            }
        }
        return TRUE;
    }

    /**
     * 判断用户时候能够增加奖学金
     * @param $uid
     * @return bool
     */
    public function scholarshipAccess($uid)
    {
        $exist = parent::$main_db->where([
            'uid' => $uid,
            'expire >' => date('Y-m-d H:i:s')
        ])->from('sty_user_scholarship')->count_all_results();

        return !empty($exist);
    }

    /**
     * 更新用户报到时间
     * @param $uid
     */
    public function updateReportTime($uid)
    {
        return parent::$main_db->update('message_user', [
            'report_dateline' => time(),
            'updatedate' => time()
        ], ['uid' => $uid]);
    }

    /**
     * 暂时取消
     * 查询用户是否有符合升级条件的订单数据
     * 规定日期后(暂定2016/9/6 零点)
     * @param $uid
     * @return bool
     */
    public function hasValidOrder($uid)
    {
        return TRUE;
    }

    /**
     * 验证是否是内部员工
     * @param $uid
     * @return bool
     */
    public function validateIsStaff($uid)
    {
        $is_staff = FALSE;
        if (in_array($uid, self::$staff_uids)) {
            $is_staff = TRUE;
        } else {
            $staff = parent::$minor_db->from('jh_common_admincp_member')
                ->where(['uid' => $uid])
                ->count_all_results();
            if ($staff > 0) {
                $is_staff = TRUE;
                self::$staff_uids[] = $uid;
            }
        }
        return $is_staff;
    }

    /**
     * 获取用户当天已经增加的学分数量
     * @param $uid
     * @return int
     */
    public function todayIncScore($uid)
    {
        $begin = date('Y-m-d 00:00:00');
        $result = parent::$main_db->select_sum('score')
            ->from('sty_user_score_log')
            ->where([
                'uid' => $uid,
                'create_time >' => $begin
            ])->where_in('type', [1, 2])->get()->row_array();
        if ($result) {
            return intval($result['score']);
        } else {
            return 0;
        }
    }

    /**
     * 用户当天是否已经超过学分增加限制, 30分
     * @param $uid
     * @return bool
     */
    public function overScoreLimit($uid)
    {
        $score = $this->todayIncScore($uid);
        if ($score >= 30) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * message_user用户有效期更新
     * @param $uid
     * @return bool
     */
    public function goodsExpireMessageUserUpdate($uid)
    {
        $this->load->model('public/goods_model');
        $about_time = $class_end_time = '';
        $result = $unlimit_expire = FALSE;
        $active_goods = $this->goods_model->getUserGoodsDetial($uid, [
            'sug.is_activate' => 1,
            'sg.is_active' => 1
        ]);
        if (!empty($active_goods)) {
            foreach ($active_goods as $av) {
                if (intval($av['unlimit_expire']) === 1) {
                    //100 年以后
                    $century = date('Y-m-d 23:59:59', strtotime('+100 year'));
                    $about_time = $century;
                    $class_end_time = $century;
                    $unlimit_expire = TRUE;
                    break;
                }
                if (!empty($av['expire'])) {
                    if (empty($class_end_time) || $av['expire'] > $class_end_time) {
                        $class_end_time = $av['expire'];
                    }
                    if ($av['expire'] >= date('Y-m-d H:i:s') && (empty($about_time) || $av['expire'] < $about_time)) {
                        $about_time = $av['expire'];
                    }
                }
            }
        }
        if ($unlimit_expire !== TRUE) {
            $vip_course = $this->goods_model->getUserVipCourse($uid);
            if (!empty($vip_course)) {
                foreach ($vip_course as $cv) {
                    if (!empty($cv['edate']) && strtotime($cv['edate'])) {
                        $end_date = date('Y-m-d 23:59:59', strtotime($cv['edate']));
                        if (empty($class_end_time) || $end_date > $class_end_time) {
                            $class_end_time = $end_date;
                        }
                        if ($end_date >= date('Y-m-d H:i:s') && (empty($about_time) || $end_date < $about_time)) {
                            $about_time = $end_date;
                        }
                    }
                }
            }
        }
        if (!empty($class_end_time)) {
            if (empty($about_time)) $about_time = $class_end_time;
            //更新操作
            $result = parent::$main_db->update('message_user', [
                'validity_dateline' => strtotime($about_time),
                'class_endtime' => strtotime($class_end_time),
                'updatedate' => time()
            ], ['uid' => $uid]);
        }
        return $result;
    }

    /**
     * 用户学分增加
     * @param $params
     * @internal param $uid
     * @internal param $schedule_id
     * @internal param $type
     */
    public function incUserScore(array $params)
    {
        if ($params['score'] == 0) {
            return;
        }

        $data = array(
            'uid' => $params['uid'],
            'type' => $params['score_type'],
            'sub_id' => $params['sub_id'],
            'score' => $params['score'],
            'schedule_id' => $params['schedule_id'],
            'schedule_lesson_id' => $params['schedule_lesson_id'],
            'plan_id' => $params['plan_id'],
            'plan_stage_id' => $params['plan_stage_id'],
            'finish_time' => date('Y-m-d H:i:s'),
            'is_del' => 0
        );

        //插入个人档案，学分记录
        $this->addUserScoreLog($params['uid'], $params['score'], $params['score_type'], $params['schedule_lesson_id']);

        //更新可用学分，总学分
        $this->incScore($params['uid'], $params['score']);
        $result = parent::$main_db->insert('sty_user_schedule_lesson_score', $data);
        if ($result) {
            $this->load->model('learning_system/learning_report_model');
            $this->load->driver('cache');
            //口语 专修 选修
            $week_key = $this->learning_report_model->getRankKey('week');
            $total_key = $this->learning_report_model->getRankKey('total');
            $this->cache->redis->pipeline();
            $this->cache->redis->zIncrBy($week_key, $params['uid'], $params['score']);
            $this->cache->redis->zIncrBy($total_key, $params['uid'], $params['score']);
            if ($params['plan_id'] !== NULL) {
                //主修
                $week_schedule_key = $this->learning_report_model->getRankKey('week', $params['schedule_id']);
                $total_schedule_key = $this->learning_report_model->getRankKey('total', $params['schedule_id']);
                $this->cache->redis->zIncrBy($week_schedule_key, $params['uid'], $params['score']);
                $this->cache->redis->zIncrBy($total_schedule_key, $params['uid'], $params['score']);
            }
            $this->cache->redis->sync();
        }
    }

    /**
     * 插入用户得分记录表
     * @param $uid
     * @param $score
     * @param $type
     * @param $scheduleLessonId
     */
    public function addUserScoreLog($uid, $score, $type, $scheduleLessonId)
    {
        $scoreType = array_flip(get_options('score_type'));
        $this->load->model('learning_system/learning_task_model');
        $scheduleLesson = $this->learning_task_model->classroomInfo($scheduleLessonId);
        $lessonName = !empty($scheduleLesson['lesson_alias']) ? $scheduleLesson['lesson_alias'] : $scheduleLesson['lesson_name'];
        $scheduleName = $scheduleLesson['name'];
        if ($scoreType[$type] === 'checkin') {
            $contentType = 'live';
            $logType = 1;
        } elseif ($scoreType[$type] === 'practice') {
            $contentType = 'practice';
            $logType = 2;
        } else {
            $contentType = 'unit';
            $logType = 2;
        }

        $logContent = get_options('score_log_content', $contentType) . " {$scheduleName} {$lessonName}";
        $scoreLog = [
            'uid' => $uid,
            'score' => $score,
            'create_time' => date('Y-m-d H:i:s'),
            'type' => $logType,
            'content' => $logContent
        ];
        return parent::$main_db->insert('sty_user_score_log', $scoreLog);
    }

    /**
     * 用户未使用未过期的卡券数量
     * @param $uid
     * @return mixed
     */
    public function getCoupon($uid)
    {
        return parent::$minor_db->from('zd_coupon_users')
            ->where([
                'uid' => $uid,
                'status' => 0
            ])
            ->count_all_results();
    }

    /**
     * 日语学习主页用户基础信息
     * @param $uid
     * @return array
     */
    public function learnUserInfo($uid)
    {
        $data = [];
        $this->load->model('teaching_center/leave_model');
        $this->load->model('public/goods_model');
        $data['suspend'] = $this->leave_model->isSuspending($uid, TRUE);
        $curricular_system = get_options('curricular_system');
        if (!empty($curricular_system)) {
            foreach ($curricular_system as $curricular => $item) {
                $data[$curricular] = $this->goods_model->getUserGoodsStatus($uid, $item);
            }
        }
        if (isset($data['major']) && $data['major']['hasGoods'] === TRUE) {
            $score_info = $this->getUserMemberCount($uid);
            if (!empty($score_info)) {
                $data['study_score_total'] = intval($score_info['studyscoreTotal']);
                $data['study_score'] = intval($score_info['studyscore']);
            }
        }
        $where = [
            'student_id' => $uid,
            'schedule_id >' => 0,
            'is_del' => 0
        ];
        $data['commentCount'] = parent::$main_db->from('sty_teacher_comment')
            ->where($where)->count_all_results();
        $data['notBrowseNum'] = parent::$main_db->from('sty_teacher_comment')
            ->where($where)->where(['browse' => 0])->count_all_results();
        return $data;
    }

    /**
     * 获取用户学习信息
     * @param $uid
     * @return mixed
     */
    public function getLearnInfo($uid)
    {
        $this->load->library('thrift');
        return $this->thrift->service('User')->getUserLearnInfo($uid);
    }
}
