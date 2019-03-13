<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Myschedule extends My_Controller
{
    public $scheduleId;
    public $planId;
    public $stageId;

    /**
     * @var array
     */
    public $actionPageMap = array('learn' => 'learnPage', 'exercise' => 'exercisePage', 'datum' => 'datumPage', 'report' => 'reportPage', 'info' => 'scheduleInfoPage');

    /**
     * @var array
     */
    public $rightNavMap = array('live' => 'liveNav', 'record' => 'recordNav');

    public $detailContentMap = array('live' => 'liveDetail', 'record' => 'recordDetail', 'datum' => 'datumDetail', 'exercise' => 'exerciseDetail');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('learning_task_model');
        $this->load->model('datum_model');
        $this->load->model('exercise_model');
        $this->load->model('learning_report_model');
        $this->load->model('public/schedule_model');
        $this->load->model('public/business_validation_model');
        $this->load->model('public/plan_stage_model');
        $this->load->model('public/plan_model');
        $this->load->model('public/member_model');
        $this->load->library('layout');
        $noSuspend = $this->validation->validate('suspend', ['uid' => $this->_uid]);
        $method = $this->router->fetch_method();
        if ($noSuspend !== TRUE) {
            if ($method !== 'main') {
                $this->response->formatJson($noSuspend['code'], [], $noSuspend['msg']);
            } else {
                redirect('/');
            }
        }

        $this->initParams();
    }

    protected function initParams()
    {
        $default = array('schedule', 'plan', 'stage');

        foreach ($default as $param) {
            $value = intval($this->input->get($param));
            if (empty($value)) {
                $value = intval($this->input->post($param));
            }
            $name = $param . 'Id';
            $this->$name = $value;
        }
    }

    /**
     * 报到
     */
    public function checkIn()
    {
        $scheduleId = intval($this->input->post('scheduleId'));
        $schedule_lesson_id = intval($this->input->post('schedule_lesson_id'));
        $plan_id = intval($this->input->post('plan'));
        $plan_stage_id = intval($this->input->post('stage'));
        $params = [
            'schedule_id' => $scheduleId,
            'schedule_lesson_id' => $schedule_lesson_id,
            'uid' => $this->_uid,
            'plan_stage_id' => $plan_stage_id,
            'plan_id' => $plan_id,
            'opt_type' => 1,
            'interactive' => intval($this->input->post('interactive')),
            'learn' => intval($this->input->post('learn')),
            'teach' => intval($this->input->post('teach')),
            'comment' => $this->input->post('comment'),
            'check_in_source' => 1
        ];
        $this->load->model('public/lesson_model');
        $ret = $this->lesson_model->userCheckIn($params);
        if (!$ret || $ret['code'] !== 200) {
            $this->response->formatJson($ret['code'], [], $ret['msg']);
        } else {
            $ret['report_num'] = $this->lesson_model->userReportCount($this->_uid);
            $ret['first_report'] = ($ret['report_num'] > 1) ? FALSE : TRUE;
            $param = LINK_HOST_JP.'T/Share/index/param/'.$this->_uid.'-'.$ret['report_num'].'-'.($ret['first_report']?1:0).'-'.($ret['full_attendance']?1:0).'-1';
            $ret['qr_code'] = LINK_HOST_JP.'Api/Qrcode/create_url_qrcode?url='.$param;
            $ret_data = [
                'score' => $ret['score'],
                'live_report' => $ret['live_report'],
                'full_attendance' => $ret['full_attendance'],
                'report_num' => $ret['report_num'],
                'first_report' => $ret['first_report'],
                'qr_code' => $ret['qr_code'],
                'over_limit' => isset($ret['over_limit']) ? 1 : 0,
                'no_reward' => isset($ret['no_reward']) ? 1 : 0,
                'auto_unlock' => isset($ret['auto_unlock']) ? 1 : 0,
            ];
            $this->response->formatJson(200, $ret_data);
        }
    }

    /**
     * 获取期望直接跳转的页面和分类
     * @return array
     */
    protected function getPreferredPage()
    {
        $nav = $this->input->get('nav');
        $category = $this->input->get('category');
        $category = $category ? $category : 'record';
        $type = $this->input->get('type');
        return array('pre_nav' => $nav, 'pre_category' => $category, 'pre_type' => $type);
    }

    /**
     * 学习系统主页面
     * TODO 重构，业务逻辑提取
     * @param $scheduleId
     */
    public function main($scheduleId)
    {
        redirect('/#myCourse?id=' . $scheduleId);
        $planId = intval($this->input->get('plan'));
        $plan_stageId = intval($this->input->get('stage'));
        $uid = $this->_uid;
        $date = $this->input->get('date');
        $validData = array('uid' => $uid, 'plan_id' => $planId, 'plan_stage_id' => $plan_stageId, 'schedule_id' => $scheduleId);
        $result = $this->validation->validate('get_schedule', $validData);
        if ($result !== TRUE) {
            //todo 重定向到阶段课程选择页面
            $params = array('plan' => $planId, 'stage' => $plan_stageId);
            redirect('/#selectSchedule?' . http_build_query($params));
        }
        $preferred = $this->getPreferredPage();
        $joinedSchedules = $this->schedule_model->getUserScheduleIds($uid);
        $scheduleList = $this->schedule_model->getPlanStageScheduleList($planId, $plan_stageId, 0, FALSE, FALSE);
        $data['scheduleList'] = [];
        foreach ($scheduleList as $schedule) {
            if (in_array(intval($schedule['id']), $joinedSchedules)) {
                $schedule['week_circle'] = $this->schedule_model->getScheduleWeekCycle(intval($schedule['id']));
                $teachers = $this->schedule_model->getScheduleTeachers(intval($schedule['id']));
                $schedule['teachers'] = [];
                foreach ($teachers as $teacher) {
                    $schedule['teachers'][] = $teacher['name'];
                }
                if ($schedule['id'] === $scheduleId) {
                    $data['current_id'] = $scheduleId;
                    $valid = TRUE;
                    $this->schedule_model->updateCurrentSchedule($uid, $planId, $plan_stageId, $scheduleId);
                    //当前选择的阶段课程排第一个
                    array_unshift($data['scheduleList'], $schedule);
                } else {
                    $data['scheduleList'][] = $schedule;
                }
            }
        }
        if (isset($valid)) {
            if (!empty($date)) {
                $data['pre_date'] = $date;
            }
            foreach ($preferred as $name => $value) {
                $data[$name] = $value;
            }
            $plan = $this->plan_model->getPlanCacheData($planId);
            $stage = $this->plan_stage_model->getStageInfo($plan_stageId);
            $data['plan_name'] = $plan['name'];
            $data['stage_name'] = $stage['name'];
            $data['plan_id'] = $planId;
            $data['plan_stage_id'] = $plan_stageId;
            $data['able_del_schedule'] = FALSE;
            $user_message = $this->member_model->getUserMessage($uid);
            if (!empty($user_message) && intval($user_message['user_level']) > 1) {
                $schedule = $this->schedule_model->getScheduleInfo($scheduleId, 'business_type');
                if (!empty($schedule) && in_array(intval($schedule['business_type']), [1, 2])) {
                    $data['able_del_schedule'] = TRUE;
                }
            }
            $title = $stage['name'] . '_学习系统_早道日语网校_多语种在线学习平台';
            $ret = $this->layout->load('main', 'learning_system', $data, array('title' => $title));
            echo $ret;
        } else {
            redirect('/');
        }
    }

    /**
     * @param $name
     */
    public function menuSelected($name)
    {
        if (!isset($this->actionPageMap[$name])) {
            show_404();
        }
        $page = $this->actionPageMap[$name];
        if (method_exists($this, $page)) {
            $this->$page();
        } else {
            show_404();
        }
    }

    /**
     * @param $name
     */
    public function rightNavSelected($name)
    {
        if (!isset($this->rightNavMap[$name])) {
            show_404();
        }
        $nav = $this->rightNavMap[$name];
        if (method_exists($this, $nav)) {
            $this->$nav();
        } else {
            show_404();
        }
    }

    public function detailContent($name)
    {
        if (!isset($this->detailContentMap[$name])) {
            show_404();
        }
        $detail = $this->detailContentMap[$name];
        if (method_exists($this, $detail)) {
            $this->$detail();
        } else {
            show_404();
        }
    }

    /**
     * 直播课表详情内容
     */
    public function liveDetail()
    {
        $scheduleId = $this->scheduleId;
        $date = $this->input->get('date');
        $date = empty($date) ? date('Y-m-d') : $date;
        $uid = $this->_uid;
        $data = $this->learning_task_model->getScheduleLessons($uid, $scheduleId, $date, $this->planId, $this->stageId);

        if (!empty($data)) {
            $data['lessons'] = $this->exercise_model->exerciseLinkProvider($data['lessons'], $scheduleId, $this->planId, $this->stageId);
            echo $this->layout->loadView('live_list', 'learning_system', $data);
        } else {
            $data = $this->learning_task_model->noLessonToday($date, $uid, $scheduleId, $this->planId, $this->stageId);
            echo $this->layout->loadView('no_class', 'learning_system', $data);
        }
    }

    /**
     * 录播回顾详情列表
     */
    public function recordDetail()
    {
        $scheduleId = $this->scheduleId;
        $type = intval($this->input->get('type'));
        $page = intval($this->input->get('page'));
        $type = empty($type) ? 0 : $type;
        $page = empty($page) ? 1 : $page;
        $limit = 15;
        $uid = $this->_uid;
        $data = $this->learning_task_model->recordReviewList($uid, $scheduleId, $page, $limit, $type);
        if (!empty($data)) {
            echo $this->layout->load('record_list', 'learning_system', $data);
        } else {
            $data = array('category' => 'record', 'type' => get_options('finish_type', $type));
            echo $this->layout->loadView('no_content', 'learning_system', $data);
        }
    }

    public function recordNav()
    {
        echo $this->layout->load('record_panel_body', 'learning_system');
    }

    public function liveNav()
    {
        $scheduleId = $this->scheduleId;
        $date = $this->input->get('date');
        $date = empty($date) ? date('Y-m-d') : $date;

        $data['weekday'] = $this->learning_task_model->getScheduleCalendar($date, $scheduleId);
        $data['current'] = $date;
        $data['current_year'] = explode('-', $date)[0];
        $data['current_month'] = explode('-', $date)[1];
        $ret = $this->layout->load('live_panel_body', 'learning_system', $data);
        echo $ret;
    }

    /**
     * 做题任务右侧面板
     */
    public function exercisePage()
    {
        $category = $this->input->get('category');
        $type = intval($this->input->get('type'));
        $scheduleId = $this->scheduleId;
        $limit = 15;
        $uid = $this->_uid;
        $page = intval($this->input->get('page'));
        $page = empty($page) ? 1 : $page;
        $data = array();
        if (!empty($category)) {
            $data['current_top'] = $category;
        }
        if (!empty($type)) {
            $data['current_sub'] = get_options('finish_type', $type);
        }
        $params = array('schedule' => $scheduleId, 'stage' => $this->stageId);
        //错题本链接
        $data['error_record_link'] = MASTER_DOMAIN . 'main.php/Learningsystem/Myerror/test?' . http_build_query($params);
        $ret = $this->layout->load('exercise_right_panel', 'learning_system', $data);
        echo $ret;
    }

    /**
     * 一课一练，单元测试详情列表
     */
    public function exerciseDetail()
    {
        $scheduleId = $this->scheduleId;
        $category = $this->input->get('category'); // 一课一练/单元测试
        $page = intval($this->input->get('page'));
        $limit = 15;
        $uid = $this->_uid;
        $type = intval($this->input->get('type')); //全部 已完成 未完成
        $data = $this->exercise_model->getExerciseList($scheduleId, $uid, $limit, $page, $type, $category, $this->planId, $this->stageId);
        if (!empty($data)) {
            $data['data'] = $this->exercise_model->exerciseLinkProvider($data['data'], $scheduleId, $this->planId, $this->stageId);
            echo $this->layout->load('exercise_list', 'learning_system', $data);
        } else {
            $data = array('category' => $category, 'type' => get_options('finish_type', $type));
            echo $this->layout->loadView('no_content', 'learning_system', $data);
        }
    }

    /**
     * 上课任务页面
     */
    public function learnPage()
    {
        $scheduleId = $this->scheduleId;
        $date = $this->input->get('date');
        $category = $this->input->get('category');
        $type = $this->input->get('type');
        $scheduleInfo = $this->schedule_model->getScheduleInfo($scheduleId, 'class_mode');
        if (!empty($scheduleInfo)) {
            $data = array();
            if (!empty($type)) {
                $data['current_sub'] = get_options('finish_type', $type);
            }
            if (!empty($category)) {
                $data['current_top'] = $category;
            }
            if ($scheduleInfo['class_mode'] === '1') {
                //直播阶段课程
                if ($category === 'live') {
                    $layout = 'live_panel_body';
                } else {
                    $layout = 'record_panel_body';
                }
                $meta = array('components' => array('learn_panel_body' => array('components' => array($layout => array('type' => 'layout')))));
                if (!empty($date)) {
                    $today = $date;
                } else {
                    $today = date('Y-m-d');
                }
                $data['weekday'] = $this->learning_task_model->getScheduleCalendar($today, $scheduleId);
                $data['current'] = $today;
                $data['current_year'] = date('Y');
                $data['current_month'] = date('m');
                $ret = $this->layout->load('learn_right_panel_live', 'learning_system', $data, $meta);
            } else {
                //录播阶段课程
                $ret = $this->layout->load('learn_right_panel_record', 'learning_system', $data);
            }
            echo $ret;
        } else {
            show_error('阶段课程id错误');
        }
    }

    /**
     * 资料下载页
     */
    public function datumPage()
    {
        $type = intval($this->input->get('type'));
        $data = $meta = array();
        if (!empty($type)) {
            $data['current_sub'] = $type;
        }
        echo $this->layout->load('datum_right_panel', 'learning_system', $data);
    }

    /**
     * 资料下载详情列表
     */
    public function datumDetail()
    {
        $scheduleId = $this->scheduleId;
        $type = intval($this->input->get('type'));
        $page = intval($this->input->get('page'));
        $page = empty($page) ? 1 : $page;
        $limit = 15;
        $data = $this->datum_model->getDatums($scheduleId, $page, $limit, $type);
        if (!empty($data)) {
            echo $this->layout->load('datum_list', 'learning_system', $data);
        } else {
            $data = array('category' => 'datum', 'type' => 'all');
            echo $this->layout->loadView('no_content', 'learning_system', $data);
        }
    }

    /**
     * 学习报告页
     */
    public function reportPage()
    {
        $scheduleId = $this->scheduleId;
        $uid = $this->_uid;
        $planId = $this->planId;
        $planStageId = $this->stageId;

        $data['score'] = $this->learning_report_model->getUserScore($uid, 'total', $scheduleId);
        $data['total'] = $this->learning_report_model->checkInReport($uid, 1, $scheduleId, $planId, $planStageId);
        $data['live'] = $this->learning_report_model->checkInReport($uid, 2, $scheduleId, $planId, $planStageId);
        $data['practice'] = $this->learning_report_model->exerciseReport($uid, 'test', $scheduleId, $planId, $planStageId);
        $data['unit'] = $this->learning_report_model->exerciseReport($uid, 'unit', $scheduleId, $planId, $planStageId);
        $ret = $this->layout->load('report_right_panel', 'learning_system', $data);
        echo $ret;
    }

    /**
     * 基本信息页
     */
    public function scheduleInfoPage()
    {
        $scheduleId = $this->scheduleId;
        $scheduleInfo = $this->schedule_model->getScheduleBaseInfo($scheduleId);
        if (!empty($scheduleInfo)) {
            $scheduleInfo['class_week_time'] = $this->schedule_model->getScheduleWeekCycle($scheduleId);
            $scheduleInfo['relate_books'] = $this->schedule_model->getTextbooks($scheduleId);
            $scheduleInfo['relate_teachers'] = $this->schedule_model->getScheduleTeachers($scheduleId);
        }
        echo $this->layout->load('schedule_info', 'learning_system', $scheduleInfo);
    }

    /**
     * 直播课表右侧日历获取
     */
    public function monthlyCalendar()
    {
        $year_month = $this->input->get('year_month');
        $scheduleId = $this->scheduleId;
        $selected = $this->input->get('selected');
        $uid = $this->_uid;

        $data = $this->getUserCalendarData($scheduleId, $uid, $year_month);
        $selected = explode('|', $selected);

        if ($year_month === $selected[0]) {
            //是当前选择的年月
            $data['selected'] = intval($selected[1]);
        }

        echo $this->layout->loadView('calendar_detail', 'learning_system', $data);
    }

    protected function getUserCalendarData($scheduleId, $uid, $year_month)
    {
        //必须显示六行
        $firstDay = date('w', strtotime($year_month . '-01'));
        $totalDays = date('t', strtotime($year_month));
        $firstWeekDays = 7 - $firstDay;

        $data['calendar'] = $this->learning_task_model->getUserMonthlyCalendar($scheduleId, $uid, $year_month);
        $data['totalDays'] = $totalDays;
        $data['firstWeekDays'] = $firstWeekDays;
        $data['stuffingDays'] = 42 - $totalDays - $firstDay;

        return $data;
    }

    /**
     * 更新当前正在学习的阶段课程
     */
    public function updateSelectedSchedule()
    {
        $uid = $this->_uid;
        $scheduleId = $this->scheduleId;
        $planId = $this->planId;
        $planStageId = $this->stageId;

        //TODO validation
        $validData = array('uid' => $uid, 'plan_id' => $planId, 'plan_stage_id' => $planStageId, 'schedule_id' => $scheduleId);
        if ($this->validation->validate('get_schedule', $validData) === TRUE) {
            $result = $this->schedule_model->updateCurrentSchedule($uid, $planId, $planStageId, $scheduleId);
            $code = $result ? 200 : 500;
        } else {
            $code = 500;
        }
        $this->response->formatJson($code);
    }

    /**
     * 获取小红点提醒
     */
    public function scheduleNotice()
    {
        $scheduleId = $this->scheduleId;
        $result = $this->learning_task_model->userNoticeData($this->_uid, $scheduleId);
        $this->response->json($result);
    }

    /**
     * 更新红点提醒记录
     */
    public function updateNoticeHistory()
    {
        $scheduleId = $this->scheduleId;
        $type = $this->input->post('type');
        if (!empty($scheduleId) && !empty($type)) {
            $this->learning_task_model->updateDotHistory($this->_uid, $scheduleId, $type);
        }
    }

    /**
     * 删除阶段课程
     */
    public function delete()
    {
        $result = FALSE;
        $nextScheduleId = intval($this->input->post('nextId'));
        $delSchedule = intval($this->input->post('del'));
        $delReason = addslashes($this->input->post('reason', TRUE));
        $joinSchedule = intval($this->input->post('joinSchedule'));
        if (!empty($delReason)) {
            $schedule = $this->schedule_model->getScheduleInfo($delSchedule, 'business_type');
            if (!empty($schedule)) {
                if ($joinSchedule === 1) {
                    //加入阶段课程时删除阶段课程
                    $result = $this->learning_task_model->deleteSchedule($this->_uid, $delSchedule, $delReason, $nextScheduleId);
                } else {
                    $user_message = $this->member_model->getUserMessage($this->_uid);
                    if (!empty($user_message) && intval($user_message['user_level']) > 1 && in_array(intval($schedule['business_type']), [1, 2])) {
                        $result = $this->learning_task_model->deleteSchedule($this->_uid, $delSchedule, $delReason, $nextScheduleId);
                    }
                }
            }
        }
        if ($result) {
            $this->response->formatJson(200);
        } else {
            $this->response->formatJson(500, [], '删除失败');
        }
    }
}
