<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LearnCourse_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/schedule_model');
    }

    /**
     * 获取用户加入的某个阶段课程基本信息
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function baseInfo($uid, $scheduleId)
    {
        $result = ['code' => 500, 'data' => [], 'msg' => ''];
        if (($isValid = $this->validation->validate('my_schedule', ['uid' => $uid, 'schedule_id' => $scheduleId])) !== TRUE) {
            $result['msg'] = $isValid['msg'];
            return $result;
        }
        $joinSchedule = $this->schedule_model->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            'schedule_id' => $scheduleId,
            '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')",
        ], ['plan_id', 'plan_stage_id'], [], [1]);

        $this->load->model('public/lesson_model');
        $schedule = $this->schedule_model->getScheduleBaseInfo($scheduleId);
        if (!empty($schedule)) {
            $schedule['class_week_time'] = $this->schedule_model->getScheduleWeekCycle($scheduleId);
            $schedule['start_time'] = substr($schedule['start_time'], 5);
            $schedule['end_time'] = substr($schedule['end_time'], 5);
            $str = '';
            foreach ($schedule['class_week_time'] as $weekday) {
                $str .= '周' . $weekday . ' ';
            }
            $schedule['class_week_time'] = rtrim($str);
            $schedule['relate_books'] = $this->schedule_model->getTextbooks($scheduleId);
            $teachers = $this->schedule_model->getScheduleTeachers($scheduleId);
            $str = '';
            foreach ($teachers as $teacher) {
                $str .= $teacher['name'] . '  ';
            }
            $schedule['teachers'] = rtrim($str);
            $schedule['check_in_count'] = $this->lesson_model->userReportCount($uid, $scheduleId);
            $schedule['first_view'] = $this->schedule_model->getUserScheduleInfo($uid, $scheduleId, 'first_view')['first_view'];
            $schedule['check_in_rate'] = intval($schedule['check_in_count'] * 100 / $schedule['class_num']);
            $schedule['plan_id'] = $joinSchedule['plan_id'];
            $schedule['plan_stage_id'] = $joinSchedule['plan_stage_id'];
            $schedule['zdtalk_download'] = get_options('zdtalk_download');
        }
        $result['code'] = 200;
        $result['data'] = $schedule;
        return $result;
    }

    /**
     * 我的课程用户阶段课程详细列表
     * @param $uid
     * @param $scheduleId
     * @param $cate
     * @return array
     */
    public function detailList($uid, $scheduleId, $cate)
    {
        $result = ['code' => 500, 'data' => [], 'msg' => ''];
        if (($isValid = $this->validation->validate('learn_details', ['uid' => $uid, 'schedule_id' => $scheduleId])) !== TRUE) {
            $result['code'] = $isValid['code'];
            $result['msg'] = $isValid['msg'];
            return $result;
        }
        switch ($cate) {
            case 'learn':
                $result['data'] = $this->getLearnList($uid, $scheduleId);
                break;
            case 'datum':
                $result['data'] = $this->getDatumList($scheduleId);
                break;
            case 'exercise':
                $result['data'] = $this->getExerciseList($uid, $scheduleId);
                break;
            case 'report':
                $result['data'] = $this->getReportDetail($uid, $scheduleId);
                break;
            case 'guide':
                $result['data'] = $this->getGuideDetail($scheduleId);
                break;
            default:
                break;
        }
        $result['code'] = 200;
        return $result;
    }

    /**
     * 新手指导
     * @param $scheduleId
     * @return mixed
     */
    public function getGuideDetail($scheduleId)
    {
        $where = ['name, qq_group, target, start_time, end_time, class_start_time, class_end_time'];
        $data['Schedule_info'] = $this->schedule_model->getScheduleInfo($scheduleId, $where);
        $data['Schedule_info']['have_group'] = ($data['Schedule_info']['qq_group'] > 0) ? TRUE : FALSE;
        $data['Schedule_info']['time'] = date('H:i',strtotime(date('Y-m-d',time()).' '.$data['Schedule_info']['class_start_time'])).'-'.date('H:i',strtotime(date('Y-m-d',time()).' '.$data['Schedule_info']['class_end_time']));
        $data['Schedule_info']['start_time'] = date('Y年m月d日',strtotime($data['Schedule_info']['start_time']));
        $data['Schedule_info']['end_time'] = date('Y年m月d日',strtotime($data['Schedule_info']['end_time']));
        $week_cycle_string = $this->schedule_model->getScheduleWeekCycle($scheduleId);
        $data['schedule_week_cycle'] = implode('、', $week_cycle_string);
        $text_books = $this->schedule_model->getTextbooks($scheduleId);
        $books_array = [];
        foreach ($text_books as $k=>$v){
            if($v['name'] != '早道内部讲义'){
                if(empty($v['download_link'])){
                    $v['download_link'] = FALSE;
                }
                if(empty($v['buy_link'])){
                    $v['buy_link'] = FALSE;
                }
                $books_array[] = $v;
            }
        }
        $data['have_text_books'] = FALSE;
        if(!empty($books_array)){
            $data['have_text_books'] = TRUE;
        }
        $data['schedule_text_books'] = $books_array;
        return $data;
    }


    /**
     * 总出勤率 直播出勤率 一课一练完成率 单元测试完成率
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function getReportDetail($uid, $scheduleId)
    {
        $this->load->model('learning_system/learning_report_model');
        $data['total'] = $this->learning_report_model->checkInReport($uid, 1, $scheduleId, 0, 0);
        $data['live'] = $this->learning_report_model->checkInReport($uid, 2, $scheduleId, 0, 0);
        $data['practice'] = $this->learning_report_model->exerciseReport($uid, 'test', $scheduleId, 0, 0);
        $data['unit'] = $this->learning_report_model->exerciseReport($uid, 'unit', $scheduleId, 0, 0);
        return $data;
    }

    /**
     * 获取阶段课程的做题列表
     * @param $uid
     * @param $scheduleId
     * @return array
     */
    public function getExerciseList($uid, $scheduleId)
    {
        $isRecord = $this->schedule_model->isRecordSchedule($scheduleId);
        parent::$main_db->select("test_id, unit_id, id, start_time")
            ->from('sty_schedule_lesson')
            ->where([
                'schedule_id' => $scheduleId,
                'is_del' => 0
            ])
            ->where('(test_id > 0 OR unit_id > 0)');

        if ($isRecord) {//录播阶段课程没有开始时间
            parent::$main_db->order_by('order', 'ASC');
        } else {
            parent::$main_db->where('start_time <', date('Y-m-d 23:59:59'));
            parent::$main_db->where('start_time >', '1000-01-01 00:00:00')->order_by('start_time', 'ASC');
        }
        $uTests = $uUnits = $final = $return = [];
        $current = time();
        $results = parent::$main_db->get()->result_array();
        $this->load->model('learning_system/exercise_model');
        //过滤重复test id, unit id
        foreach ($results as $result) {
            if (!empty($result['test_id'])) {
                $test = $this->exercise_model->getUserPracticeInfo($result['test_id'], $uid, FALSE);
                $test['id'] = $result['test_id'];
                $uTests[$result['test_id']] = $result['id'];
                $final[$result['id']]['test'] = $test;
            }
            if (!empty($result['unit_id'])) {
                $unit = $this->exercise_model->getUserUnitTestInfo($result['unit_id'], $uid, FALSE);
                if (!empty($unit)) {
                    $isOnTime = $this->exercise_model->isOnTimeUnitTest(null, $unit['exam_type'], FALSE);
                    if (!$isRecord && $isOnTime && $current < strtotime($result['start_time']) - 1800) {
                        //按时完成单元测试上课前半小时提示
                        $unit['unit_notstart'] = TRUE;
                        $unit['unit_start_time'] = strtotime($result['start_time']) - 1800;
                    }
                    if (!$isRecord && $isOnTime && $current > strtotime($result['start_time'] + 1800) &&
                        $current < strtotime($result['start_time']) + 3600) {
                        //按时完成类型单元测试超过上课时间30min进入考场提示
                        $unit['over_30min'] = TRUE;
                    }
                }
                $unit['id'] = $result['unit_id'];
                $uUnits[$result['unit_id']] = $result['id'];
                $final[$result['id']]['unit'] = $unit;
            }
        }
        $testIds = array_keys($uTests);
        $unitIds = array_keys($uUnits);
        $tsql = $usql = '(1=2)';
        if (!empty($testIds)) {
            $inStr = implode(',', $testIds);
            $tsql = "(type = 1 AND sub_id in ({$inStr}))";
        }
        if (!empty($unitIds)) {
            $inStr = implode(',', $unitIds);
            $usql = "(type = 2 AND sub_id in ({$inStr}))";
        }

        $exercises = parent::$main_db->select('score, type, sub_id, schedule_lesson_id, schedule_id')
            ->from('sty_user_schedule_lesson_score')
            ->where([
                'uid' => $uid,
                'is_del' => 0
            ])
            ->where("({$tsql} OR {$usql})")->get()->result_array();
        foreach ($exercises as $exercise) {
            if ($exercise['type'] === '1') {
                $key = 'test';
                $temp = $uTests;
            } else {
                $key = 'unit';
                $temp = $uUnits;
            }
            if ($exercise['schedule_id'] === $scheduleId) {//是否是在同一个阶段课程下完成的
                $final[$exercise['schedule_lesson_id']][$key]['current_finish'] = TRUE;
                $final[$exercise['schedule_lesson_id']][$key]['finish'] = TRUE;
                $final[$exercise['schedule_lesson_id']][$key]['score'] = $exercise['score'];
            } else {
                $final[$temp[$exercise['sub_id']]][$key]['finish'] = TRUE;
            }
        }
        $index = 1;
        foreach ($final as $lessonId => $item) {
            if (isset($item['test'])) {
                $item['test']['index'] = sprintf('%02d', $index);
                $item['test']['type'] = 'test';
                $item['test']['lesson_id'] = $lessonId;
                $return[] = $item['test'];
                $index++;
            }
            if (isset($item['unit'])) {
                $item['unit']['index'] = sprintf('%02d', $index);
                $item['unit']['type'] = 'unit';
                $item['unit']['lesson_id'] = $lessonId;
                $return[] = $item['unit'];
                $index++;
            }
        }
        return $return;
    }

    /**
     * 获取阶段课程的资料列表
     * @param $scheduleId
     * @return mixed
     */
    public function getDatumList($scheduleId)
    {
        $results = parent::$minor_db->select('zdl.names, zdl.download_type, zdl.tfp_url')
            ->from('zd_downloads zdl')
            ->join('zd_netschool.sty_schedule_datum ssd', 'ssd.datum_id = zdl.id', 'left')
            ->where([
                'ssd.schedule_id' => $scheduleId,
                'ssd.is_del' => 0
            ])
            ->order_by('zdl.update_time', 'DESC')->get()->result_array();
        foreach ($results as $i => $result) {
            $results[$i]['type_title'] = get_options('datum_type', $result['download_type']);
        }
        return $results;
    }

    /**
     * 获取我的课程课列表
     * @param $uid
     * @param $scheduleId
     * @return mixed
     */
    public function getLearnList($uid, $scheduleId)
    {
        $scheduleInfo = $this->schedule_model->getScheduleInfo($scheduleId, 'class_mode, enable_zdtalk');
        $isRecord = $scheduleInfo['class_mode'] === '2';
        $schedulePlan = $this->schedule_model->viewUserPlanStageJoinSchedule([
            'uid' => $uid,
            'schedule_id' => $scheduleId,
            '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')",
        ], ['plan_id', 'plan_stage_id'], [], [1]);
        parent::$main_db->select('ssle.id, ssle.name, ssle.alias,
                ssle.start_time, ssle.end_time, ssle.test_id, ssle.unit_id, 
                zti.name as teacher_name, zti.desPic2, zti.smallPic, susl.check_in_status, susl.check_in_time, 
                susl.test_time, susl.unit_time, ssle.is_record, ssle.record_link, 
                ssle.record_download, susl.check_in_type, susl.check_in_score, susl.test_status, 
                susl.test_score, susl.unit_status, susl.unit_score, susl.unit_type')
            ->from('sty_schedule_lesson ssle')
            ->join('zd_class.zd_teacher_info zti', 'zti.uid = ssle.teacher_id', 'left')
            ->join('sty_user_schedule_lesson susl', "susl.uid = {$uid} AND susl.schedule_lesson_id = ssle.id AND susl.is_del = 0",
                'left')
            ->where(array(
                'ssle.schedule_id' => $scheduleId,
                'ssle.is_del' => 0
            ));
        if (!$isRecord) {//非录播阶段课程
            parent::$main_db->where(['ssle.start_time > ' => '1000-01-01 00:00:00'])->order_by('ssle.start_time', 'ASC');
        } else {
            parent::$main_db->order_by('ssle.order', 'ASC');
        }
        $result = parent::$main_db->get()->result_array();
        foreach ($result as $index => $item) {
            $result[$index]['title'] = empty($item['alias']) ? $item['name'] : $item['alias'];
            $result[$index]['index'] = sprintf('%02d', $index + 1);
            $result[$index]['check_in_score'] = intval($item['check_in_score']);
            $result[$index]['check_in_status'] = intval($item['check_in_status']);
            $result[$index]['record_download'] = intval($item['record_download']);
            $result[$index]['status'] = $this->getLessonStatus($isRecord, $item);
            if (!$isRecord) {
                $result[$index]['time_str'] = date('m月d日 H:i-', strtotime($item['start_time'])) . date('H:i', strtotime($item['end_time']));
                $result[$index]['start_st'] = strtotime($item['start_time']) * 1000;
            }
            if ($scheduleInfo['enable_zdtalk'] == '1') {
                $result[$index]['zdtalk_link'] = $this->schedule_model->getZDTalkLink($uid, $item['id']);
            } else {
                $result[$index]['zdtalk_link'] = '';
            }
        }
        return $result;
    }

    /**
     * 获取课程上课状态
     * @param $isRecord
     * @param $lesson
     * @return string
     */
    public function getLessonStatus($isRecord, $lesson)
    {
        if ($isRecord) {
            return 'finished';
        } else {
            $endTime = strtotime($lesson['end_time']);
            if (time() < $endTime + 5400) {//还没下课
                $today = date('Y-m-d');
                $startDay = date('Y-m-d', strtotime($lesson['start_time']));
                if ($today === $startDay) {//今天上课
                    return 'today';
                } else {
                    return 'not start';
                }
            } else {
                return 'finished';
            }
        }

    }

    /**
     * 获取阶段课程课件的下载链接
     * @param $uid
     * @param $scheduleId
     * @param $lessonId
     * @return array|string
     */
    public function getDownloadLink($uid, $scheduleId, $lessonId)
    {
        $result = ['code' => 500, 'data' => [], 'msg' => ''];
        if (($isValid = $this->validation->validate('my_schedule', ['uid' => $uid, 'schedule_id' => $scheduleId])) !== TRUE) {
            $result['msg'] = $isValid['msg'];
            return $result;
        }
        $schedule_lesson_id = intval($lessonId);
        if (!empty($schedule_lesson_id)) {
            $this->load->model('learning_system/learning_task_model');
            $this->load->model('learning_system/review_model');
            $scheduleInfo = $this->learning_task_model->getScheduleLessonInfo($schedule_lesson_id);
            if (!empty($scheduleInfo)) {
                if (!empty($scheduleInfo['record_link']) && $scheduleInfo['record_download'] === '1') {
                    $result['code'] = 200;
                    $result['data']['link'] = $this->review_model->generateDownUrl($scheduleInfo['record_link']);
                }
            }
        }
        return $result;
    }

    /**
     * 获取talk教室地址
     * @param $uid
     * @param $lesson_id
     * @return null
     */
    public function getLessonTalkRoom($uid, $lesson_id)
    {
        $data = ['talkUrl' => '', 'download' => get_options('zdtalk_download')];
        $this->load->model('public/lesson_model');
        $lesson = $this->lesson_model->getScheduleLessonDetail([
            'ssl.id' => $lesson_id
        ], [
            'ss.enable_zdtalk',
            'ss.curricular_system'
        ], [], [1]);
        if (!empty($lesson) && intval($lesson['enable_zdtalk']) === 1) {
            $curricular_system = intval($lesson['curricular_system']);
            if ($curricular_system === 1) {//主修
                $data['talkUrl'] = $this->schedule_model->getZDTalkLink($uid, $lesson['id']);
            } else {
                //查询购买对应课程体系的商品状态
                $this->load->model('public/goods_model');
                $goods_status = $this->goods_model->getUserGoodsStatus($uid, $curricular_system);
                if ($goods_status['hasGoods'] === TRUE && $goods_status['hasExpire'] === TRUE) {
                    //是否预约
                    $reservation_lesson = parent::$main_db->from('sty_user_schedule_lesson')
                        ->where([
                            'uid' => $uid,
                            'schedule_lesson_id' => $lesson['id'],
                            'reservation_status' => 1,
                            'is_del' => 0
                        ])->count_all_results();
                    if ($reservation_lesson > 0) {
                        $data['talkUrl'] = $this->schedule_model->getZDTalkLink($uid, $lesson['id']);
                    }
                }
            }
        }
        return $data;
    }
}
