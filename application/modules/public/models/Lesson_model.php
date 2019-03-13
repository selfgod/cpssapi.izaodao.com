<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lesson_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/schedule_model');
    }

    /**
     * 获取单条用户lesson数据
     * @param $uid
     * @param $schedule_lesson_id
     * @return mixed
     */
    public function getUserScheduleLessonInfo($uid, $lesson_id)
    {
        return parent::$main_db->select('schedule_id, reservation_status, check_in_status, check_in_type, check_in_time, check_in_score, test_status, unit_status, is_showroom')
            ->from('sty_user_schedule_lesson')
            ->where(array(
                'uid' => $uid,
                'schedule_lesson_id' => $lesson_id,
                'is_del' => 0
            ))
            ->limit(1)->get()->row_array();
    }

    /**
     * 获取预约总人数
     * @param $uid
     * @return int
     */
    public function getReservationCount($lesson_id)
    {
        return parent::$main_db->from('sty_user_schedule_lesson')
            ->where(array(
                'schedule_lesson_id' => $lesson_id,
                'reservation_status' => 1,
                'is_del' => 0
            ))
            ->count_all_results();
    }

    /**
     * 用户报道总数
     * @param $uid
     * @return int
     */
    public function userReportCount($uid, $schedule_id = NULL)
    {
        $query = parent::$main_db->from('sty_user_schedule_lesson')
            ->where(array(
                'uid' => $uid,
                'check_in_status' => 1,
                'is_del' => 0
            ));
        if (intval($schedule_id) > 0) {
            $query->where(['schedule_id' => $schedule_id]);
        }
        return $query->count_all_results();
    }

    /**
     * 用户预约总数
     * @param $uid
     * @return mixed
     */
    public function userReservationLessonCount($uid)
    {
        return parent::$main_db->from('sty_user_schedule_lesson')
            ->where([
                'uid' => $uid,
                'reservation_status' => 1,
                'is_del' => 0
            ])
            ->count_all_results();
    }

    /**
     * 阶段课程下课件总数 、已完成数
     * @param $schedule_id
     * @param bool $finished
     * @return mixed
     */
    public function getScheduleLessonCount($schedule_id, $finished = FALSE)
    {
        $query = parent::$main_db->from('sty_schedule_lesson')
            ->where([
                'schedule_id' => $schedule_id,
                'is_del' => 0
            ]);
        if ($finished === TRUE) {
            $query->where(['end_time<' => date('Y-m-d H:i:s'), 'end_time>' => date('Y-m-d H:i:s', 0)]);
        }
        return $query->count_all_results();
    }

    /**
     * 是否存在已预约未完成的课程
     * @param $uid
     * @return bool
     */
    public function existUserReservationUnfinish($uid)
    {
        $query = parent::$main_db->from('sty_user_schedule_lesson AS susl')
            ->join('sty_schedule_lesson AS ssl', 'ssl.id=susl.schedule_lesson_id', 'left')
            ->where(array(
                'susl.uid' => $uid,
                'susl.reservation_status' => 1,
                'susl.check_in_status' => 0,
                'susl.is_del' => 0
            ))
            ->where('ssl.end_time IS NOT NULL')
            ->where('ssl.end_time>', date('Y-m-d H:i:s', strtotime('-90 minute')))->where('ssl.is_del', 0)->count_all_results();
        if ($query) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 用户报到
     * @param $params
     * @return mixed
     */
    public function userCheckIn($params)
    {
        $scheduleInfo = $this->schedule_model->getScheduleInfo($params['schedule_id'], 'id, class_mode, curricular_system, credit_type, class_num');
        if (empty($scheduleInfo)) {
            return ['code' => 500, 'msg' => '阶段课程不存在'];
        }
        $valid = $this->validateCheckIn($params['uid'], $scheduleInfo, $params['schedule_lesson_id']);
        if ($valid !== TRUE) {
            return $valid;
        }
        $this->submitUserFeeling($params['uid'], $params['schedule_id'], $params['schedule_lesson_id'],
            $params['interactive'], $params['learn'], $params['teach'], $params['comment'], $params['check_in_source']);

        $params['category'] = $scheduleInfo['curricular_system'];
        $params['class_mode'] = $scheduleInfo['class_mode'];
        $params['modify_time'] = $params['check_in_time'] = $params['create_time'] = date('Y-m-d H:i:s');
        $params['check_in_status'] = 1;
        $params['is_del'] = 0;
        $result['score'] = 0;
        $this->load->model('learning_task_model');
        $params['check_in_type'] = 2;
        $params['check_in_score'] = 0;
        $nowDate = date('Y-m-d H:i:s');
        $current = time();
        $lesson = $this->getScheduleLessonCacheData($params['schedule_lesson_id']);
        if (!empty($lesson) && $lesson['is_del'] === '0') {
            $end_time = strtotime($lesson['end_time']);
            if ($current >= $end_time - 1800 && $current <= $end_time + 5400) {
                $params['check_in_type'] = 1;//按时报到
                if (intval($scheduleInfo['credit_type']) === 0) {
                    $result['no_reward'] = 1;//不奖励学分
                } else {
                    $params['check_in_score'] = 5;
                }
            }
        }
        $result['live_report'] = $params['check_in_type'];
        $checkInFields = $this->getCheckInFields($params['category']);
        //当前学分上限判断
        $this->load->model('public/member_model');
        if (!isset($result['no_reward'])) {
            if ($this->member_model->overScoreLimit($params['uid'])) {
                $params['check_in_score'] = 0;
                $result['over_limit'] = 1;
            }
        }
        //报到前的出勤率
        $bingo = $this->reward($params['uid'], $params['schedule_id']);

        parent::$main_db->trans_start();
        $upsertResult = $this->upsertScheduleLesson($checkInFields['insert'], $checkInFields['update'], $params);
        if ($upsertResult) {
            $params['score_type'] = get_options('score_type', 'checkin');
            $params['sub_id'] = $params['schedule_lesson_id'];
            $params['score'] = $params['check_in_score'];
            if ($params['score'] === 0) {
                $this->addCompleteStatusLog($params);
            } else {
                $this->member_model->incUserScore($params);
            }
            $result['score'] = $params['score'];
        }
        $this->load->model('public/member_model');
        $this->member_model->updateReportTime($params['uid']);
        //新增用户行为
        parent::$main_db->insert('sty_user_schedule_action', [
            'uid' => $params['uid'],
            'schedule_id' => $params['schedule_id'],
            'lesson_id' => $params['schedule_lesson_id'],
            'type' => 5,
            'create_time' => $nowDate
        ]);
        parent::$main_db->trans_complete();
        $schedule_count = $this->userReportCount($params['uid'], $params['schedule_id']);
        if($scheduleInfo['curricular_system'] != 1){
            $full_attendance = FALSE;
        }else{
            $full_attendance = (($schedule_count / $scheduleInfo['class_num'])>=1) ? TRUE : FALSE;
        }
        $result['full_attendance'] = $full_attendance;
        //kafka生产消费数据
        $this->load->library('thrift');
        $this->thrift->service('Kafka')->producer('sasschedule', 'userReport', [
            'uid' => $params['uid'],
            'schedule_id' => $params['schedule_id'],
            'lesson_id' => $params['schedule_lesson_id'],
            'score' => $params['score'],
            'dateTime' => $nowDate
        ]);
        $result['code'] = 200;
        return $result;
    }

    /**
     * 验证是否可以报到
     * @param $uid
     * @param $schedule
     * @param $lessonId
     * @return mixed
     */
    protected function validateCheckIn($uid, $schedule, $lessonId)
    {
        if ($schedule['curricular_system'] === '1') {
            $data = [
                'schedule_id' => $schedule['id'],
                'schedule_lesson_id' => $lessonId,
                'uid' => $uid,
                'isMajor' => TRUE
            ];
            return $this->validation->validate('major_check_in', $data);
        } else {
            $data = [
                'schedule_id' => $schedule['id'],
                'schedule_lesson_id' => $lessonId,
                'uid' => $uid,
                'isMajor' => FALSE
            ];
            return $this->validation->validate('check_in', $data);
        }
    }

    /**
     * 获取报到sql字段
     * @param $type
     * @return array|bool
     */
    public function getCheckInFields($type)
    {
        if (intval($type) === 1) {
            //主修
            return array(
                'insert' => array(
                    'uid',
                    'schedule_id',
                    'schedule_lesson_id',
                    'category',
                    'create_time',
                    'plan_id',
                    'plan_stage_id',
                    'opt_type',
                    'class_mode',
                    'check_in_status',
                    'check_in_time',
                    'check_in_type',
                    'check_in_score',
                    'check_in_source',
                    'modify_time',
                    'is_del'
                ),
                'update' => array(
                    'check_in_status',
                    'check_in_time',
                    'check_in_type',
                    'check_in_score',
                    'check_in_source',
                    'modify_time'
                )
            );
        } else {
            //口语 专修 选修
            return array(
                'insert' => array(
                    'uid',
                    'schedule_id',
                    'schedule_lesson_id',
                    'category',
                    'opt_type',
                    'check_in_status',
                    'check_in_time',
                    'is_del',
                    'check_in_type',
                    'check_in_score',
                    'check_in_source',
                    'modify_time',
                    'create_time'
                ),
                'update' => array(
                    'check_in_status',
                    'check_in_time',
                    'check_in_type',
                    'check_in_score',
                    'check_in_source',
                    'modify_time'
                )
            );
        }
    }

    /**
     * 是否还有必要弹出奖杯提示
     * @param $uid
     * @param $scheduleId
     * @return bool true 没必要| false 要
     */
    public function reward($uid, $scheduleId)
    {
        $attendance = $this->getAttendance($uid, $scheduleId);
        return $attendance >= 80;
    }

    /**
     * 获取出勤率，不考虑时间
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function getAttendance($uid, $scheduleId)
    {
        $this->load->model('learning_system/learning_report_model');
        return $this->learning_report_model->attendanceNoTime($uid, $scheduleId);
    }

    /**
     * 插入或更新sty_user_schedule_lesson表
     * @param $inserts
     * @param $updates
     * @param $params
     * @return mixed
     */
    public function upsertScheduleLesson($inserts, $updates, $params)
    {
        $insert = $values = $update = '';
        $valuesArr = $updateArr = [];
        foreach ($inserts as $field) {
            if (!isset($params[$field])) {
                //TODO:添加验证
                //json_error
            } else {
                $insert .= "`$field`,";
                $values .= '?,';
                $valuesArr[] = $params[$field];
            }
        }
        $insert = rtrim($insert, ',');
        $values = rtrim($values, ',');
        foreach ($updates as $field) {
            if (!isset($params[$field])) {
                //TODO:添加验证
                //json_error
            } else {
                $update .= "`$field`=?,";
                $updateArr[] = $params[$field];
            }
        }
        $update = rtrim($update, ',');
        $sql = "INSERT INTO `sty_user_schedule_lesson` ({$insert}) VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";
        return parent::$main_db->query($sql, array_merge($valuesArr, $updateArr));
    }

    /**
     * 增加用户完成做题报到记录，但是不加学分
     * @param array $params
     * @return mixed
     */
    public function addCompleteStatusLog(array $params)
    {
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
        return parent::$main_db->insert('sty_user_schedule_lesson_score', $data);
    }

    /**
     * 提交用户上课感受
     * @param $uid
     * @param $scheduleId
     * @param $scheduleLessonId
     */
    public function submitUserFeeling($uid, $scheduleId, $scheduleLessonId, $interactive, $learn, $teach, $comment, $check_in_source)
    {
        $this->load->model('learning_system/learning_task_model');
        $lesson = $this->learning_task_model->getScheduleLessonInfo($scheduleLessonId);
        if ($lesson) {
            $teacherId = $lesson['teacher_id'];
            $this->load->model('public/survey_model');
            $this->survey_model->submitLearnFeeling($uid, $teacherId, $scheduleId, $scheduleLessonId, $interactive, $learn, $teach, $comment, $check_in_source);
        }
    }

    /**
     * 用户预约
     * @param $params
     * @return mixed
     */
    public function userReserve($params)
    {
        $fields = array(
            'insert' => array(
                'uid',
                'schedule_id',
                'schedule_lesson_id',
                'category',
                'create_time',
                'opt_type',
                'is_del',
                'modify_time',
                'reservation_status'
            ),
            'update' => array('modify_time', 'reservation_status')
        );
        $params['modify_time'] = $params['create_time'] = date('Y-m-d H:i:s');
        $params['reservation_status'] = 1;
        $params['is_del'] = 0;
        $result = $this->upsertScheduleLesson($fields['insert'], $fields['update'], $params);
        return $result;
    }

    /**
     * 预约课程
     * @param $uid
     * @param $catgory
     * @param $lesson_id
     * @return array|void
     */
    public function userReserveLesson($uid, $catgory, $lesson_id)
    {
        $this->load->model('public/goods_model');
        $nowDate = date('Y-m-d H:i:s');
        $result = $this->reserveBefore($uid, $catgory, $lesson_id);
        if (intval($result['code']) === 200) {
            $enable_zdtalk = $result['enable_zdtalk'];
            $start_time = $result['start_time'];
            $schedule_id = intval($result['schedule_id']);
            $result = [];
            $params = array(
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'schedule_lesson_id' => $lesson_id,
                'category' => $catgory
            );
            parent::$main_db->trans_start();
            //预约
            $this->userReserve($params);
            //更新次数 变更记录
            $this->goods_model->deductNum($uid, $catgory, $schedule_id, $lesson_id, NULL, $start_time);
            //新增用户行为
            parent::$main_db->insert('sty_user_schedule_action', [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'lesson_id' => $lesson_id,
                'type' => 3,
                'create_time' => $nowDate
            ]);
            parent::$main_db->trans_complete();
            if (parent::$main_db->trans_status() !== FALSE) {
                //kafka生产消费数据
                $this->load->library('thrift');
                $this->thrift->service('Kafka')->producer('sasschedule', 'userReservation', [
                    'uid' => $uid,
                    'schedule_id' => $schedule_id,
                    'lesson_id' => $lesson_id,
                    'dateTime' => $nowDate
                ]);
                $result['code'] = 200;
                $result['enable_zdtalk'] = $enable_zdtalk;
            } else {
                $result['code'] = 500;
            }
        }
        return $result;
    }

    /**
     * 取消预约
     * @param $uid
     * @param $catgory
     * @param $lesson_id
     * @param bool $systemCancel TRUE 系统取消 FALSE 非系统取消
     * @return array
     */
    public function userCancelReserveLesson($uid, $catgory, $lesson_id, $systemCancel = FALSE)
    {
        $this->load->model('public/goods_model');
        $nowDate = date('Y-m-d H:i:s');
        $result = $this->cancelReserveBefore($uid, $catgory, $lesson_id, $systemCancel);
        if (intval($result['code']) === 200) {
            $num = 0;
            $schedule_id = intval($result['schedule_id']);
            $msg = 'NOMAJOR_ADD';
            if ($systemCancel === TRUE) {
                $num = intval($result['num']) ? intval($result['num']) : 0;
                $msg = 'USER_NOT_ENOUGH_ADD';
            }
            $result = [];
            parent::$main_db->trans_start();
            //取消预约
            parent::$main_db->update('sty_user_schedule_lesson', array(
                'reservation_status' => 2,
                'modify_time' => date('Y-m-d H:i:s')
            ), array('uid' => $uid, 'schedule_id' => $schedule_id, 'schedule_lesson_id' => $lesson_id, 'is_del' => 0));

            //返还次数
            $this->goods_model->giveBackNum($uid, $catgory, $schedule_id, $lesson_id, $msg);
            //新增用户行为
            parent::$main_db->insert('sty_user_schedule_action', [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'lesson_id' => $lesson_id,
                'type' => 4,
                'create_time' => $nowDate
            ]);
            parent::$main_db->trans_complete();
            if (parent::$main_db->trans_status() !== FALSE) {
                //kafka生产消费数据
                $this->load->library('thrift');
                $this->thrift->service('Kafka')->producer('sasschedule', 'userReservation', [
                    'uid' => $uid,
                    'schedule_id' => $schedule_id,
                    'lesson_id' => $lesson_id,
                    'dateTime' => $nowDate
                ]);
                $result['code'] = 200;
                if ($systemCancel === TRUE) {
                    $result['num'] = $num;
                    $result['date'] = date('m月d日');
                }
            } else {
                $result['code'] = 500;
            }
        }
        return $result;
    }

    /**
     * 预约/取消预约前验证
     * @param $uid
     * @param $catgory
     * @param bool $cancel TRUE 取消预约 FALSE 预约
     * @return array|bool
     */
    public function reserveBeforeVerify($uid, $catgory, $cancel = FALSE)
    {
        $this->load->model('public/goods_model');
        $result = $this->validation->validate('goods_type_mode', ['uid' => $uid, 'type' => $catgory]);
        if ($result === TRUE) {
            $result = ['code' => 0];
            //验证激活和有效期 次数
            $used = $this->goods_model->getUserGoodsExpireAndNumInfo($uid, $catgory);
            if (!empty($used)) {
                if (intval($used['is_activate']) === 1) {
                    //已过期
                    if ($used['unlimit_expire'] != 1 && (!intval($used['expire']) || $used['expire'] < date('Y-m-d H:i:s'))) {
                        $result['code'] = 221;
                        $goods = $this->goods_model->goodsBaseInfo($used['goods_id']);
                        $result['goods_name'] = '';
                        $result['show_time'] = '';
                        if (!empty($goods)) {
                            $result['goods_name'] = $goods['name'];
                            $result['show_time'] = date('Y年m月d日', strtotime($used['expire']));
                        }
                    } else {
                        $result['code'] = 200;
                        if ($cancel === FALSE && $used['unlimit_count'] != 1 && $used['remain_count'] <= 0) {
                            $result['code'] = 222;
                        }
                        $result['used'] = $used;
                    }
                } else {
                    //未激活
                    $result['code'] = 216;
                }
            }
        }
        return $result;
    }

    /**
     * 预约之前操作
     * @param $uid
     * @param $catgory
     * @param $lesson_id
     */
    public function reserveBefore($uid, $catgory, $lesson_id)
    {
        $this->load->model('public/goods_model');
        $result = $this->reserveBeforeVerify($uid, $catgory);
        if (intval($result['code']) === 200) {
            $used = $result['used'];
            $result = ['code' => 0];
            //获取阶段课程课件信息
            $schedule_lesson = $this->getScheduleLessonDetail([
                'ssl.id' => $lesson_id,
                'ssl.curricular_system' => $catgory,
                'ss.curricular_system' => $catgory,
                'ss.status' => 1,
            ], ['ssl.min_num', 'ssl.max_num', 'ssl.start_time', 'ssl.end_time', 'ss.enable_zdtalk'], [], [1]);
            if (!empty($schedule_lesson)) {
                //用户是否已预约
                $user_lesson = $this->getUserScheduleLessonInfo($uid, $lesson_id);
                if (empty($user_lesson) || intval($user_lesson['reservation_status']) !== 1) {
                    if (!empty($used) && ($used['unlimit_expire'] == 1 || $used['expire'] > $schedule_lesson['start_time'])) {
                        if (intval($schedule_lesson['max_num']) > 0) {
                            //预约此课件总人数
                            $count = $this->getReservationCount($lesson_id);
                            if (intval($count) >= intval($schedule_lesson['max_num'])) {
                                //预约人数已满
                                $result['code'] = 219;
                            }
                        }
                        if ($result['code'] == 0) {
                            //验证是否在预约时间内
                            if (intval($schedule_lesson['start_time']) > 0 && time() > strtotime($schedule_lesson['start_time'] . ' -7 day') && time() < strtotime($schedule_lesson['start_time'] . ' -2 hour')) {
                                $result['code'] = 200;
                                $result['time_conflict'] = 0;
                                $result['schedule_id'] = $schedule_lesson['schedule_id'];
                                $result['min_num'] = intval($schedule_lesson['min_num']);
                                $result['start_time'] = $schedule_lesson['start_time'];
                                $result['enable_zdtalk'] = intval($schedule_lesson['enable_zdtalk']);
                                //是否与其它预约制课程时间冲突
                                $conflict = $this->getUserScheduleLessonDetial($uid, [
                                    'susl.reservation_status' => 1,
                                    'susl.check_in_status' => 0,
                                    '_string' => "((`ssl`.`start_time` >= '" . $schedule_lesson['start_time'] . "' AND `ssl`.`start_time` < '" . $schedule_lesson['end_time'] . "') OR (`ssl`.`end_time` > '" . $schedule_lesson['start_time'] . "' AND `ssl`.`end_time` <= '" . $schedule_lesson['end_time'] . "'))",
                                ]);
                                if (!empty($conflict)) {
                                    $result['time_conflict'] = 1;
                                } else {
                                    //是否与主修课上课时间冲突
                                    $scheduleIds = $this->schedule_model->getUserAbleScheduleArr($uid);
                                    if (!empty($scheduleIds)) {
                                        $lessonCount = parent::$main_db->from('sty_schedule_lesson')
                                            ->where_in('schedule_id', $scheduleIds)
                                            ->where_not_in('business_type', [2, 5])
                                            ->where([
                                                'class_mode' => 1,
                                                'curricular_system' => 1,
                                                'is_del' => 0,
                                            ])
                                            ->where("((`start_time` >= '" . $schedule_lesson['start_time'] . "' AND `start_time` < '" . $schedule_lesson['end_time'] . "') OR (`end_time` > '" . $schedule_lesson['start_time'] . "' AND `end_time` <= '" . $schedule_lesson['end_time'] . "'))")
                                            ->count_all_results();
                                        if (intval($lessonCount) > 0) {
                                            $result['time_conflict'] = 1;
                                        }
                                    }
                                }
                            } else {
                                $result['code'] = 220;
                            }
                        }
                    } else {
                        //预约时间是否在有效期内
                        $result['code'] = 224;
                    }
                } else {
                    //已预约
                    $result['code'] = 218;
                }
            } else {
                $result['code'] = 217;
            }
        }
        $result['exist_un_activate'] = FALSE;
        if ($result['code'] == 222 || $result['code'] == 221) {
            //当过期或者没次数的情况 验证是否还有未激活的此上课模式的商品
            $un_activate = $this->goods_model->selectUserGoodsJoinUserGoodsInfoObj($uid, [
                'ugi.type' => $catgory,
                'ug.is_activate' => 0
            ], [], ['ug.expire DESC']);
            if (!empty($un_activate)) {
                $result['exist_un_activate'] = TRUE;
            }
        }
        return $result;
    }


    /**
     * 取消预约之前操作
     * @param $uid
     * @param $catgory
     * @param $lesson_id
     * @param bool $systemCancel
     * @return array|bool
     */
    public function cancelReserveBefore($uid, $catgory, $lesson_id, $systemCancel = FALSE)
    {
        $result = $this->reserveBeforeVerify($uid, $catgory, TRUE);
        if (intval($result['code']) === 200) {
            $result = [];
            //获取阶段课程课件信息
            $schedule_lesson = $this->getScheduleLessonDetail([
                'ssl.id' => $lesson_id,
                'ssl.curricular_system' => $catgory,
                'ss.curricular_system' => $catgory,
                'ss.status' => 1,
            ], ['ssl.min_num', 'ssl.max_num', 'ssl.start_time', 'ssl.end_time'], [], [1]);
            if (!empty($schedule_lesson)) {
                //用户是否已预约
                $user_lesson = $this->getUserScheduleLessonInfo($uid, $lesson_id);
                if (!empty($user_lesson) && intval($user_lesson['reservation_status']) === 1) {
                    $result['schedule_id'] = $schedule_lesson['schedule_id'];
                    if ($systemCancel !== TRUE) {
                        //验证是否在预约时间内
                        if (intval($schedule_lesson['start_time']) > 0 && time() > strtotime($schedule_lesson['start_time'] . ' -7 day') && time() < strtotime($schedule_lesson['start_time'] . ' -2 hour')) {
                            $result['code'] = 200;
                        } else {
                            $result['code'] = 220;
                        }
                    } else {
                        $count = $this->getReservationCount($lesson_id);
                        //验证预约总数
                        if (intval($count) < intval($schedule_lesson['min_num']) && intval($schedule_lesson['min_num']) > 0) {
                            $result['code'] = 200;
                            $result['num'] = $schedule_lesson['min_num'];
                        } else {
                            //此课程预约人数不足最低开课人数
                            $result['code'] = 231;
                        }
                    }
                } else {
                    $result['code'] = 223;
                }
            } else {
                $result['code'] = 217;
            }
        }
        return $result;
    }


    /**
     * 处理用户报到行为
     * @param $uid
     * @param $schedule_id
     * @param $lesson_id
     * @param $action_time
     * @param bool $major
     * @return array
     */
    public function handleLessonReportAction($uid, $schedule_id, $lesson_id, $action_time, $major = TRUE)
    {
        $result = ['code' => 500, 'msg' => NULL];
        $lesson = $this->getScheduleLessonCacheData($lesson_id);
        if (empty($lesson) && intval($lesson['schedule_id']) !== intval($schedule_id)) {
            $result['msg'] = 'lesson NULL OR schedule_id Unmatch';
            return $result;
        }
        $userLesson = $this->getUserScheduleLessonInfo($uid, $lesson_id);
        if (!empty($userLesson) && intval($userLesson['check_in_status']) === 1) {
            //非主修课未预约直接返回
            if ($major !== TRUE && intval($userLesson['reservation_status']) !== 1) {
                $result['msg'] = 'major not true and reservation_status neq 1';
                return $result;
            }
            if ($major === TRUE) {
                //更新user_schedule
                $this->load->model('public/schedule_model');
                $join_schedule = $this->schedule_model->getUserJoinedSchedule($uid, $schedule_id, -1);
                if (!empty($join_schedule)) {
                    $userLiveNum = parent::$main_db->from('sty_user_schedule_lesson')
                        ->where([
                            'uid' => $uid,
                            'schedule_id' => $schedule_id,
                            'check_in_status' => 1,
                            'check_in_type' => 1,
                            'staff' => 0,
                            'is_del' => 0
                        ])
                        ->count_all_results();

                    $userRecordNum = parent::$main_db->from('sty_user_schedule_lesson')
                        ->where([
                            'uid' => $uid,
                            'schedule_id' => $schedule_id,
                            'check_in_status' => 1,
                            'check_in_type' => 2,
                            'staff' => 0,
                            'is_del' => 0
                        ])
                        ->count_all_results();
                    $update_schedule = parent::$main_db->where([
                        'uid' => $uid,
                        'schedule_id' => $schedule_id
                    ])->update('sty_user_schedule', [
                        'live_report_number' => intval($userLiveNum),
                        'record_report_number' => intval($userRecordNum),
                        'last_report_time' => $action_time,
                    ]);
                    if ($update_schedule === FALSE) {
                        $result['msg'] = 'error Sql:' . parent::$main_db->last_query();
                        return $result;
                    }
                } else {
                    $result['msg'] = 'join_schedule NULL';
                    return $result;
                }
            }
            $this->load->model('public/member_model');
            //计算互动率,学习满意度,授课满意度
            $liveReportNumber = parent::$main_db->from('sty_user_schedule_lesson')
                ->where([
                    'schedule_id' => $schedule_id,
                    'schedule_lesson_id' => $lesson_id,
                    'check_in_status' => 1,
                    'check_in_type' => 1,
                    'staff' => 0,
                    'is_del' => 0
                ])
                ->count_all_results();
            $recordReportNumber = parent::$main_db->from('sty_user_schedule_lesson')
                ->where([
                    'schedule_id' => $schedule_id,
                    'schedule_lesson_id' => $lesson_id,
                    'check_in_status' => 1,
                    'check_in_type' => 2,
                    'staff' => 0,
                    'is_del' => 0
                ])
                ->count_all_results();
            $interactive_rate = $learn_rate = $teach_rate = 0;
            $report_count = intval($liveReportNumber) + intval($recordReportNumber);
            if ($report_count > 0) {
                //互动
                $interactive_count = parent::$main_db->from('sty_user_feeling')
                    ->where([
                        'schedule_id' => $schedule_id,
                        'schedule_lesson_id' => $lesson_id,
                        'staff' => 0,
                        'is_del' => 0
                    ])->where_in('interactive', [4, 3, 2])
                    ->count_all_results();
                if ($interactive_count > 0) $interactive_rate = round($interactive_count / $report_count, 4) * 10000;
                //学习满意
                $learn_count = parent::$main_db->from('sty_user_feeling')
                    ->where([
                        'schedule_id' => $schedule_id,
                        'schedule_lesson_id' => $lesson_id,
                        'learn' => 3,
                        'staff' => 0,
                        'is_del' => 0
                    ])
                    ->count_all_results();

                if ($learn_count > 0) $learn_rate = round($learn_count / $report_count, 4) * 10000;
                //授课满意
                $teach_count = parent::$main_db->from('sty_user_feeling')
                    ->where([
                        'schedule_id' => $schedule_id,
                        'schedule_lesson_id' => $lesson_id,
                        'teach' => 3,
                        'staff' => 0,
                        'is_del' => 0
                    ])
                    ->count_all_results();
                if ($teach_count > 0) $teach_rate = round($teach_count / $report_count, 4) * 10000;
            }
            //更新schedule_lesson
            $save = parent::$main_db->where([
                'id' => $lesson_id,
                'schedule_id' => $schedule_id,
            ])->update('sty_schedule_lesson', [
                'live_report_number' => intval($liveReportNumber),
                'record_report_number' => intval($recordReportNumber),
                'interactive_rate' => $interactive_rate,
                'learn_rate' => $learn_rate,
                'teach_rate' => $teach_rate
            ]);
            if ($save !== FALSE) {
                $result['code'] = 200;
            } else {
                $result['msg'] = 'error Sql:' . parent::$main_db->last_query();
            }
        } else {
            $result['msg'] = 'userLesson is NULL OR check_in_status neq 1';
        }
        return $result;
    }

    /**
     * 处理做题动作
     * @param $schedule_id
     * @param $lesson_id
     * @param bool $unit
     * @return mixed
     */
    public function handleLessonAnswerAction($schedule_id, $lesson_id, $test = FALSE)
    {
        $result = ['code' => 500, 'msg' => NULL];
        if ($test === TRUE) {
            $testNum = parent::$main_db->from('sty_user_schedule_lesson')
                ->where([
                    'schedule_id' => $schedule_id,
                    'schedule_lesson_id' => $lesson_id,
                    'test_status' => 1,
                    'staff' => 0,
                    'is_del' => 0
                ])
                ->count_all_results();
            $setKey = 'test_number';
            $setVal = intval($testNum);
        } else {
            $unitNum = parent::$main_db->from('sty_user_schedule_lesson')
                ->where([
                    'schedule_id' => $schedule_id,
                    'schedule_lesson_id' => $lesson_id,
                    'unit_status' => 1,
                    'staff' => 0,
                    'is_del' => 0
                ])
                ->count_all_results();
            $setKey = 'unit_number';
            $setVal = intval($unitNum);
        }
        $save = parent::$main_db->where([
            'id' => $lesson_id,
            'schedule_id' => $schedule_id
        ])->update('sty_schedule_lesson', [$setKey => $setVal]);
        if ($save !== FALSE) {
            $result['code'] = 200;
        } else {
            $result['msg'] = 'error Sql:' . parent::$main_db->last_query();
        }
        return $result;
    }

    /**
     * 获取课件信息
     * @param array $w
     * @return mixed
     */
    public function getScheduleLessonDetail($w = [], array $f = [], array $order = [], array $limit = [])
    {
        $this->load->model('public/common_model');
        $where = ['ssl.is_del' => 0, 'ss.is_del' => 0];
        $field = ['ssl.id', 'ssl.schedule_id'];
        if (!empty($w)) $where = array_merge($where, $w);
        if (!empty($f)) $field = array_merge($field, $f);
        $join = ['joinTable' => 'sty_schedule AS ss', 'joinCond' => 'ss.id = ssl.schedule_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_schedule_lesson AS ssl', [
            'field' => $field,
            'where' => $where,
            'order' => $order,
            'limit' => $limit
        ], [$join]);
    }

    /**
     * 获取用户schedule lesson 信息
     * @param $uid
     * @param array $w
     * @param array $f
     * @return mixed
     */
    public function getUserScheduleLessonDetial($uid, array $w = [], array $f = [], array $order = [], array $limit = [])
    {
        $this->load->model('public/common_model');
        $where = ['susl.uid' => $uid, 'susl.is_del' => 0, 'ssl.is_del' => 0];
        $field = ['ssl.id', 'susl.schedule_lesson_id', 'ssl.schedule_id'];
        if (!empty($w)) $where = array_merge($where, $w);
        if (!empty($f)) $field = array_merge($field, $f);
        $join = ['joinTable' => 'sty_schedule_lesson as ssl', 'joinCond' => 'ssl.id = susl.schedule_lesson_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_user_schedule_lesson as susl', [
            'field' => $field,
            'where' => $where,
            'order' => $order,
            'limit' => $limit
        ], [$join]);
    }

    /**
     * 阶段课程课件缓存数据
     * @param $schedule_lesson_id
     * @param bool $cache
     * @return array
     */
    public function getScheduleLessonCacheData($schedule_lesson_id, $cache = FALSE)
    {
        $this->load->driver('cache');
        $key = sprintf(get_options('baseDataCacheKey', 'scheduleLesson'), $schedule_lesson_id);
        if ($cache === TRUE && BASE_DATA_CACHE_OPEN === TRUE) {
            $cacheData = $this->cache->redis->hGetAll($key);
            if (!empty($cacheData)) return $cacheData;
        }
        $data = parent::$main_db->from('sty_schedule_lesson')->where([
            'id' => $schedule_lesson_id
        ])->limit(1)->get()->row_array();
        if (!empty($data)) {
            if (BASE_DATA_CACHE_OPEN === TRUE) $this->cache->redis->hMset($key, $data);
            return $data;
        }
        return [];
    }
}