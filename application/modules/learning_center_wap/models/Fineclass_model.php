<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fineclass_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户进入wap站学习中心的相关信息
     * @return int
     */
    public function getFineclassInfo($uid = "")
    {
        $is_fineclass_student = $this->isFineclassStudent($uid);
        if ($is_fineclass_student === FALSE) {//非精品课学员跳转到老学习中心
            $res['jump_url'] = MASTER_DOMAIN . 'main.php/Wptouch/Myclass';
        } else {//有且只有精品课
            $res['jump_url'] = '';
            $this->load->model('public/Schedule_model');
            $fineclass_schedule_info = $this->Schedule_model->getUserScheduleIds($uid);
            if (empty($fineclass_schedule_info)) { //没有加入过精品课，进入选课页面
                $res['have_class'] = FALSE;
            } else { //有精品课，进入课程表页面
                $res['have_class'] = TRUE;
            }
        }
        return $res;
    }

    /**
     * 获取用户选课列表信息
     * @return int
     */
    public function getScheduleListInfo($uid = "")
    {
        //获取已加入的阶段课程信息
        $user_plan_stage = $this->getFineclassPlanStage($uid);
        $fineclass_schedule_info = $this->planAndStageScheduleDetail($uid, $user_plan_stage[0]['plan_id'], $user_plan_stage[0]['plan_stage_id'], 1);
        //时间处理
        $res['have_schedule'] = FALSE;
        foreach ($fineclass_schedule_info as $k => $v) {
            $time = explode(' ', $v['class_time']);
            $fineclass_schedule_info[$k]['class_time_d'] = $time[0];
            $fineclass_schedule_info[$k]['class_time_t'] = $time[1];
            $fineclass_schedule_info[$k]['plan_id'] = $user_plan_stage[0]['plan_id'];
            $fineclass_schedule_info[$k]['plan_stage_id'] = $user_plan_stage[0]['plan_stage_id'];
            if ($v['begin_study'] == TRUE) {
                $res['have_schedule'] = TRUE;
            }
        }
        $res['schedule_info'] = $fineclass_schedule_info;
        return $res;
    }

    /**
     * 获取用户课程表信息
     * @return int
     */
    public function getScheduleLessonInfo($uid = "")
    {
        $res = array('code' => 0, 'month_info' => array());
        $month_info_arr = array();
        $year = $this->input->post('year', TRUE);
        $year = $year ? $year : date('Y');
        $month = $this->input->post('month', TRUE);
        $month = $month ? $month : date('m');
        $day = $this->input->post('date', TRUE);
        $day = $day ? $day : date('d');

        $month = intval($month) < 10 ? '0' . intval($month) : $month;
        $startDay = $year . '-' . $month . '-01 00:00:00';
        $goods_expire = $this->getUserGoodsVaildTime($uid);
        $expire_end_day = null;
        if (!empty($goods_expire['time'])) {
            $expire = $goods_expire['time'][0] . '-' . $goods_expire['time'][1] . '-' . $goods_expire['time'][2];
            $expire_end_day = date('Y-m-d 23:59:59', strtotime($expire));
        }
        $endDay = date('Y-m-t 23:59:59', strtotime($year . '-' . $month . '+1month'));
        if (empty($expire_end_day) || ($expire_end_day <= $endDay)) {
            $endDay = $expire_end_day;
        }
        //取本月到下月末课件信息
        $currentTime = strtotime(date('Y-m') . '-01 00:00:00');
        if (strtotime($startDay) >= $currentTime) {
            //直播课表(我的课表)
            $this->load->model('public/schedule_model');
            $liveCourses = $this->schedule_model->getLiveCourseNew($uid);
            $res['finish_all_courses'] = FALSE;
            if (!empty($liveCourses)) {
                $res['finish_all_courses'] = TRUE;
                $next_class_info = array();
                foreach ($liveCourses as $key => $lv) {
                    if ($lv['end_time'] <= $expire_end_day) {
                        //当月有课日
                        if ($lv['schedule_lesson_id'] > 0) {
                            $timeday = date('d', strtotime($lv['start_time']));
                            $month_info_arr[intval($timeday)] = 1;
                        }
                        //下次课信息
                        if ($lv['start_time'] > date('Y-m-d H:i:s', time())) {
                            $start_time_n = $this->dateInfoReformat($lv['start_time']);
                            $next_class_info[$key]['month'] = intval($start_time_n['date_array1'][1]);
                            $next_class_info[$key]['day'] = intval($start_time_n['date_array1'][2]);
                            $next_class_info[$key]['hour'] = $start_time_n['date_array3'][0];
                            $next_class_info[$key]['date'] = $start_time_n['date_array3'][1];
                            $next_class_info[$key]['week'] = $start_time_n['date_array4'];
                            $next_class_info[$key]['title'] = $lv['lessonName'];
                        }

                        //取某日的课件信息
                        $start_time_today = $year . '-' . $month . '-' . $day . ' 00:00:00';
                        $end_time_today = $year . '-' . $month . '-' . $day . ' 23:59:59';
                        if (($lv['start_time'] > $start_time_today) && ($lv['end_time'] < $end_time_today)) {
                            $lv['teacher_name'] = $this->schedule_model->getTeacherInfoNew($lv['teacher_id']);
                            $start_time_new = $this->dateInfoReformat($lv['start_time']);
                            $lv['start_time_d'] = $start_time_new['date_array1'];
                            $lv['start_time_t'] = $start_time_new['date_array2'];

                            $end_time_new = $this->dateInfoReformat($lv['end_time']);
                            $lv['end_time_d'] = $end_time_new['date_array1'];
                            $lv['end_time_t'] = $end_time_new['date_array2'];

                            $lv['year'] = $year;
                            $lv['month'] = $month;
                            $res['schedule_info_today'][] = $lv;
                        }
                    }
                }
                $next_class_info = array_values($next_class_info);
                $res['next_class_info'] = $next_class_info ? $next_class_info[0] : null;
            }
        }
        if (!empty($month_info_arr)) {
            $res['code'] = 1;
            $res['month_info'] = array_keys($month_info_arr);
        }
        return $res;
    }


    /**
     * 判断是否是精品课学员
     * @return int
     */
    public function isFineclassStudent($uid)
    {
        $result = TRUE;
        $query = parent::$main_db
            ->select('b.is_active')
            ->from('sty_user_goods as a')
            ->join('sty_goods as b', 'a.goods_id = b.id', 'left')
            ->where(
                array(
                    'a.uid' => $uid,
                    'a.is_del' => 0,
                )
            )
            ->get();
        $my_goods = $query->result_array();
        if (!empty($my_goods)) {
            foreach ($my_goods as $item) {
                if (intval($item['is_active']) === 1) {
                    $result = FALSE;
                    break;
                }
            }
        } else {
            $result = FALSE;
        }
        return $result;
    }

    /**
     * 已购精品课商品的计划阶段
     * @return int
     */
    public function getFineclassPlanStage($uid = "")
    {
        $query = parent::$main_db
            ->select('plan_id,plan_stage_id')
            ->from('sty_user_plan_stage')
            ->where(
                array(
                    'uid' => $uid,
                    'is_del' => 0,
                )
            )
            ->get();
        $result = $query->result_array();
        return $result;
    }

    /**
     * 得到某日所在一周的日期
     * @return int
     */
    public function getDayList($class_day = array())
    {
        $week_name = array('日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日');
        $w = $this->input->post('day', TRUE);
        $d = $this->input->post('date', TRUE);
        $w = isset($w) ? $w : date('w');
        $d = isset($d) ? $d : date('d');
        $firstday = $d - $w;
        for ($i = 0; $i < 7; $i++) {
            $res[$i]['day'] = $firstday + $i;
            $res[$i]['w'] = $i;
            $res[$i]['week_name'] = $week_name[$i];
            $res[$i]['select_flag'] = ($d == $firstday + $i) ? 1 : 0;
            $res[$i]['class_flag'] = (in_array($firstday + $i, $class_day)) ? 1 : 0;
        }
        return $res;
    }

    /**
     * 已购精品课最长有效期
     * @return int
     */
    public function getUserGoodsVaildTime($uid = "")
    {
        $query = parent::$main_db
            ->select('expire')
            ->from('sty_user_goods')
            ->where(
                array(
                    'uid' => $uid,
                    'is_activate' => 1,
                    'is_del' => 0,
                )
            )
            ->order_by('expire desc')
            ->get();
        $expire = $query->result_array();
        $expire_time['expire_flag'] = 0;
        if (!empty($expire)) {
            if (strtotime($expire[0]['expire']) < time()) {
                $expire_time['expire_flag'] = 1;
            }
            $time = explode(' ', $expire[0]['expire']);
            $expire_time['time'] = explode('-', $time[0]);
        }
        return $expire_time;
    }

    /**
     * 阶段课程下已完成/总课件数信息
     * @return int
     */
    public function getScheduleCountNumInfo($schedule_id = "")
    {
        $this->load->model('public/lesson_model');
        $total_num = $this->lesson_model->getScheduleLessonCount($schedule_id);
        $finish_num = $this->lesson_model->getScheduleLessonCount($schedule_id, TRUE);
        $res['total_num'] = $total_num;
        $res['finish_num'] = $finish_num;
        $res['finish_percent'] = $finish_num / $total_num * 100;
        $res['finish_flag'] = FALSE;
        $res['not_begin_flag'] = FALSE;
        $res['stick_flag'] = TRUE;
        if ($total_num == $finish_num) {
            $res['finish_flag'] = TRUE;
        }
        if (!$finish_num) {
            $res['not_begin_flag'] = TRUE;
        }
        if ($res['finish_flag'] || $res['not_begin_flag']) {
            $res['stick_flag'] = FALSE;
        }
        return $res;
    }

    /**
     * 阶段课程 教师列表详细信息
     * @param $uid
     * @param $plan_id
     * @param $plan_stage_id
     * @param $type
     */
    public function planAndStageScheduleDetail($uid, $plan_id, $plan_stage_id, $type)
    {
        $this->load->model('public/schedule_model');
        //已加入的阶段课程
        $join_schedule_ids = $this->schedule_model->getUserScheduleIds($uid);
        //阶段课程列表
        $schedule = $this->schedule_model->getPlanStageScheduleList($plan_id, $plan_stage_id, 1, TRUE);
        if (!empty($schedule)) {
            foreach ($schedule as $k => $v) {
                //主讲老师
                $schedule[$k]['teachers'] = $this->schedule_model->getScheduleTeachers($v['id']);
                //使用教材
                $schedule[$k]['progress'] = '已完结 [随到随学]';
                $schedule[$k]['prompt_button'] = '加入课程';
                $schedule[$k]['prompt_color'] = 'bg_1e93fa';
                if ($type == 1) {
                    //上课日期
                    $schedule[$k]['class_date'] = '';
                    if (intval($v['start_time']) > 0 && intval($v['end_time']) > 0) {
                        $schedule[$k]['class_date'] = date('Y/m/d', strtotime($v['start_time'])) . '-' . date('Y/m/d', strtotime($v['end_time']));
                    }

                    //上课时间
                    $schedule[$k]['class_time'] = '';
                    $class_week_cycle = $this->schedule_model->getScheduleWeekCycle($v['id']);
                    if (!empty($class_week_cycle)) {
                        $schedule[$k]['class_time'] = implode('、', $class_week_cycle);
                    }
                    if (intval($v['class_start_time']) > 0 && intval($v['class_end_time']) > 0) {
                        $schedule[$k]['class_time'] .= ' ' . $v['class_start_time'] . '-' . $v['class_end_time'];
                    }

                    //上课进度
                    $schedule[$k]['rate_of_progress'] = $this->getScheduleCountNumInfo($v['id']);

                    //是否招满
                    $schedule_count = $this->schedule_model->joinScheduleCount($v['id']);
                    $remain_num = intval($v['max_curriculum_num']) - intval($schedule_count);

                    if (!in_array($v['id'], $join_schedule_ids) && $remain_num <= 0) {
                        $schedule[$k]['prompt_button'] = '招生已满';
                        $schedule[$k]['prompt_color'] = 'bg_d2d2d2';
                    }
                }
                $schedule[$k]['begin_study'] = FALSE;
                //提示信息
                if (in_array($v['id'], $join_schedule_ids)) {
                    $schedule[$k]['prompt_button'] = '开始学习';
                    $schedule[$k]['prompt_color'] = 'bg_1e93fa';
                    $schedule[$k]['begin_study'] = TRUE;
                }
            }
        }
        return $schedule;
    }

    /**
     * 加入阶段课程
     * @param $uid
     */
    public function joinFineClass($uid = '')
    {
        $plan_id = $this->input->post('plan_id', TRUE);
        $plan_stage_id = $this->input->post('plan_stage_id', TRUE);
        $schedule_id = $this->input->post('schedule_id', TRUE);
        //激活商品
        $this->load->model('public/Goods_model');
        $goodsIds = $this->Goods_model->unActivationGoods($uid);
        if (!empty($goodsIds)) {
            foreach ($goodsIds as $k => $v) {
                $res_active = $this->Goods_model->activateGoods($uid, $v['goods_id']);
                if ($res_active['code'] != 200) {
                    return $res_active;
                }
            }
        }
        $this->load->model('public/schedule_model');
        //加入班级
        $result = $this->schedule_model->joinScheduleBefore($uid, $plan_id, $plan_stage_id, $schedule_id, -1);

        if (!empty($result['code'])) {
            if (empty($result['msg'])) {
                if ($result['code'] === 200) {
                    $result['msg'] = '加课成功';
                } else {
                    $result['msg'] = get_options('result_code', $result['code']);
                }
            }
        } else {
            $result['msg'] = '加课失败';
        }
        return $result;
    }


    /**
     * 时间信息重新格式化
     * @param $date
     * @return $result
     */
    public function dateInfoReformat($date)
    {
        $week_array = array('日', '一', '二', '三', '四', '五', '六');
        $date_new = date('H:i', strtotime($date));
        $date_array = explode(' ', $date);
        $result['date_array1'] = explode('-', $date_array[0]);
        $result['date_array2'] = $date_new;
        $result['date_array3'] = explode(':', $date_new);
        $result['date_array4'] = $week_array[date('w', strtotime($date))];
        return $result;
    }

    /**
     * 获取教室信息
     */
    public function getClassroomInfo()
    {
        $schedule_lesson_id = $this->input->post('schedule_lesson_id', TRUE);
        $this->load->model('learning_system/learning_task_model');
        $class_room_info = $this->learning_task_model->classroomInfo($schedule_lesson_id);
        return $class_room_info;
    }
}
