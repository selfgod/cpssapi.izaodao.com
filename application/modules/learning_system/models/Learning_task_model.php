<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learning_task_model extends MY_Model
{
    /**
     * 获取某一天的上课任务列表信息
     * @param $uid
     * @param $schedule_id
     * @param $date
     * @param $plan
     * @param $planStage
     * @return array
     */
    public function getScheduleLessons($uid, $schedule_id, $date, $plan, $planStage)
    {
        $datetimeLow = $date . ' 00:00:00';
        $datetimeHigh = $date . ' 23:59:59';
        $totalTask = 0;
        $isAllDone = TRUE;
        $current = time();

        $this->load->model('public/schedule_model');
        $scheduleInfo = $this->schedule_model->getScheduleInfo($schedule_id, 'enable_zdtalk');

        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        //课件基本信息
        $query = parent::$main_db->select('ssle.id, ssle.name, ssle.alias,
                ssle.start_time, ssle.end_time, ssle.test_id, ssle.unit_id, 
                zti.title as teacher_name, zti.desPic2, zti.smallPic, susl.check_in_status, susl.check_in_time, 
                susl.test_time, susl.unit_time, ssle.is_record, ssle.record_link, 
                ssle.record_download, susl.check_in_type, susl.check_in_score, susl.test_status, 
                susl.test_score, susl.unit_status, susl.unit_score, susl.unit_type')
            ->from('sty_schedule_lesson ssle')
            ->join('zd_class.zd_teacher_info zti', 'zti.uid = ssle.teacher_id', 'left')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0',
                'left')
            ->where(array(
                'ssle.schedule_id' => $schedule_id,
                'ssle.start_time > ' => $datetimeLow,
                'ssle.start_time < ' => $datetimeHigh,
                'ssle.is_del' => 0
            ))
            ->order_by('ssle.start_time', 'ASC')
            ->get();

        $lessons = [];
        $this->load->model('exercise_model');
        foreach ($query->result_array() as $lesson) {
            //教师头像
            $lesson['smallPic'] = !empty($lesson['desPic2']) ? MASTER_DOMAIN . $lesson['desPic2'] : (!empty($lesson['smallPic']) ? MASTER_DOMAIN . $lesson['smallPic'] : '');
            $totalTask++;

            //获取对应的一课一练，单元测试信息
            if (!empty($lesson['test_id'])) {
                $totalTask++;
                $practice = $this->exercise_model->getUserPracticeInfo($lesson['test_id'], $uid, TRUE);
                $lesson['test_item_count'] = empty($practice) ? 0 : $practice['total_count'];
                $lesson['test_total_time'] = empty($practice) ? 0 : $practice['total_time'];
                if (isset($practice['already_did'])) {
                    $lesson['test_last_time'] = $practice['time_diff'];
                }
                if (isset($practice['score'])) {
                    $lesson['test_complete'] = TRUE;
                    $lesson['test_complete_date'] = date('Y/m/d', strtotime($practice['finish_time']));
                    //是否是在当同一个阶段课程下完成的
                    if ($schedule_id === intval($practice['schedule_id'])
                    ) {
                        $lesson['test_current_finish'] = TRUE;
                    }
                }
            }
            if (!empty($lesson['unit_id'])) {
                $totalTask++;
                $unitTest = $this->exercise_model->getUserUnitTestInfo($lesson['unit_id'], $uid, TRUE);
                $lesson['ontime_unit'] = $this->exercise_model->isOnTimeUnitTest(null, $unitTest['exam_type'], false);
                if ($lesson['ontime_unit'] && $current < strtotime($lesson['start_time']) - 1800) {
                    $lesson['ontime_unit_notstart'] = TRUE;
                }
                //按时完成类型单元测试超过30min进入考场提示
                if ($current > strtotime($lesson['start_time']) + 1800 &&
                    $current < strtotime($lesson['start_time']) + 3600) {
                    $lesson['over_30min'] = TRUE;
                }
                $lesson['unit_item_count'] = empty($unitTest) ? 0 : $unitTest['total_count'];
                $lesson['unit_total_time'] = empty($unitTest) ? 0 : $unitTest['total_time'];
                if (isset($unitTest['score'])) {
                    $lesson['unit_complete'] = TRUE;
                    $lesson['unit_complete_date'] = date('Y/m/d', strtotime($unitTest['finish_time']));
                    //是否是在当同一个阶段课程下完成的
                    if ($schedule_id === intval($unitTest['schedule_id'])
                    ) {
                        $lesson['unit_current_finish'] = TRUE;
                    }
                }
                if (isset($unitTest['already_did'])) {
                    $lesson['unit_last_time'] = $unitTest['time_diff'];
                    $lesson['unit_result_id'] = $unitTest['result_id'];
                }
            }
            //课程名
            if (!empty($lesson['alias'])) {
                $lesson['name'] = $lesson['alias'];
            }
            //当天任务是否全部完成
            if ($isAllDone && ($lesson['check_in_status'] !== '1' ||
                    !isset($lesson['test_complete']) ||
                    !isset($lesson['unit_complete']))
            ) {
                $isAllDone = FALSE;
            }

            $lesson['start_timestamp'] = strtotime($lesson['start_time']);
            $lesson['end_timestamp'] = strtotime($lesson['end_time']);
            $lesson['beauty_start_time'] = date('H:i', $lesson['start_timestamp']);
            $lesson['beauty_end_time'] = date('H:i', $lesson['end_timestamp']);

            $endTime = strtotime($lesson['end_time']);
            if ($lesson['check_in_status'] === '1' &&
                strtotime($lesson['check_in_time']) < $endTime + (90 * 60)
            ) {
                $lesson['ontime'] = TRUE;
            } elseif ($lesson['check_in_status'] !== '1' && $current < $endTime + (90 * 60)) {
                $lesson['ontime'] = TRUE;
            }
            //是否使用ZDTalk
            $scheduleInfo['schedule_id'] = $schedule_id;
            $lesson['enable_zdtalk'] = $this->schedule_model->enableZDTalk($uid, $scheduleInfo, $lesson['id'], $plan, $planStage);
            $lessons['lessons'][] = $lesson;
        }
        if (!empty($lessons)) {
            $lessons['total'] = $totalTask;
            $lessons['allDone'] = $isAllDone;
            $lessons['current'] = $current;
            $lessons['zd_download'] = get_options('zdtalk_download');
        }

        return $lessons;
    }

    /**
     * 获取一段时间的上课安排
     * @param string $firstDay 第一天
     * @param string $lastDay 最后一天
     * @param int $schedule_id 阶段课程id
     * @return array
     */
    public function getLessonSchedule($firstDay, $lastDay, $schedule_id)
    {
        $query = parent::$main_db->select('id, start_time')
            ->from('sty_schedule_lesson')
            ->where(array(
                'schedule_id' => $schedule_id,
                'start_time > ' => $firstDay,
                'start_time < ' => $lastDay,
                'is_del' => 0
            ))
            ->get();

        $scheduleList = [];
        foreach ($query->result_array() as $schedule) {
            $date = date('Y-m-d', strtotime($schedule['start_time']));
            $scheduleList[] = $date;
        }
        return $scheduleList;
    }

    /**
     * 获取前后七天直播上课任务日历
     * @param $cur_date
     * @param $schedule_id
     * @return array
     */
    public function getScheduleCalendar($cur_date, $schedule_id)
    {
        $date = new DateTime($cur_date);
        if (!$date) {
            $date = new DateTime();
        }

        $calendar = [];
        $date->modify('-1 day');
        $recentlyDate = $this->generateNearlyDate();
        $firstDay = $date->format('Y-m-d 00:00:00');
        for ($i = 0; $i < 7; $i++) {
            $day = $date->format('Y-m-d');
            $titleDate = $date->format('m-d');
            $key = $day;
            if (isset($recentlyDate[$titleDate])) {
                //显示昨天、今天、明天、后天
                $titleDate = $recentlyDate[$titleDate];
            }
            $calendar[$i]['date'] = $key;
            $calendar[$i]['title'] = sprintf('%s(%s)',
                $titleDate, get_options('week_day', $date->format('w'))
            );
            $date->modify('+1 day');
        }
        $date->modify('-1 day');
        $lastDay = $date->format('Y-m-d 23:59:59');
        //查看当天是否有课
        $arrangement = $this->getLessonSchedule($firstDay, $lastDay, $schedule_id);
        foreach ($calendar as $index => $value) {
            if (in_array($calendar[$index]['date'], $arrangement)) {
                $calendar[$index]['planned'] = 1;
            }
        }

        return $calendar;
    }

    /**
     * 获取最近四天日期
     * @return array
     */
    public function generateNearlyDate()
    {
        $dateArr = [];
        $date = new DateTime();
        $date->modify('-1 day');
        for ($i = 0; $i < 4; $i++) {
            $dateArr[$date->format('m-d')] = get_options('recently_date', $i);
            $date->modify('+1 day');
        }
        return $dateArr;
    }

    /**
     * 获取用户课表当月日历显示信息
     * @param $schedule_id
     * @param $uid
     * @param $year_month '2016-05'
     * @return array
     */
    public function getUserMonthlyCalendar($schedule_id, $uid, $year_month)
    {
        $firstDay = $year_month . '-01 00:00:00';
        $lastDay = date('Y-m-t 23:59:59', strtotime($year_month));
        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        $query = parent::$main_db->select('ssle.start_time, ssle.test_id,
          ssle.unit_id, susl.check_in_status,
            susl.test_status, susl.unit_status')
            ->from('sty_schedule_lesson ssle')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0', 'left')
            ->where(array(
                'ssle.schedule_id' => $schedule_id,
                'ssle.start_time > ' => $firstDay,
                'ssle.start_time < ' => $lastDay,
                'ssle.is_del' => 0
            ))
            ->get();
        $currentDay = date('Y-m-d');
        $result = [];//0-未来的课程没有报到 1-之前的课程任务已完成 2-之前的课程任务未完成
        foreach ($query->result_array() as $schedule) {
            $timestamp = strtotime($schedule['start_time']);
            $day = date('j', $timestamp);
            $startDay = date('Y-m-d', $timestamp);
            if ($startDay > $currentDay) {
                $result[$day] = 0;
            } else {
                //上课任务已报到，而且如果有一课一练或单元测试的话也都是已完成的状态，才算当天任务完成
                if (empty($schedule['check_in_status'])
                    || (!empty($schedule['test_id']) && empty($schedule['test_status']))
                    || (!empty($schedule['unit_id']) && empty($schedule['unit_status']))
                ) {
                    $result[$day] = 2;
                } else {
                    $result[$day] = 1;
                }
            }
        }

        return $result;
    }

    /**
     * 获取录播回顾列表
     * @param $uid
     * @param $schedule_id
     * @param int $pageNum
     * @param $pageCount
     * @param $type 0 全部|1 已完成|2 未完成
     * @return array
     */
    public function recordReviewList($uid, $schedule_id, $pageNum = 1, $pageCount, $type = 0)
    {
        $offset = ($pageNum - 1) * $pageCount;
        $where = array(
            'ssle.schedule_id' => $schedule_id,
            'ssle.is_del' => 0
        );
        $this->load->model('public/schedule_model');
        $isRecord = $this->schedule_model->isRecordSchedule($schedule_id);

        if (!$isRecord) {
            //只统计下课后一个半小时之后
            $cur_time = time() - 90 * 60;
            $where['ssle.end_time < '] = date('Y-m-d H:i:s', $cur_time);
            //过滤掉没有设置结束时间的课程
            $where['ssle.end_time >'] = '1000-01-01 00:00:00';
        }

        if ($type === 1) {
            //已完成
            $where['susl.check_in_status'] = 1;
        }

        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        //课件基本信息
        parent::$main_db->select('ssle.id, ssle.name, ssle.alias,
                ssle.start_time, zti.title as teacher_name, zti.desPic2, zti.smallPic,
                susl.check_in_status, susl.check_in_type, susl.check_in_score,
                susl.check_in_time, ssle.record_link, ssle.record_download')
            ->join('zd_class.zd_teacher_info zti', 'zti.uid = ssle.teacher_id', 'left')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0',
                'left')
            ->where($where);

        if ($type === 2) {
            //未完成
            parent::$main_db->where('(susl.check_in_status is NULL OR susl.check_in_status = 0)');
        }

        $totalCount = parent::$main_db->count_all_results('sty_schedule_lesson ssle', FALSE);
        if ($isRecord) {//录播阶段课程没有开始时间
            parent::$main_db->order_by('ssle.order', 'ASC');
        } else {
            parent::$main_db->order_by('ssle.start_time', 'DESC');
        }
        $query = parent::$main_db->limit($pageCount, $offset)->get();
        $results = [];
        foreach ($query->result_array() as $record) {
            $record['smallPic'] = !empty($record['desPic2']) ? MASTER_DOMAIN . $record['desPic2'] : (!empty($record['smallPic']) ? MASTER_DOMAIN . $record['smallPic'] : '');
            if (!empty($record['alias'])) {
                $record['name'] = $record['alias'];
            }
            if (!$isRecord) {
                $record['beauty_start_date'] = date('Y/m/d', strtotime($record['start_time']));
            }
            if ($record['check_in_status'] === '1') {
                $record['beauty_checkin_date'] = date('Y/m/d', strtotime($record['check_in_time']));
            }
            $results['data'][] = $record;
        }
        if (!empty($results)) {
            $results['total_count'] = $totalCount;
            $results['total_page'] = intval(ceil($totalCount / $pageCount));
            $results['page_limit'] = $pageCount;
            $results['current_page'] = $pageNum;
            $results['is_record'] = $isRecord;
        }
        return $results;
    }

    /**
     * 获取最新的录播回顾未完成记录
     * @param $uid
     * @param $schedule_id
     */
    public function latestRecord($uid, $schedule_id)
    {
        //只统计下课后一个半小时之后
        $cur_time = time() - 90 * 60;
        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        $query = parent::$main_db->select('ssle.id, ssle.start_time, ssle.end_time')
            ->from('sty_schedule_lesson ssle')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0',
                'left')
            ->where(array(
                'ssle.schedule_id' => $schedule_id,
                'ssle.is_del' => 0,
                'ssle.end_time < ' => date('Y-m-d H:i:s', $cur_time)
            ))
            ->where('(susl.check_in_status is NULL OR susl.check_in_status = 0)')
            ->order_by('ssle.start_time', 'DESC')
            ->limit(1)
            ->get();
        return $query->row_array();
    }


    /**
     * 班级信息
     * @param $schedule_lesson_id
     * @return array
     */
    public function classroomInfo($schedule_lesson_id)
    {
        $query = parent::$main_db->select('sl.start_time, sl.name as lesson_name,
            sl.alias as lesson_alias, ss.name, ss.room_name, ss.room_pwd')
            ->from('sty_schedule_lesson sl')
            ->join('sty_schedule ss', 'ss.id = sl.schedule_id', 'left')
            ->where(array(
                'sl.id' => $schedule_lesson_id,
                'sl.is_del' => 0,
                'ss.is_del' => 0
            ))
            ->limit(1)
            ->get();
        if ($classroomInfo = $query->row_array()) {
            $start_time = date('Y年m月d日 H:i', strtotime($classroomInfo['start_time']));
            $classroomInfo['start_time'] = $start_time;
            $classroomInfo['yy'] = get_options('yy_rome');
        }
        return $classroomInfo;
    }

    /**
     * 下一次上课提醒
     * @param $uid
     * @param $schedule_id
     * @param $cur_date
     * @param $planId
     * @param $stageId
     * @return array
     */
    public function nextLessonWarning($uid, $schedule_id, $cur_date, $planId, $stageId)
    {
        $date = new DateTime($cur_date);
        $date->modify('+1 day');
        $beginTime = $date->format('Y-m-d 00:00:00');

        $query = parent::$main_db->select('sl.id, sl.name, sl.alias,
                sl.start_time, zti.name t_name')
            ->from('sty_schedule_lesson sl')
            ->join('zd_class.zd_teacher_info zti', 'sl.teacher_id = zti.uid', 'left')
            ->where(array(
                'sl.schedule_id' => $schedule_id,
                'sl.start_time >' => $beginTime,
                'sl.is_del' => 0
            ))
            ->order_by('sl.start_time', 'ASC')
            ->limit(1)
            ->get();

        $result = array();
        if ($nextLesson = $query->row_array()) {
            $start_time = strtotime($nextLesson['start_time']);
            $week = convert_keys_2_vaules('week_day', array(date('w', $start_time)));
            $startDate = date('n月d日', $start_time);
            $nextLesson['start_time'] = $startDate . " (周$week[0]) " . date('H:i', $start_time);
            $nextLesson['start_timestamp'] = $start_time;
            if (!empty($nextLesson['alias'])) {
                $nextLesson['name'] = $nextLesson['alias'];
            }

            //是否使用ZDTalk
            $this->load->model('public/schedule_model');
            $scheduleInfo = $this->schedule_model->getScheduleInfo($schedule_id, 'enable_zdtalk');
            $scheduleInfo['schedule_id'] = $schedule_id;
            $nextLesson['enable_zdtalk'] = $this->schedule_model->enableZDTalk($uid, $scheduleInfo, $nextLesson['id'], $planId, $stageId);
            return $nextLesson;
        }
        return $result;
    }

    /**
     * 根据当前时间和下课时间获取报到所得学分
     * 在下课前半小时到下课后一个半小时之间报到，得5分
     * @param $schedule_lesson_id
     * @return int
     */
    public function getCheckInScore($schedule_lesson_id)
    {
        if ($lesson = $this->getScheduleLessonInfo($schedule_lesson_id)) {
            $current = time();
            $end_time = strtotime($lesson['end_time']);

            if ($current > $end_time - 1800 &&
                $current < $end_time + 5400
            ) {
                return 5;
            }
        }
        return 0;
    }

    /**
     * 获取阶段课程课件基本信息
     * @param $schedule_lesson_id
     * @return bool|array
     */
    public function getScheduleLessonInfo($schedule_lesson_id)
    {
        $schedule_lesson_Info = parent::$main_db->select('schedule_id, min_num, start_time, end_time,
        record_link, record_download, name, alias, teacher_id, outline_lesson_id, test_id, unit_id')
            ->from('sty_schedule_lesson')
            ->where(array(
                'id' => $schedule_lesson_id,
                'is_del' => 0
            ))
            ->limit(1)
            ->get()->row_array();

        return $schedule_lesson_Info;
    }

    /**
     * 获取用户通知小红点
     * @param $uid
     * @param $scheduleId
     * @return array
     */
    public function userNoticeData($uid, $scheduleId)
    {
        $this->load->model('public/schedule_model');
        $this->load->model('exercise_model');
        $scheduleInfo = $this->schedule_model->getScheduleInfo($scheduleId, 'class_mode');
        $data = [];
        $lastItems = [];
        if (!empty($scheduleInfo)) {
            if ($scheduleInfo['class_mode'] === '2') {
                $data['record'] = $data['practice'] = $data['unit'] = 0;
            } else {
                $dotInfo = $this->getUserDotInfo($uid, $scheduleId);
                $lastItems['record'] = $this->latestRecord($uid, $scheduleId);
                //做题任务/一课一练/未完成
                $lastItems['practice'] = $this->exercise_model->latestExercise($scheduleId, $uid, 'test');
                //做题任务/单元测试/未完成
                $lastItems['unit'] = $this->exercise_model->latestExercise($scheduleId, $uid, 'unit');

                foreach ($lastItems as $type => $item) {
                    if (empty($dotInfo) && !empty($item)) {
                        $data[$type] = 1;//有红点
                        continue;
                    } elseif (!empty($dotInfo) && !empty($item)) {
                        if ($dotInfo[$type] < strtotime($item['start_time'])) {
                            $data[$type] = 1;
                            continue;
                        }
                    }
                    $data[$type] = 0;//没红点
                }
            }
        }
        return $data;
    }

    /**
     * 获取用户红点通知记录
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function getUserDotInfo($uid, $scheduleId)
    {
        $query = parent::$main_db->select('report_starttime, practice_starttime, exam_starttime')
            ->from('studytask_set_dot')
            ->where(array(
                'schedule_id' => $scheduleId,
                'uid' => $uid
            ))
            ->limit(1)
            ->get();
        $dotInfo = $query->row_array();
        $result = [];
        if ($dotInfo) {
            $result['record'] = $dotInfo['report_starttime'];
            $result['practice'] = $dotInfo['practice_starttime'];
            $result['unit'] = $dotInfo['exam_starttime'];
        }
        return $result;
    }

    /**
     * 更新用户红点通知记录
     * @param $uid
     * @param $scheduleId
     * @param $type
     */
    public function updateDotHistory($uid, $scheduleId, $type)
    {
        if ($type === 'test' || $type === 'unit') {
            $now = strtotime(date('Y-m-d 23:59:59'));
        } else {
            $now = time();
        }
        $type = get_options('dotNoticField', $type);
        if ($type === FALSE) {
            return;
        }
        parent::$main_db->where(array(
            'uid' => $uid,
            'schedule_id' => $scheduleId
        ));
        $exist = parent::$main_db->count_all_results('studytask_set_dot');

        if ($exist) {
            parent::$main_db->set(array(
                $type => $now,
                'dateline' => $now
            ))->where(array(
                'uid' => $uid,
                'schedule_id' => $scheduleId
            ))->update('studytask_set_dot');
        } else {
            parent::$main_db->set(array(
                'uid' => $uid,
                'schedule_id' => $scheduleId,
                $type => $now,
                'dateline' => $now
            ))->insert('studytask_set_dot');
        }
    }


    /**
     * 在阶段课程下已经完成过某一个课件
     * @param $uid
     * @param $scheduleId
     * @return bool
     */
    public function alreadyCompletedOne($uid, $scheduleId)
    {
        $day = date('Y-m-d 00:00:00');
        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        $query = parent::$main_db->select('ssle.id')
            ->from('sty_schedule_lesson ssle')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0',
                'left')
            ->where(array(
                'ssle.id' => $scheduleId,
                'ssle.is_del' => 0,
                'susl.check_in_status' => 1,
                'ssle.start_time <' => $day
            ))
            ->limit(1)
            ->get();

        if (!empty($query->row_array())) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 手动删除阶段课程
     * @param $uid
     * @param $scheduleId
     * @param $nextScheduleId
     * @return bool
     */
    public function deleteSchedule($uid, $scheduleId, $reason, $nextScheduleId)
    {
        $this->load->model('public/schedule_model');
        $joinSchedule = $this->schedule_model->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            'schedule_id' => $scheduleId
        ], ['plan_id', 'plan_stage_id']);
        if (!empty($joinSchedule)) {
            $planId = $joinSchedule[0]['plan_id'];
            $planStageId = $joinSchedule[0]['plan_stage_id'];
        } else {
            return FALSE;
        }
        $result = $this->schedule_model->delConflictSchedule($uid, $scheduleId, 4, $reason);
        if ($result['code'] === 200) {
            //删除成功
            $validData = array(
                'uid' => $uid,
                'schedule_id' => $nextScheduleId
            );
            if (empty($nextScheduleId) || $this->validation->validate('my_schedule', $validData) === TRUE) {
                $this->load->model('public/plan_stage_model');
                return $this->plan_stage_model->updateUserPlanStageSelectSchedule($uid,
                    $planId, $planStageId, $nextScheduleId);
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 当天没有课程
     * @param $date
     * @param $uid
     * @param $scheduleId
     * @param $planId
     * @param $stageId
     * @return array
     */
    public function noLessonToday($date, $uid, $scheduleId, $planId, $stageId)
    {
        $data = array();
        if ($date === date('Y-m-d')) {
            $data['nextLesson'] = $this->nextLessonWarning($uid, $scheduleId, $date, $planId, $stageId);
        }
        $this->load->model('learning_system/learning_report_model');
        $data['go_study_link'] = $this->learning_report_model->genLearnLink($scheduleId, $planId, $stageId, 2);
        $data['go_exercise_link'] = $this->learning_report_model->genExerciseLink($scheduleId, $planId, $stageId, 'test', 0);
        $data['hasCompletedOne'] = $this->alreadyCompletedOne($uid, $scheduleId);
        $data['zd_download'] = get_options('zdtalk_download');
        return $data;
    }
}
