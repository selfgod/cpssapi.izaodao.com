<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule_model extends MY_Model
{

    /**
     * 获取阶段课程信息
     * @param $id
     * @return mixed
     */
    public function getScheduleInfo($id, $field = NULL)
    {
        $field = !empty($field) ? $field : '';
        $this->load->model('public/common_model');
        return $this->common_model->selectObjData('sty_schedule', [
            'field' => $field,
            'where' => ['id' => $id, 'is_del' => 0],
            'limit' => [1]
        ]);
    }

    /**
     * 用户加入阶段课程信息
     * @param $uid
     * @param $schedule_id
     * @param null $field
     * @return mixed
     */
    public function getUserScheduleInfo($uid, $schedule_id, $field = NULL)
    {
        $field = !empty($field) ? $field : '';
        $this->load->model('public/common_model');
        return $this->common_model->selectObjData('sty_user_schedule', [
            'field' => $field,
            'where' => ['uid' => $uid, 'schedule_id' => $schedule_id, 'is_del' => 0],
            'limit' => [1]
        ]);
    }

    /**
     * 标记第一次进入班级
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function markFirstView($uid, $scheduleId)
    {
        $query = parent::$main_db->select('first_view')->from('sty_user_schedule')->where(array(
            'uid' => $uid,
            'schedule_id' => $scheduleId
        ));
        $result = $query->get()->row_array();
        if(!empty($result['first_view'])){
            $res = TRUE;
        }else{
            $sql = "UPDATE sty_user_schedule SET first_view = 1 WHERE uid = ({$uid}) AND schedule_id = ({$scheduleId})";
            $res = parent::$main_db->query($sql);
        }
        return $res;
    }

    /**
     * 获取阶段课程基本信息
     * @param $id
     * @return mixed
     */
    public function getScheduleBaseInfo($id)
    {
        $schedule_info = $this->getScheduleInfo($id, 'id, name, target, start_time, end_time, class_start_time,
        class_end_time, class_num, class_mode, qq_group, room_id, room_name, room_pwd,
        content_cat,max_curriculum_num, enable_zdtalk, business_type, curricular_system');
        if (!empty($schedule_info)) {
            $schedule_info['start_time'] = date('Y/m/d', strtotime($schedule_info['start_time']));
            $schedule_info['end_time'] = date('Y/m/d', strtotime($schedule_info['end_time']));
            $schedule_info['class_start_time'] = date('H:i', strtotime($schedule_info['class_start_time']));
            $schedule_info['class_end_time'] = date('H:i', strtotime($schedule_info['class_end_time']));
        }
        return $schedule_info;
    }

    /**
     * 获取用户加入过的阶段课程
     * @param $uid
     * @param $schedule_id
     * @param bool $del
     * @return mixed
     */
    public function getUserJoinedSchedule($uid, $schedule_id, $del = 0)
    {
        $query = parent::$main_db->select('schedule_id')->from('sty_user_schedule')->where(array(
            'uid' => $uid,
            'schedule_id' => $schedule_id
        ));
        if (intval($del) !== -1) {
            $query->where('is_del', intval($del));
        }
        return $query->get()->row_array();
    }

    /**
     * 获取阶段课程主讲教师信息列表
     * @param $schedule_id
     * @return array
     */
    public function getScheduleTeachers($schedule_id)
    {
        $query = parent::$main_db->select('zti.uid, zti.name, zti.desPic2, zti.smallPic, zti.personal_tag, sst.audition_link')
            ->from('zd_class.zd_teacher_info zti')
            ->join('zd_netschool.sty_schedule_teacher sst', 'zti.uid = sst.teacher_id', 'left')
            ->where(array(
                'sst.schedule_id' => $schedule_id,
                'sst.category' => 1,
                'sst.is_del' => 0
            ))->order_by('sst.id', 'ASC')->get();
        $teachers = array();
        if ($teacher = $query->result_array()) {
            foreach ($teacher as $item) {
                $teachers[] = $this->formatTeacher($item);
            }
        }
        return $teachers;
    }

    /**
     * 获取教师信息
     * @param $teacher_id
     * @return array
     */
    public function getTeacherInfo($teacher_id)
    {
        $result = parent::$minor_db
            ->select('uid, name, desPic2, smallPic, personal_tag')
            ->from('zd_teacher_info')
            ->where('uid', $teacher_id)
            ->limit(1)
            ->get()->row_array();

        $teacher = array();
        if (!empty($result)) {
            $teacher = $this->formatTeacher($result);
        }
        return $teacher;
    }

    /**
     * 获取教师信息(新)
     * @param $teacher_id
     * @return array
     */
    public function getTeacherInfoNew($teacher_id)
    {
        $this->load->library('thrift');
        return $this->thrift->service('User')->getTeacherInfo($teacher_id);
    }

    /**
     * 格式化教师信息
     * @param $data
     * @return mixed
     */
    public function formatTeacher($data)
    {
        $teacher['id'] = $data['uid'];
        $teacher['name'] = $data['name'];
        $teacher['avatar'] = !empty($data['desPic2']) ? MASTER_DOMAIN . $data['desPic2'] : (!empty($data['smallPic']) ? MASTER_DOMAIN . $data['smallPic'] : '');
        $teacher['tag'] = $data['personal_tag'];
        $teacher['detail_link'] = MASTER_DOMAIN . 'Index/Teacher/show?uid=' . $data['uid'];
        $teacher['audition_link'] = isset($data['audition_link']) ? $data['audition_link'] : '';
        return $teacher;
    }

    /**
     * 获取阶段课程教材信息
     * @param $schedule_id
     * @return array
     */
    public function getTextbooks($schedule_id)
    {
        $query = parent::$main_db->select('sb.name, sb.cover_img, sb.buy_link, sb.download_link')->from('sty_book sb')->join('sty_schedule_book ssb', 'ssb.book_id = sb.id', 'left')->where(array(
            'ssb.schedule_id' => $schedule_id,
            'ssb.is_del' => 0,
            'sb.is_del' => 0
        ))->get();
        return $query->result_array();
    }

    /**
     * 插入或更新sty_user_schedule_lesson表
     * @param $inserts
     * @param $updates
     * @param $params
     * @return mixed
     */
    public function upsertSchedule($inserts, $updates, $params)
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
        $sql = "INSERT INTO `sty_user_schedule` ({$insert}) VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";
        return parent::$main_db->query($sql, array_merge($valuesArr, $updateArr));
    }

    /**
     * 阶段课程上课安排
     * 返回一，二，三...六，日数组
     * @param $schedule_id
     * @return mixed
     */
    public function getScheduleWeekCycle($schedule_id)
    {
        $query = parent::$main_db->select('class_time')->from('sty_schedule_week_cycle')->where(array(
            'schedule_id' => $schedule_id,
            'status' => 1,
            'is_del' => 0
        ))->order_by('class_time ASC')->get();
        $days = [];
        if ($result = $query->result_array()) {
            foreach ($result as $day) {
                $days[] = $day['class_time'];
            }
            if ($days[0] === '0') {
                array_shift($days);
                $days[] = 0;
            }
        }
        return convert_keys_2_vaules('week_day', $days);
    }

    /**
     * 获取用户已经加入的阶段课程id数组
     * @param $uid
     * @return mixed
     */
    public function getUserScheduleIds($uid)
    {
        $query = parent::$main_db->select('schedule_id')
            ->from('sty_user_schedule')
            ->where([
                'uid' => $uid,
                'is_del' => 0
            ])->get();
        $result = [];
        foreach ($query->result_array() as $schedule) {
            $result[] = intval($schedule['schedule_id']);
        }
        return $result;
    }


    /**
     * 获取某阶段下阶段课程列表
     * @param $plan_id
     * @param $plan_stage_id
     * @param int $class_mode 1:直播 2:录播
     * @param $valida_end bool 验证是否结课
     * @param mixed $status 1 开启的 0 关闭的 false 忽略
     * @return
     */
    public function getPlanStageScheduleList($plan_id, $plan_stage_id, $class_mode = 0, $valida_end = FALSE, $status = 1)
    {
        $where = [
            'sssi.plan_id' => $plan_id,
            'sssi.plan_stage_id' => $plan_stage_id,
            'sssi.is_del' => 0,
            'ss.is_del' => 0
        ];
        if ($status !== FALSE) {
            $where['ss.status'] = $status;
        }
        $query = parent::$main_db->select('ss.id, ss.name, ss.remark, ss.class_num, ss.basic, ss.start_time, ss.end_time, ss.room_id, ss.room_name, ss.room_pwd, 
                ss.class_start_time, ss.class_end_time, ss.qq_group, ss.target, ss.max_curriculum_num, ss.class_mode')->from('sty_schedule AS ss')->join('sty_schedule_stage_item AS sssi', 'sssi.schedule_id=ss.id', 'left')->where($where);
        if ($class_mode > 0) {
            $query->where('ss.class_mode', $class_mode);
        }
        if ($valida_end && intval($class_mode) === 1) {
            $query->where('ss.end_time>', date('Y-m-d H:i:s'));
        }
        $result = $query->order_by('start_time ASC')->get()->result_array();
        foreach ($result as $index => $schedule) {
            $result[$index]['class_start_time'] = substr($schedule['class_start_time'], 0, strrpos($schedule['class_start_time'], ':'));
            $result[$index]['class_end_time'] = substr($schedule['class_end_time'], 0, strrpos($schedule['class_end_time'], ':'));
        }
        return $result;
    }

    /**
     * 加入此阶段课程人数
     * @param $schedule_id
     * @param bool $removeStaff 移除内部员工
     * @return mixed
     */
    public function joinScheduleCount($schedule_id, $removeStaff = TRUE)
    {
        parent::$main_db->from('sty_user_schedule')
            ->where(array(
                'schedule_id' => $schedule_id,
                'is_del' => 0
            ));
        if ($removeStaff === TRUE) {
            parent::$main_db->where('staff', 0);
        }
        return parent::$main_db->count_all_results();
    }

    /**
     * 精品课直播课表(新我的课表)
     * @param $uid
     * @return array|mixed
     */
    public function getLiveCourseNew($uid)
    {
        $result = [];
        $has_class_day = [];
        $this->load->library('thrift');
        $WeekInfo = $this->checkUserHasLessonByWeek($uid, date('Y-m-d'));
        if(!empty($WeekInfo['weekDate'])){
            foreach ($WeekInfo['weekDate'] as $key=>$value){
                if(!empty($value['hasCourse'])){
                    $has_class_day[] = $value['date'];
                }
            }
        }
        if(!empty($has_class_day)){
            foreach ($has_class_day as $k=>$v){
                $param = ['day' => $v];
                $res = $this->thrift->service('Learn')->liveLessonList($uid, $param);
                $result[$k]['check_in_status'] = $res[0]['check_in_status'];
                $result[$k]['category'] = '';
                $result[$k]['start_time'] = $res[0]['start_time'];
                $result[$k]['end_time'] = $res[0]['end_time'];
                $result[$k]['alias'] = $res[0]['alias'];
                $result[$k]['lessonName'] = $res[0]['name'];
                $result[$k]['schedule_id'] = $res[0]['schedule_id'];
                $result[$k]['teacher_id'] = $res[0]['teacher_id'];
                $result[$k]['schedule_lesson_id'] = $res[0]['id'];
                $result[$k]['scheduleName'] = $res[0]['schedule_name'];
            }
        }
        return $result;
    }

    /**
     * 直播课表(我的课表)
     * @param $uid
     * @param $start
     * @param $end
     * @return array|mixed
     */
    public function getLiveCourse($uid, $start, $end)
    {
        if (!$uid || !$start || !$end) {
            return FALSE;
        }

        $scheduleIds = array();
        $scheduleList = array();
        $uidJoinStr1 = sprintf('sups.uid = %d', $uid);
        $uidJoinStr2 = sprintf('sus.uid = %d', $uid);
        $uidJoinStr3 = sprintf('susl.uid = %d', $uid);

        //获取有效期内的所有计划
        $this->load->model('public/plan_model');
        $plans = $this->plan_model->getExpirePlan($uid);
        if (empty($plans)) {
            return FALSE;
        }
        $plans = implode(',', $plans);

        //获取 schedule_id,plan_id,plan_stage_id
        $sql = "select sus.schedule_id,si.plan_id,si.plan_stage_id
                from sty_user_schedule as sus
                left join sty_schedule_stage_item as si on si.schedule_id = sus.schedule_id and si.is_del = 0
                left join sty_user_plan_stage as sups on sups.plan_id = si.plan_id and sups.plan_stage_id = si.plan_stage_id and sups.is_del = 0 and " . $uidJoinStr1 . "
                where " . $uidJoinStr2 . " and sus.is_del = 0 and sups.plan_id in ($plans)";
        $schedules = parent::$main_db->query($sql)->result_array();
        if (!empty($schedules)) {
            foreach ($schedules as $schedule) {
                $scheduleList[$schedule['schedule_id']]['plan_id'] = $schedule['plan_id'];
                $scheduleList[$schedule['schedule_id']]['plan_stage_id'] = $schedule['plan_stage_id'];
                //循环去重
                $scheduleIds[$schedule['schedule_id']] = $schedule['schedule_id'];
            }
        }

        //获取课列表
        if (empty($scheduleIds)) {
            return FALSE;
        }
        $query = parent::$main_db->select('susl.plan_id,susl.plan_stage_id,susl.check_in_status,susl.category,
            sl.start_time,sl.end_time,sl.alias,sl.name lessonName,sl.schedule_id,
            sl.teacher_id,sl.id schedule_lesson_id,ss.name scheduleName,ss.content_cat')
            ->from('sty_schedule_lesson as sl')
            ->join('sty_user_schedule_lesson as susl', $uidJoinStr3 . ' and susl.schedule_lesson_id = sl.id and susl.is_del = 0', 'left')
            ->join('sty_schedule as ss', 'ss.id = sl.schedule_id and ss.is_del = 0', 'left')
            ->where(array(
                'sl.is_del' => 0,
                'sl.start_time >= ' => $start,
                'sl.start_time <= ' => $end
            ))
            ->where_in('sl.schedule_id', $scheduleIds)
            ->order_by('sl.start_time', 'asc')->get();
        $mySchedules = $query->result_array();
        if (empty($mySchedules)) {
            return FALSE;
        }

        //给未加入的课赋予plan_id,plan_stage_id的值
        if (!empty($mySchedules)) {
            foreach ($mySchedules as $key => $mySchedule) {
                if (empty($mySchedule['plan_id']) || empty($mySchedule['plan_stage_id'])) {
                    $mySchedules[$key]['plan_id'] = $scheduleList[$mySchedule['schedule_id']]['plan_id'];
                    $mySchedules[$key]['plan_stage_id'] = $scheduleList[$mySchedule['schedule_id']]['plan_stage_id'];
                }
            }
        }
        return $mySchedules;
    }


    /**
     * 删除阶段课程
     * @param $uid
     * @param $schedule_id
     * @return mixed
     */
    public function delSchedule($uid, $schedule_id)
    {
        return parent::$main_db->update('sty_user_schedule', array(
            'is_del' => 1,
            'modify_time' => date('Y-m-d H:i:s')
        ), array('uid' => $uid, 'schedule_id' => $schedule_id, 'is_del' => 0));
    }

    /**
     * 删除理由
     * @param $uid
     * @param $conflict_type
     * @param $conflict_desc
     */
    public function delScheduleReason($uid, $schedule_id, $conflict_type, $conflict_desc)
    {
        return parent::$main_db->insert('sty_user_del_schedule_reason', array(
            'uid' => $uid,
            'schedule_id' => $schedule_id,
            'del_type' => $conflict_type,
            'del_reason' => $conflict_desc
        ));
    }

    /**
     * 获取在某计划阶段下加入的阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @return mixed
     */
    public function getUserPlanStageJoinSchedule($uid, $plan_id, $plan_stage_id)
    {
        $schedule_ids = array();
        $query = $this->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id
        ], ['schedule_id']);
        if (!empty($query)) {
            foreach ($query as $item) {
                $schedule_ids[] = $item['schedule_id'];
            }
        }
        return $schedule_ids;
    }

    /**
     * 获取某课程体系下课件信息
     * @param $schedule_lesson_id
     * @param $content_cat
     * @return mixed
     */
    public function getCategoryScheduleLessonInfo($schedule_lesson_id, $content_cat = '')
    {
        $where = ['ssl.id' => $schedule_lesson_id, 'ss.status' => 1, 'ssl.is_del' => 0, 'ss.is_del' => 0];
        if (!empty($content_cat)) {
            $where['ss.content_cat'] = $content_cat;
        }
        return parent::$main_db->select('ssl.id, ssl.name, ssl.alias, ssl.teacher_id, ssl.schedule_id, ssl.is_record, ssl.record_link,
            ssl.min_num, ssl.max_num, ssl.start_time, ssl.end_time, ssl.lesson_num, ssl.outline_id,
            ssl.outline_lesson_id, ssl.order ,ss.name as schedule_name, ss.room_name, ss.room_pwd, ss.content_cat, ss.enable_zdtalk')
            ->from('sty_schedule_lesson AS ssl')
            ->join('sty_schedule AS ss', 'ss.id=ssl.schedule_id', 'left')
            ->where($where)->limit(1)->get()->row_array();
    }

    /**
     * 课件总数
     * @param array $w
     */
    public function getScheduleJoinLessonCount($w = [])
    {
        $this->load->model('public/common_model');
        $where = ['ssl.is_del' => 0, 'ss.is_del' => 0];
        if (!empty($w)) $where = array_merge($where, $w);
        $join = ['joinTable' => 'sty_schedule AS ss', 'joinCond' => 'ss.id = ssl.schedule_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_schedule_lesson AS ssl', [
            'where' => $where,
            'count' => TRUE
        ], [$join]);
    }

    /**
     * 判断阶段课程是否是录播
     * @param $scheduleId
     * @return bool
     */
    public function isRecordSchedule($scheduleId)
    {
        $scheduleInfo = $this->getScheduleInfo($scheduleId, 'class_mode');
        if ($scheduleInfo['class_mode'] === '2') {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    /**
     * 判断是否启用ZDTalk，如果启用返回链接
     * @param $uid
     * @param $scheduleInfo 阶段课程信息
     * @param $lessonId
     * @param int $planId
     * @param int $planStageId
     * @return array
     */
    public function enableZDTalk($uid, $scheduleInfo, $lessonId, $planId = 0, $planStageId = 0)
    {
        if ($scheduleInfo['enable_zdtalk'] == '0') {
            return ['enable' => FALSE];
        } else {
            $href = $this->getZDTalkLink($uid, $lessonId);
            return ['enable' => TRUE, 'href' => $href];
        }
    }

    /**
     * 获取zdtalk启动链接
     * @param $uid
     * @param $scheduleId
     * @param $lessonId
     * @param int $planId
     * @param int $planStageId
     * @return string
     */
    public function getZDTalkLink($uid, $lessonId)
    {
        $this->load->library('thrift');
        $zdTalk = $this->thrift->service('ZDtalk')->getZDtalkParameter([
            'uid' => $uid,
            'lessonId' => $lessonId,
            'sessionId' => $this->passport->sessionId(),
            'token' => $this->passport->get('pwd_token'),
            'is_report' => 1
        ]);
        if (!empty($zdTalk) && intval($zdTalk->code) === 200) {
            return 'ZDTalk:' . $zdTalk->data['json'];
        }
        return '';
    }

    /**
     * 用户加入schedule总数
     * @param $uid
     * @return mixed
     */
    public function userJoinScheduleCount($uid)
    {
        return parent::$main_db->from('sty_user_schedule')
            ->where([
                'uid' => $uid,
                'is_del' => 0
            ])
            ->count_all_results();
    }

    /**
     * 已加入的阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     * @return array|bool
     */
    public function joinedSchedule($uid, $plan_id, $plan_stage_id, $schedule_id)
    {
        $this->load->model('public/goods_model');
        $activationProcess = $this->goods_model->activationProcess($uid, $plan_id, $plan_stage_id);
        if ($activationProcess['code'] !== 200) return $activationProcess;
        $verify = $this->validation->validate('get_schedule', [
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id,
            'schedule_id' => $schedule_id
        ]);
        if ($verify !== TRUE) return $verify;
        $this->load->model('public/plan_stage_model');
        $update = $this->plan_stage_model->updateUserPlanStageSelectSchedule($uid, $plan_id, $plan_stage_id, $schedule_id);
        if ($update !== FALSE) {
            $result['code'] = 200;
        }
        return $result;

    }

    /**
     * 加入阶段课程校验
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     * @return array|bool
     */
    protected function joinScheduleVerify($uid, $plan_id, $plan_stage_id, $schedule_id)
    {
        $date = date('Y-m-d H:i:s');
        //验证加入阶段课程参数
        $verify = $this->validation->validate('join_schedule', [
            'uid' => $uid,
            'plan_id' => $plan_id,
            'plan_stage_id' => $plan_stage_id,
            'schedule_id' => $schedule_id
        ]);
        if ($verify !== TRUE) return $verify;
        //阶段课程数据
        $schedule_info = $this->getScheduleInfo($schedule_id, 'id, class_mode, end_time, max_curriculum_num');
        if (!empty($schedule_info)) {
            if (intval($schedule_info['class_mode']) === 1) {
                //直播
                if (date('Y-m-d 23:59:59', strtotime($schedule_info['end_time'])) > $date) {
                    //查询加入此阶段课程的总人数
                    $join_count = $this->joinScheduleCount($schedule_id);
                    if (intval($schedule_info['max_curriculum_num']) > intval($join_count)) {
                        $result['code'] = 200;
                    } else {
                        //此阶段加入人数已满
                        $result['code'] = 230;
                    }
                } else {
                    //阶段课程已结课
                    $result['code'] = 229;
                }
            } else {
                //录播
                $result['code'] = 200;
            }
            if ($result['code'] === 200) {
                $result['is_plan_join_schedule'] = FALSE;
                $result['is_old_join_schedule'] = FALSE;
                //查询当前阶段是否加入过阶段课程
                $this->load->model('public/plan_stage_model');
                $plan_stage = $this->plan_stage_model->getUserStageInfo($uid, $plan_id, $plan_stage_id);
                if (!empty($plan_stage) && $plan_stage['is_join_schedule'] == 1) {
                    $result['is_plan_join_schedule'] = TRUE;
                }
                //阶段课程是否被加入过
                $old_schedule = $this->getUserJoinedSchedule($uid, $schedule_id, 1);
                if (!empty($old_schedule)) {
                    $result['is_old_join_schedule'] = TRUE;
                }
                $result['scheduleInfo'] = $schedule_info;
            }
        } else {
            //此阶段课程不存在
            $result['code'] = 227;
        }
        return $result;
    }

    /**
     * 加入阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     * @param $before -1 直接加入 0 加入阶段课程第一步 1 加入阶段课程第二部
     * @return array|bool
     */
    public function joinScheduleBefore($uid, $plan_id, $plan_stage_id, $schedule_id, $before)
    {
        $this->load->model('public/goods_model');
        $verifySchedule = $this->joinScheduleVerify($uid, $plan_id, $plan_stage_id, $schedule_id);
        if ($verifySchedule['code'] !== 200) return $verifySchedule;
        $activationProcess = $this->goods_model->activationProcess($uid, $plan_id, $plan_stage_id);
        if ($activationProcess['code'] !== 200) return $activationProcess;

        $is_plan_join_schedule = $verifySchedule['is_plan_join_schedule'];
        $is_old_join_schedule = $verifySchedule['is_old_join_schedule'];
        $planStage = $activationProcess['planStage'];
        $scheduleInfo = $verifySchedule['scheduleInfo'];
        if ($is_old_join_schedule) {
            //验证是否存在课程冲突
            $liveVerify = $this->verifyScheduleLive($uid, $scheduleInfo, $planStage, $before);
            if ($liveVerify['code'] !== 200) return $liveVerify;
            $result = $this->insertSchedule($uid, $plan_id, $plan_stage_id, $schedule_id, $is_plan_join_schedule, $is_old_join_schedule);
        } else {
            $num = intval($this->goods_model->getUserAbleNum($uid, 1));
            if ($num === 0 && $is_plan_join_schedule) {
                $result['code'] = 212;
            } else {
                //验证是否存在课程冲突
                $liveVerify = $this->verifyScheduleLive($uid, $scheduleInfo, $planStage, $before);
                if ($liveVerify['code'] !== 200) return $liveVerify;
                if ((!($num === 0 && $is_plan_join_schedule) && $before === -1)
                    || ($before >= 0 && (!$is_plan_join_schedule || $num === -1))
                ) {
                    $result = $this->insertSchedule($uid, $plan_id, $plan_stage_id, $schedule_id, $is_plan_join_schedule, $is_old_join_schedule);
                } else {
                    if ($before === -1) {
                        $result['code'] = 214;
                    } else {
                        if ($is_plan_join_schedule) {
                            //此阶段不是首次添加
                            $result['code'] = 212;
                        }
                    }
                }
            }
            $result['num'] = $num;
        }
        return $result;
    }

    /**
     * 直播课表下 验证是否冲突、有效期
     * @param $uid
     * @param $schedule
     * @param $plan_stage
     * @return array
     */
    protected function verifyScheduleLive($uid, $schedule, $plan_stage, $before)
    {
        $result = ['code' => 200];
        if ($before === 0) {
            //同阶段下加入阶段课程数
            $count = parent::$main_db->from('view_sty_user_plan_stage_join_schedule')
                ->where([
                    'uid' => $uid,
                    'plan_id' => $plan_stage['plan_id'],
                    'plan_stage_id' => $plan_stage['plan_stage_id'],
                ])
                ->where("(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')")
                ->count_all_results();
            if ($count > 0) {
                $result['stage_name'] = '';
                $result['join_count'] = $count;
                $result['code'] = 213;
                $this->load->model('public/plan_stage_model');
                $stage = $this->plan_stage_model->getStageInfo($plan_stage['plan_stage_id']);
                if (!empty($stage)) {
                    $result['stage_name'] = $stage['name'];
                }
                return $result;
            }
        }
        if (intval($schedule['class_mode']) === 1) {
            //是否存在冲突
            $result = $this->scheduleTimeConflict($uid, $schedule['id']);
            if ($result['code'] === 211) {
                return $result;
            }
            //阶段课程有效期是否够学完课程
            //暂时去掉有效期判断
            /*if (intval($plan_stage['unlimit_expire']) !== 1 && $schedule['end_time'] > $plan_stage['expire']) {
                $result['code'] = 254;
            }*/
        }
        return $result;
    }


    /**
     * 上课时间冲突
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     */
    public function scheduleTimeConflict($uid, $schedule_id)
    {
        $result = array('code' => 200, 'html' => '', 'conflict_time' => '');
        $lab = $week_arr = $conflict_arr = [];
        $conflict_time = '';
        $schedule = $this->getScheduleInfo($schedule_id, 'start_time, end_time, class_start_time, class_end_time');
        if (!empty($schedule)) {
            $schedule['class_week_time'] = $this->getScheduleWeekCycle($schedule_id);
            $where = ['ss.class_mode' => 1, 'ss.end_time >' => date('Y-m-d H:i:s'), 'ss.status' => 1];
            $this->load->model('public/plan_model');
            $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
            if (!empty($planHave)) {
                $where['_string'] = '(ss.business_type NOT IN (2, 5))';
            }
            $schedule_lab = $this->getUserScheduleDetial($uid, $where, [
                'ss.name',
                'ss.remark',
                'ss.start_time',
                'ss.end_time',
                'ss.class_start_time',
                'ss.class_end_time'
            ], ['ss.start_time ASC']);

            if (!empty($schedule_lab)) {
                foreach ($schedule_lab as $val) {
                    $val['class_week_time'] = $this->getScheduleWeekCycle($val['id']);
                    if (!($schedule['end_time'] < $val['start_time'] || $schedule['start_time'] > $val['end_time'])) {
                        $intersect = array_intersect($schedule['class_week_time'], $val['class_week_time']);
                        if (!empty($intersect)) {
                            if (intval($schedule['class_start_time']) && intval($schedule['class_end_time'])) {
                                if ($this->conflictsTime($schedule['class_start_time'], $schedule['class_end_time'], $val['class_start_time'], $val['class_end_time'])) {
                                    $week_arr = array_merge($week_arr, $intersect);
                                    $lab[] = $val;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($lab)) {
            if (!empty($week_arr)) {
                foreach ($week_arr as $wv) {
                    if (!in_array($wv, $conflict_arr)) {
                        $conflict_arr[] = $wv;
                    }
                }
                sort($conflict_arr);
                $conflict_time .= implode('、', $conflict_arr);
                $conflict_time .= ' ' . date('H:i', strtotime($schedule['class_start_time'])) . '-' . date('H:i', strtotime($schedule['class_end_time']));
            }
            foreach ($lab as $k => $item) {
                $lab[$k]['teacher_info'] = '';
                $lab[$k]['show_name'] = '';
                $teachers = $this->getScheduleTeachers($item['id']);
                if (!empty($teachers)) {
                    foreach ($teachers as $tv) {
                        if (!empty($tv['name'])) {
                            if ($lab[$k]['teacher_info'] == '') {
                                $lab[$k]['teacher_info'] .= $tv['name'];
                            } else {
                                $lab[$k]['teacher_info'] .= '、' . $tv['name'];
                            }
                        }
                    }
                }
                $lab[$k]['show_name'] = !empty($item['name']) ? $item['name'] : $item['remark'];
            }
            $data['lab'] = $lab;
            $result['code'] = 211;
            $result['data'] = $data;
            $result['conflict_time'] = $conflict_time;
        }
        return $result;
    }

    /**
     * 对比时间信息是否冲突
     * @param array $week
     * @param array $weekTo
     */
    public function conflictsTime($start, $end, $start_contrast, $end_contrast)
    {
        $conflict = TRUE;
        $start = strtotime($start);
        $end = strtotime($end);
        $start_contrast = strtotime($start_contrast);
        $end_contrast = strtotime($end_contrast);
        if ($start && $end && $start_contrast && $end_contrast && $end > $start && $end_contrast > $start_contrast) {
            if ($end_contrast <= $start || $start_contrast >= $end) {
                $conflict = FALSE;
            }
        }
        return $conflict;
    }

    /**
     * 删除冲突的阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $conflict_id
     * @param $conflict_type
     * @param $conflict_desc
     * @return array|bool
     */
    public function delConflictSchedule($uid, $conflict_id, $conflict_type, $conflict_desc)
    {
        $this->load->model('public/business_validation_model');
        $this->load->model('public/goods_model');
        $nowDate = date('Y-m-d H:i:s');
        if ($this->business_validation_model->validateUserJoinedSchedule($uid, $conflict_id) === TRUE) {
            $schedule_info = $this->getScheduleInfo($conflict_id, 'class_mode, start_time');
            if (!empty($schedule_info)) {
                parent::$main_db->trans_start();
                //删除理由
                $this->delScheduleReason($uid, $conflict_id, $conflict_type, $conflict_desc);
                //删除阶段课程
                $this->delSchedule($uid, $conflict_id);
                if (intval($schedule_info['class_mode']) == 1) {
                    if ($schedule_info['start_time'] > $nowDate) {
                        //验证加入时是否扣过次数
                        if (intval($this->goods_model->deductUserScheduleNum($uid, $conflict_id))) {
                            //是否返还次数
                            $this->goods_model->giveBackNum($uid, 1, $conflict_id);
                        }
                    }
                }
                //更新学管师记录
                $this->goods_model->save_teach_task($uid);
                //新增用户行为
                parent::$main_db->insert('sty_user_schedule_action', [
                    'uid' => $uid,
                    'schedule_id' => $conflict_id,
                    'type' => 2,
                    'create_time' => $nowDate
                ]);
                parent::$main_db->trans_complete();
                if (parent::$main_db->trans_status() !== FALSE) {
                    //kafka生产消费数据
                    $this->load->library('thrift');
                    $this->thrift->service('Kafka')->producer('sasschedule', 'userJoinSchedule', [
                        'uid' => $uid,
                        'schedule_id' => $conflict_id,
                        'join' => FALSE,
                        'dateTime' => $nowDate
                    ]);
                    $result['code'] = 200;
                } else {
                    $result['code'] = 500;
                }
            } else {
                $result['code'] = 227;
            }

        } else {
            $result['code'] = 250;
        }
        return $result;
    }

    /**
     * 添加阶段课程
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $schedule_id
     * @return array
     */
    public function insertSchedule($uid, $plan_id, $plan_stage_id, $schedule_id, $is_plan_join_schedule = TRUE, $is_old_join_schedule = FALSE)
    {
        $result = array('code' => 0);
        $staff = 0;
        $this->load->model('public/member_model');
        $this->load->model('public/plan_stage_model');
        $this->load->model('public/goods_model');
        //验证内部员工
        if ($this->member_model->validateIsStaff($uid) !== FALSE) {
            $staff = 1;
        }
        $inserts = array('uid', 'schedule_id', 'staff');
        $updates = array('staff', 'is_del', 'modify_time');
        $params = array('uid' => $uid, 'schedule_id' => $schedule_id, 'staff' => $staff, 'is_del' => 0);
        $params['modify_time'] = $params['create_time'] = date('Y-m-d H:i:s');
        parent::$main_db->trans_start();
        $this->upsertSchedule($inserts, $updates, $params);
        $this->plan_stage_model->updateUserPlanStageSelectSchedule($uid, $plan_id, $plan_stage_id, $schedule_id, TRUE);
        if ($is_plan_join_schedule && !$is_old_join_schedule) {
            $this->goods_model->deductNum($uid, 1, $schedule_id);
        }
        //更新学管师记录
        $this->goods_model->save_teach_task($uid);
        //新增用户行为
        parent::$main_db->insert('sty_user_schedule_action', [
            'uid' => $uid,
            'schedule_id' => $schedule_id,
            'type' => 1,
            'create_time' => $params['create_time']
        ]);
        parent::$main_db->trans_complete();
        if (parent::$main_db->trans_status() !== FALSE) {
            //Kafka生产消费数据
            $this->load->library('thrift');
            $this->thrift->service('Kafka')->producer('sasschedule', 'userJoinSchedule', [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'plan_id' => $plan_id,
                'plan_stage_id' => $plan_stage_id,
                'join' => TRUE,
                'dateTime' => $params['create_time']
            ]);
            $result['code'] = 200;
        } else {
            $result['code'] = 500;
        }
        return $result;
    }

    //更新当前阶段课程
    public function updateCurrentSchedule($uid, $plan_id, $plan_stage_id, $schedule_id)
    {
        $nowDate = date('Y-m-d H:i:s');
        $this->load->model('public/plan_stage_model');
        parent::$main_db->trans_start();
        parent::$main_db->set(['last_learn_time' => $nowDate, 'modify_time' => $nowDate])->where(['uid' => $uid, 'schedule_id' => $schedule_id])->update('sty_user_schedule');
        $this->plan_stage_model->updateUserPlanStageSelectSchedule($uid, $plan_id, $plan_stage_id, $schedule_id);
        parent::$main_db->trans_complete();
        if (parent::$main_db->trans_status() !== FALSE) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 用户行为数据
     * @param $action_id
     * @return mixed
     */
    public function userScheduleAction(array $w = [])
    {
        return parent::$main_db->select('uid, schedule_id, lesson_id, test_id, unit_id, goods_id, type, process, internal_staff, create_time')
            ->from('sty_user_schedule_action')->where($w)->limit(1)->get()->row_array();
    }

    /**
     * 处理用户行为数据
     * @param $action_id
     * @return array
     */
    public function handleUserScheduleAction($action_id)
    {
        $result = ['code' => 286, 'msg' => NULL];
        $action = $this->userScheduleAction(['id' => $action_id, 'process' => 0]);
        if (!empty($action)) {
            $this->load->model('public/member_model');
            $staff = $this->member_model->validateIsStaff($action['uid']);
            if ($staff === TRUE) {
                //内部员工
                $result['code'] = 201;
                parent::$main_db->where(['id' => $action_id])->update('sty_user_schedule_action', ['process' => 2, 'internal_staff' => 1]);
            } else {
                if (intval($action['type']) === 8) {
                    $this->load->model('public/goods_model');
                    $handleData = $this->goods_model->getGoodsInfo($action['uid'], $action['goods_id']);
                } else {
                    $handleData = $this->getScheduleCacheData($action['schedule_id']);
                }
                if (!empty($handleData)) {
                    $this->load->model('public/lesson_model');
                    parent::$main_db->trans_begin();
                    switch (intval($action['type'])) {
                        case 1:
                            $result = $this->handleJoinScheduleAction($handleData, $action['create_time']);
                            break;
                        case 2:
                            $result = $this->handleJoinScheduleAction($handleData, $action['create_time'], TRUE);
                            break;
                        case 3:
                            $result = $this->saveReservationShouldReport($action['lesson_id']);
                            break;
                        case 4:
                            $result = $this->saveReservationShouldReport($action['lesson_id']);
                            break;
                        case 5:
                            $major = FALSE;
                            if (intval($handleData['curricular_system']) === 1) $major = TRUE;
                            $result = $this->lesson_model->handleLessonReportAction($action['uid'], $action['schedule_id'], $action['lesson_id'], $action['create_time'], $major);
                            break;
                        case 6:
                            $result = $this->lesson_model->handleLessonAnswerAction($action['schedule_id'], $action['lesson_id'], TRUE);
                            break;
                        case 7:
                            $result = $this->lesson_model->handleLessonAnswerAction($action['schedule_id'], $action['lesson_id'], FALSE);
                            break;
                        case 8:
                            $result['code'] = 200;
                            break;
                        default:
                    }
                    if (intval($result['code']) === 200) {
                        $save_process = parent::$main_db->update('sty_user_schedule_action', [
                            'process' => 1,
                            'process_time' => date('Y-m-d H:i:s')
                        ], ['id' => $action_id]);
                        if ($save_process === FALSE) {
                            $result['code'] = 500;
                            $result['msg'] = 'error SQL:' . parent::$main_db->last_query();
                        }
                    }
                    if (intval($result['code']) === 200 && parent::$main_db->trans_status() !== FALSE) {
                        parent::$main_db->trans_commit();
                    } else {
                        parent::$main_db->trans_rollback();
                    }
                }
            }
        } else {
            $result['msg'] = 'action NULL';
        }
        return $result;
    }

    /**
     * 处理加入或删除阶段课程的更新加入人数和结课数
     * @param $schedule
     * @param bool $del
     * @return mixed
     */
    public function handleJoinScheduleAction($schedule, $action_time, $del = FALSE)
    {
        $result = ['code' => 500, 'msg' => NULL];
        $this->load->model('public/lesson_model');
        $schedule_id = intval($schedule['id']);
        $class_mode = intval($schedule['class_mode']);
        $join_count = $this->joinScheduleCount($schedule_id);
        $saveJoin = parent::$main_db->set('join_people_number', $join_count)->where(['id' => $schedule_id])->update('sty_schedule');
        if ($saveJoin !== FALSE) {
            $lessson_ids = [];
            $order = [];
            $w['ssl.schedule_id'] = $schedule_id;
            if ($class_mode === 1) {
                if ($del === TRUE) {
                    $w['ssl.start_time >='] = $action_time;
                } else {
                    $w['ssl.end_time >'] = date('Y-m-d H:i:s', strtotime($action_time) - 60 * 90);
                }
                $order = ['ssl.start_time ASC'];
            }
            $lessson = $this->lesson_model->getScheduleLessonDetail($w, [], $order);
            if (!empty($lessson)) {
                foreach ($lessson as $item) {
                    $lessson_ids[] = $item['id'];
                }
                $saveShould = parent::$main_db->set('should_report_number', $join_count)->where_in('id', $lessson_ids)->update('sty_schedule_lesson');
                if ($saveShould !== FALSE) {
                    $result['code'] = 200;
                } else {
                    $result['msg'] = 'update should_report_number fail, error Sql:' . parent::$main_db->last_query();
                }
            } else {
                $result['code'] = 200;
            }
        } else {
            $result['msg'] = 'update join_people_number fail, error Sql:' . parent::$main_db->last_query();
        }
        return $result;
    }

    /**
     * 更新lesson应报到人数
     * @param $lesson_id
     * @param bool $cancel
     * @return mixed
     */
    public function saveReservationShouldReport($lesson_id)
    {
        $result = ['code' => 500, 'msg' => NULL];
        $reservation_count = parent::$main_db->from('sty_user_schedule_lesson')
            ->where([
                'schedule_lesson_id' => $lesson_id,
                'reservation_status' => 1,
                'staff' => 0,
                'is_del' => 0
            ])
            ->count_all_results();
        $saveShould = parent::$main_db->where(['id' => $lesson_id])->update('sty_schedule_lesson', ['should_report_number' => $reservation_count]);
        if ($saveShould !== FALSE) {
            $result['code'] = 200;
        } else {
            $result['msg'] = 'update should_report_number fail, error Sql:' . parent::$main_db->last_query();
        }
        return $result;
    }

    /**
     * 用户可用schedule
     * @param $uid
     * @return array
     */
    public function getUserAbleScheduleArr($uid)
    {
        //获取未过期阶段下的直播schedule_id
        $ableScheduleArr = [];
        $schedule_stage = $this->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')",
        ], ['schedule_id']);
        if (!empty($schedule_stage)) {
            foreach ($schedule_stage as $sps) {
                if (!in_array($sps['schedule_id'], $ableScheduleArr)) {
                    $ableScheduleArr[] = $sps['schedule_id'];
                }
            }
        }
        return $ableScheduleArr;
    }

    /**
     * table sty_schedule sty_schedule_stage_item
     * @param array $w
     * @param array $f
     * @param array $order
     * @return mixed
     */
    public function getScheduleStageDetial(array $w = [], array $f = [], array $order = [])
    {
        $this->load->model('public/common_model');
        $where = ['ss.is_del' => 0, 'sssi.is_del' => 0];
        $field = ['ss.id', 'sssi.plan_id', 'sssi.plan_stage_id'];
        if (!empty($w)) $where = array_merge($where, $w);
        if (!empty($f)) $field = array_merge($field, $f);
        $join = ['joinTable' => 'sty_schedule_stage_item AS sssi', 'joinCond' => 'sssi.schedule_id = ss.id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_schedule AS ss', [
            'field' => $field,
            'where' => $where,
            'order' => $order
        ], [$join]);
    }

    /**
     * sty_user_schedule left join sty_schedule
     * @param $uid
     * @param array $w
     * @param array $f
     * @param array $order
     * @param array $limit
     * @return mixed
     */
    public function getUserScheduleDetial($uid, array $w = [], array $f = [], array $order = [], array $limit = [])
    {
        $this->load->model('public/common_model');
        $where = ['sus.uid' => $uid, 'sus.is_del' => 0, 'ss.is_del' => 0];
        $field = ['ss.id'];
        if (!empty($w)) $where = array_merge($where, $w);
        if (!empty($f)) $field = array_merge($field, $f);
        $join = ['joinTable' => 'sty_schedule as ss', 'joinCond' => 'ss.id = sus.schedule_id', 'joinType' => 'left'];
        return $this->common_model->selectObjData('sty_user_schedule as sus', [
            'field' => $field,
            'where' => $where,
            'order' => $order,
            'limit' => $limit
        ], [$join]);
    }

    /**
     * 用户计划阶段加入阶段课程信息
     * @param array $w
     * @param array $field
     * @param array $order
     * @param array $limit
     * @return mixed
     */
    public function viewUserPlanStageJoinSchedule(array $where = [], array $field = [], array $order = [], array $limit = [])
    {
        $this->load->model('public/common_model');
        return $this->common_model->selectObjData('view_sty_user_plan_stage_join_schedule', [
            'field' => $field,
            'where' => $where,
            'order' => $order,
            'limit' => $limit
        ]);
    }

    /**
     * 阶段课程缓存数据
     * @param $schedule_id
     * @param bool $cache
     * @return array
     */
    public function getScheduleCacheData($schedule_id, $cache = FALSE)
    {
        $this->load->driver('cache');
        $key = sprintf(get_options('baseDataCacheKey', 'schedule'), $schedule_id);
        if ($cache === TRUE && BASE_DATA_CACHE_OPEN === TRUE) {
            $cacheData = $this->cache->redis->hGetAll($key);
            if (!empty($cacheData)) return $cacheData;
        }
        $data = parent::$main_db->from('sty_schedule')->where([
            'id' => $schedule_id
        ])->limit(1)->get()->row_array();
        if (!empty($data)) {
            if (BASE_DATA_CACHE_OPEN === TRUE) $this->cache->redis->hMset($key, $data);
            return $data;
        }
        return [];
    }

    /**
     * 检查用户当周哪天有课
     * @param $uid
     * @param $day
     * @return array
     */
    public function checkUserHasLessonByWeek($uid, $day)
    {
        $result = [];
        //获取一周内数据
        if (empty($day) && empty(strtotime($day))) {
            $day = date('Y-m-d');
        }
        $this->load->library('thrift');
        $weekDate = $this->onWeekDate($day);
        $dateMapInfo = $this->thrift->service('Learn')->getLiveLessonClassDate($uid, ['start' => $weekDate[0]['date'], 'end' => end($weekDate)['date']]);
        foreach ($weekDate as $wk => $wv) {
            $weekDate[$wk]['hasCourse'] = FALSE;
            if (in_array(date('Y-m-d', strtotime($wv['date'])), $dateMapInfo)) {
                $weekDate[$wk]['hasCourse'] = TRUE;
            }
        }
        $result['weekDate'] = $weekDate;
        return $result;
    }

    /**
     * 获取一周内的时间
     * @param $date
     * @return array
     */
    public function onWeekDate($date)
    {
        $result = [];
        $week_day = ['日','一', '二','三','四','五','六'];
        $nowData = date('Y-m-d');
        if (empty($date) && empty(strtotime($date))) {
            $date = $nowData;
        }
        for ($i = 0; $i < 7; $i++) {
            if ($i == 0) {
                $result[$i]['date'] = date('Y-m-d', strtotime($date . ' -1 day'));
            } elseif ($i == 1) {
                $result[$i]['date'] = $date;
            } else {
                $result[$i]['date'] = date('Y-m-d', strtotime($date . ' +' . ($i - 1) . ' day'));;
            }
            $result[$i]['week'] = $week_day[date('w', strtotime($result[$i]['date']))];
        }
        return $result;
    }
}