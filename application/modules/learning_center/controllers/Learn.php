<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('curriculum_model');
        $this->load->model('level_test/grade_model');
        $this->load->library('thrift');
        $this->validate_leave();
    }

    /**
     * 主体结构
     */
    public function main()
    {
        $result = $this->layout->load('learn', 'learning_center');
        $this->response->html($result);
    }

    /**
     * 直播课表
     */
    public function live_schedule()
    {
        $xDate = $this->input->get('date', TRUE);
        $data = $this->curriculum_model->liveSchedule($this->_uid, $xDate);
        if ($data['hasGoods'] === TRUE) {
            if ($data['hasExpire'] === TRUE) {
                if ($data['selectCourse'] === TRUE) {
                    $result = $this->layout->load('live_schedule', 'learning_center', $data);
                } else {
                    $result = $this->layout->loadView('not_select_course', 'learning_center', $data);
                }
            } else {
                if ($data['hasUnActivate'] === TRUE) {
                    $result = $this->layout->loadView('not_select_course', 'learning_center', $data);
                } else {
                    $result = $this->layout->loadView('not_expire', 'learning_center', $data);
                }
            }
        } else {
            $result = $this->layout->loadView('not_goods_vip', 'learning_center');
        }
        $this->response->html($result);
    }

    /**
     * 某一天的直播课表
     */
    public function live_schedule_day()
    {
        $result = '';
        $xDate = $this->input->get('date', TRUE);
        $data = $this->curriculum_model->liveScheduleDay($this->_uid, $xDate);
        if (!empty($data)) {
            $result = $this->layout->loadView('day_schedule', 'learning_center', $data);
        }
        $this->response->html($result);
    }

    /**
     * 日历
     */
    public function monthly_calendar()
    {
        $year_month = $this->input->get('year_month');
        $selected = $this->input->get('selected');
        $uid = $this->_uid;
        $data = $this->get_user_calendar_data($uid, $year_month);
        $selected = explode('|', $selected);
        if ($year_month === $selected[0]) {
            //是当前选择的年月
            $data['selected'] = intval($selected[1]);
        }
        $result = $this->layout->loadView('calendar_detail', 'learning_system', $data);
        $this->response->html($result);
    }

    protected function get_user_calendar_data($uid, $year_month)
    {
        //必须显示六行
        $firstDay = date('w', strtotime($year_month . '-01'));
        $totalDays = date('t', strtotime($year_month));
        $firstWeekDays = 7 - $firstDay;
        $data['calendar'] = $this->curriculum_model->liveUserMonthlyCalendar($uid, $year_month);
        $data['totalDays'] = $totalDays;
        $data['firstWeekDays'] = $firstWeekDays;
        $data['stuffingDays'] = 42 - $totalDays - $firstDay;
        return $data;
    }

    /**
     * 选择主修
     */
    public function select_course_major()
    {
        $data = $this->curriculum_model->selectCourseMajor($this->_uid);
        if ($data['hasGoods'] === TRUE) {
            if ($data['goSelectPlan'] === TRUE) {
                $result = $this->layout->load('select_plan', 'learning_center', $data);
            } else {
                $result = $this->layout->load('select_course_major', 'learning_center', $data);
            }
        } else {
            $result = $this->layout->loadView('not_goods_vip', 'learning_center');
        }
        $this->response->html($result);
    }

    /**
     * 切换计划
     */
    public function select_course_switch_plan()
    {
        $result = '';
        $plan = $this->input->get('plan', TRUE);
        $data = $this->curriculum_model->switchPlan($this->_uid, $plan);
        if (!empty($data)) {
            $result = $this->layout->loadView('switch_plan_stage', 'learning_center', $data);
        }
        $this->response->html($result);
    }

    /**
     * 选择计划
     */
    public function select_plan()
    {
        $this->load->model('public/plan_model');
        $plan = $this->input->post('plan', TRUE);
        if ($this->plan_model->updateCurrentPlan($this->_uid, $plan) !== FALSE) {
            $this->response->formatJson(200);
        }
    }

    /**
     * 解锁阶段
     */
    public function unlock_plan_stage()
    {
        $this->load->model('public/plan_stage_model');
        $show_popup = intval($this->input->post('show_popup', TRUE));
        $plan = $this->input->post('plan', TRUE);
        $stage = $this->input->post('stage', TRUE);
        $result = $this->plan_stage_model->unlockPlanStage($this->_uid, $plan, $stage, $show_popup);
        $this->response->json($result);
    }

    /**
     * 已解锁的计划阶段
     */
    public function click_unlock_plan_stage()
    {
        $this->load->model('public/plan_stage_model');
        $plan = $this->input->post('plan', TRUE);
        $stage = $this->input->post('stage', TRUE);
        $result = $this->plan_stage_model->clickUnlockPlanStage($this->_uid, $plan, $stage);
        $this->response->json($result);
    }

    /**
     * 选择阶段课程
     */
    public function select_schedule()
    {
        $params['plan_id'] = $this->input->get('plan', TRUE);
        $params['plan_stage_id'] = $this->input->get('stage', TRUE);
        $params['class_mode'] = $this->input->get('class_mode', TRUE);
        $params['time_type'] = intval($this->input->get('time_type', TRUE));
        $params['course_status'] = intval($this->input->get('course_status', TRUE));
        $data = $this->curriculum_model->scheduleRoutes($this->_uid, 'schedule_main', $params);
        if ($data['hasGoods'] === TRUE) {
            if ($data['hasPlanStage'] === TRUE) {
                $result = $this->layout->load('select_schedule', 'learning_center', $data);
            } else {
                $result = $this->layout->loadView('not_plan_stage', 'learning_center');
            }
        } else {
            $result = $this->layout->loadView('not_goods_vip', 'learning_center');
        }
        $this->response->html($result);
    }

    /**
     * 切换阶段课程分类
     */
    public function schedule_label()
    {
        $params['plan_id'] = $this->input->get('plan', TRUE);
        $params['plan_stage_id'] = $this->input->get('stage', TRUE);
        $params['class_mode'] = $this->input->get('class_mode', TRUE);
        $params['time_type'] = intval($this->input->get('time_type', TRUE));
        $params['course_status'] = intval($this->input->get('course_status', TRUE));
        $data = $this->curriculum_model->scheduleRoutes($this->_uid, 'schedule_lable', $params);
        $result = $this->layout->load('schedule_label', 'learning_center', $data);
        $this->response->html($result);

    }

    /**
     * 阶段课程列表
     */
    public function schedule_list()
    {
        $params['plan_id'] = $this->input->get('plan', TRUE);
        $params['plan_stage_id'] = $this->input->get('stage', TRUE);
        $params['class_mode'] = $this->input->get('class_mode', TRUE);
        $params['time_type'] = intval($this->input->get('time_type', TRUE));
        $params['course_status'] = intval($this->input->get('course_status', TRUE));
        $data = $this->curriculum_model->scheduleRoutes($this->_uid, 'schedule_data', $params);
        $result = $this->layout->load('schedule_list', 'learning_center', $data);
        $this->response->html($result);

    }

    /**
     * 预约制选课内容
     */
    public function select_course_reservation()
    {
        $curricular = $this->input->get('curricular', TRUE);
        $params['curricular'] = $curricular;
        $params['basic'] = intval($this->input->get('basic', TRUE));
        $params['content_cat'] = intval($this->input->get('content_cat', TRUE));
        $params['page'] = intval($this->input->get('page', TRUE));
        $data = $this->curriculum_model->selectCourseReservationRoutes($this->_uid, 'select_reservation', $params);
        $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
        if ($data['hasGoods'] === TRUE) {
            if ($curricular == 'custom' && intval($data['total_count']) === 0) {
                $result = $this->layout->load('select_reservation_not_custom', 'learning_center', $data);
            } else {
                $result = $this->layout->load('select_reservation', 'learning_center', $data);
            }
        } else {
            if ($curricular == 'special' || $curricular == 'custom') {
                $result = $this->layout->loadView('not_goods_vip', 'learning_center', [], ['button_href' => get_options('svip_link')]);
            } else {
                $result = $this->layout->loadView('not_goods_vip', 'learning_center');
            }
        }
        $this->response->html($result);
    }

    /**
     * 选择预约制课程主体
     */
    public function select_reservation_main()
    {
        $params['curricular'] = $this->input->get('curricular', TRUE);
        $params['basic'] = intval($this->input->get('basic', TRUE));
        $params['content_cat'] = intval($this->input->get('content_cat', TRUE));
        $params['page'] = intval($this->input->get('page', TRUE));
        $data = $this->curriculum_model->selectCourseReservationRoutes($this->_uid, 'reservation_main', $params);
        $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
        $result = $this->layout->load('reservation_mains', 'learning_center', $data);
        $this->response->html($result);
    }

    /**
     * 我的课程（主修）
     */
    public function my_course_major()
    {
        $data = $this->curriculum_model->myCourseMajorRoutes($this->_uid, 'my_course_major');
        if ($data['hasGoods'] === TRUE) {
            if ($data['ableCourse'] === TRUE) {
                $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
            } else {
                $data['noCourse'] = $this->layout->loadView('not_able_major', 'learning_center');
            }
            $result = $this->layout->load('my_major', 'learning_center', $data);
        } else {
            $result = $this->layout->loadView('not_goods_vip', 'learning_center');
        }
        $this->response->html($result);
    }

    /**
     * 我的课程（主修课列表）
     */
    public function my_major_list()
    {
        $data = $this->curriculum_model->myCourseMajorRoutes($this->_uid, 'my_course_major');
        if ($data['ableCourse'] === TRUE) {
            $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
        } else {
            $data['noCourse'] = $this->layout->loadView('not_able_major', 'learning_center');
        }
        $result = $this->layout->loadView('my_major_list', 'learning_center', $data);
        $this->response->html($result);
    }

    /**
     * 我的课程（预约制）
     */
    public function my_reservation()
    {
        $curricular = $this->input->get('curricular', TRUE);
        $params['curricular'] = $curricular;
        $params['type'] = $this->input->get('type', TRUE);
        $data = $this->curriculum_model->myReservationRoutes($this->_uid, 'my_reservation', $params);
        if (!empty($data)) {
            if ($data['hasGoods'] === TRUE) {
                if ($data['ableCourse'] === TRUE) {
                    $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
                } else {
                    $data['noCourse'] = $this->layout->loadView('not_able_reservation', 'learning_center', [], ['button_href' => '/#selectCourse/' . $data['current_curricular']]);
                }
                $result = $this->layout->load('my_reservation', 'learning_center', $data);
            } else {
                if ($curricular == 'special' || $curricular == 'custom') {
                    $result = $this->layout->loadView('not_goods_vip', 'learning_center', [], ['button_href' => get_options('svip_link')]);
                } else {
                    $result = $this->layout->loadView('not_goods_vip', 'learning_center');
                }
            }
        }
        $this->response->html($result);
    }

    /**
     * 我的课程（预约制列表）
     */
    public function my_reservation_list()
    {
        $params['curricular'] = $this->input->get('curricular', TRUE);
        $params['type'] = $this->input->get('type', TRUE);
        $data = $this->curriculum_model->myReservationRoutes($this->_uid, 'my_reservation', $params);
        if ($data['ableCourse'] === TRUE) {
            $data['noCourse'] = $this->layout->loadView('not_course', 'learning_center');
        } else {
            $data['noCourse'] = $this->layout->loadView('not_able_reservation', 'learning_center', [], ['button_href' => '/#selectCourse/' . $data['current_curricular']]);
        }
        $result = $this->layout->loadView('my_reservation_lesson', 'learning_center', $data);
        $this->response->html($result);
    }

    /**
     * 口语测评
     */
    public function oral_assessment()
    {
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $data = $this->curriculum_model->oralAssessment($this->_uid, $lesson_id);
        if ($data['hasGoods'] === TRUE) {
            $result['html'] = $this->layout->load('oral_assessment', 'learning_center', $data);
            $result['is_apply'] = $data['apply'];
            $result['can_apply'] = $data['can_apply'];
            $this->response->formatJson(200, $result);
        } else {
            $result = $this->layout->loadView('not_goods_vip', 'learning_center');
            $this->response->html($result);
        }
    }

    /**
     * 口语测评 时间-老师
     */
    public function oral_assessment_date_teacher()
    {
        $date = $this->input->get('date', TRUE);
        if (!empty($date) && !empty(strtotime($date))) {
            $data = $this->curriculum_model->oralAssessmentDateTeacher($date);
            $result = $this->layout->loadView('oral_assessment_date_teacher', 'learning_center', $data);
            $this->response->html($result);
        }
    }

    /**
     * 提交口语测评
     * @return array
     */
    public function oral_assessment_submit()
    {
        $result = ['code' => 0];
        $msg = '';
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $request['reg_name'] = $this->input->post('reg_name', TRUE);
        $request['mobile'] = $this->input->post('mobile', TRUE);
        $request['teacher_uid'] = $this->input->post('teacher_uid', TRUE);
        $request['hope_time'] = $this->input->post('hope_time', TRUE);
        if (intval($lesson_id) > 0) {
            $res = $this->curriculum_model->oralAssessment($this->_uid, $lesson_id, FALSE);
            if (!empty($res) && $res['hasGoods'] === TRUE && $res['existLesson'] === TRUE) {
                if (intval($res['apply']) === 1) {
                    $result['code'] = 247;
                } elseif (intval($res['can_apply']) === 1) {
                    $request['lesson_id'] = $res['lesson_id'];
                    $request['schedule_id'] = $res['schedule_id'];
                    $request['lesson_num'] = $res['lesson_num'];
                    $result = $this->curriculum_model->oralAssessmentApply($this->_uid, $request);
                } else {
                    $result['code'] = 248;
                }
            }
        }
        if ($result['code'] > 0) {
            $msg = get_options('result_code', $result['code']);
        }
        $this->response->formatJson($result['code'], [], $msg);
    }


    /**
     * 预约课程前
     */
    public function reservation_before()
    {
        $result = [];
        $this->load->model('public/lesson_model');
        $curricular = $this->input->post('curricular', TRUE);
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $catgory = get_options('curricular_system', $curricular);
        if (intval($catgory) > 1 && intval($lesson_id)) {
            $result = $this->lesson_model->reserveBefore($this->_uid, $catgory, $lesson_id);
        }
        $this->response->json($result);
    }

    /**
     * 预约课程
     */
    public function reservation_lesson()
    {
        $result = [];
        $this->load->model('public/lesson_model');
        $curricular = $this->input->post('curricular', TRUE);
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $catgory = get_options('curricular_system', $curricular);
        if (intval($catgory) > 1 && intval($lesson_id)) {
            $result = $this->lesson_model->userReserveLesson($this->_uid, $catgory, $lesson_id);
        }
        $this->response->json($result);
    }

    /**
     * 取消预约
     */
    public function cancel_reservation_lesson()
    {
        $result = [];
        $this->load->model('public/lesson_model');
        $curricular = $this->input->post('curricular', TRUE);
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $catgory = get_options('curricular_system', $curricular);
        if (intval($catgory) > 1 && intval($lesson_id)) {
            $result = $this->lesson_model->userCancelReserveLesson($this->_uid, $catgory, $lesson_id);
        }
        $this->response->json($result);
    }

    /**
     * 系统取消预约
     */
    public function system_cancel_reservation()
    {
        $result = [];
        $this->load->model('public/lesson_model');
        $curricular = $this->input->post('curricular', TRUE);
        $lesson_id = $this->input->post('lesson_id', TRUE);
        $catgory = get_options('curricular_system', $curricular);
        if (intval($catgory) > 1 && intval($lesson_id)) {
            $result = $this->lesson_model->userCancelReserveLesson($this->_uid, $catgory, $lesson_id, TRUE);
        }
        $this->response->json($result);
    }

    /**
     * 激活流程限制
     */
    public function activation_process()
    {
        $this->load->model('public/goods_model');
        $plan = $this->input->post('plan', TRUE);
        $stage = $this->input->post('stage', TRUE);
        $result = $this->goods_model->activationProcess($this->_uid, $plan, $stage, TRUE);
        $this->response->json($result);
    }

    /**
     * 加入阶段课程
     */
    public function join_schedule()
    {
        $this->load->model('public/schedule_model');
        $plan = $this->input->post('plan', TRUE);
        $stage = $this->input->post('stage', TRUE);
        $schedule_id = $this->input->post('schedule_id', TRUE);
        $before = $this->input->post('before', TRUE);
        $result = $this->schedule_model->joinScheduleBefore($this->_uid, $plan, $stage, $schedule_id, intval($before));
        if ($result['code'] === 211 && isset($result['data'])) {
            $result['html'] = $this->load->view('conflict_schedule', $result['data'], TRUE);
        }
        $this->response->json($result);
    }

    /**
     * 已经加入
     */
    public function joined_schedule()
    {
        $this->load->model('public/schedule_model');
        $plan = $this->input->post('plan', TRUE);
        $stage = $this->input->post('stage', TRUE);
        $schedule_id = $this->input->post('schedule_id', TRUE);
        $result = $this->schedule_model->joinedSchedule($this->_uid, $plan, $stage, $schedule_id);
        $this->response->json($result);
    }

    /**
     * 选课提示层
     */
    public function select_course_tip()
    {
        $result = $this->curriculum_model->selectCourseTip($this->_uid);
        $this->response->formatJson($result['code'], ['goods_names' => $result['goods_names']]);
    }

    /**
     * 关闭选课提示
     */
    public function select_course_tip_close()
    {
        $result = $this->curriculum_model->selectCourseTipClose($this->_uid);
        $this->response->formatJson($result['code']);
    }

    /**
     * 新手引导提示层
     */
    public function new_guide_tip()
    {
        $result = $this->curriculum_model->newGuideStatus($this->_uid);
        $this->response->formatJson($result['code']);
    }

    /**
     * 关闭新手引导提示层
     */
    public function new_guide_tip_close()
    {
        $result = $this->curriculum_model->newGuideStatusClose($this->_uid);
        $this->response->formatJson($result['code']);
    }

    /**
     * 学员是否购买正式课
     */
    public function have_formal_class()
    {
        $result = $this->curriculum_model->haveFormalClass($this->_uid);
        if ($result === TRUE) {
            $this->response->formatJson(200);
        }
        $this->response->formatJson(500);
    }

    /**
     * 教师评价
     */
    public function teacher_comment()
    {
        $id = $this->input->get('id', TRUE);
        $type = $this->input->get('type', TRUE);
        $result = $this->curriculum_model->switchTeacherComment($this->_uid, intval($id), $type);
        if (!empty($result)) {
            $this->response->formatJson(200, $result);
        } else {
            $this->response->formatJson(500);
        }
    }

    /**
     * 日语等级
     */
    public function my_grade()
    {
        $result = $this->thrift->service('Grade')->getUserGradeInfo($this->_uid);
        if(!empty($result)) {
            $isPassMax = 0;
            $max = $this->grade_model->_getMaxGrade();
            if (intval($result['grade_id']) === $max) {
                $pass = $this->grade_model->_isPassMaxGrade($this->_uid, $result['grade_id']);
                if ($pass) {
                    $isPassMax = 1;
                }
            }
            $result['is_pass_max'] = $isPassMax;
        }
        if (!empty($result)) {
            $this->response->formatJson(200, $result);
        } else {
            $this->response->formatJson(500);
        }
    }

    /**
     * 验证休学
     */
    public function validate_leave()
    {
        $this->load->model('teaching_center/leave_model');
        $method = $this->router->fetch_method();
        $able_method = array(
            'live_schedule',
            'select_course_major',
            'select_course_reservation',
            'my_course_major',
            'my_reservation',
            'oral_assessment'
        );
        if (in_array($method, $able_method)) {
            if ($this->leave_model->isSuspending($this->_uid)) {
                $result = $this->load->view('leave_doing', array(), TRUE);
                if ($method == 'oral_assessment') {
                    $this->response->formatJson(200, ['html' => $result]);
                } else {
                    $this->response->html($result);
                }
                exit;
            }
        }
    }
}
