<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course extends CI_Controller
{
    /**
     * 加入阶段课程
     */
    public function joinSchedule()
    {
        $this->load->model('public/schedule_model');
        $uid = intval($this->input->post('uid')) ? $this->input->post('uid') : $this->input->get('uid');
        $plan_id = intval($this->input->post('plan_id')) ? $this->input->post('plan_id') : $this->input->get('plan_id');
        $plan_stage_id = intval($this->input->post('plan_stage_id')) ? $this->input->post('plan_stage_id') : $this->input->get('plan_stage_id');
        $schedule_id = intval($this->input->post('schedule_id')) ? $this->input->post('schedule_id') : $this->input->get('schedule_id');
        $class_mode = intval($this->input->post('class_mode')) ? $this->input->post('class_mode') : $this->input->get('class_mode');
        $result = $this->schedule_model->joinScheduleBefore($uid, $plan_id, $plan_stage_id, $schedule_id, -1);
        $res = [];
        if (!empty($result) && $result['code']) {
            $res['code'] = $result['code'];
            $result_code = get_options('result_code');
            if (isset($result_code[$result['code']])) {
                $res['msg'] = $result_code[$result['code']];
            }
        }
        $this->response->json($res);
    }

    /**
     * 删除阶段课程
     */
    public function delSchedule()
    {
        $this->load->model('public/schedule_model');
        $uid = intval($this->input->post('uid'));
        $conflict_id = intval($this->input->post('conflict_id', TRUE));
        $conflict_type = intval($this->input->post('conflict_type', TRUE));
        $conflict_desc = addslashes($this->input->post('conflict_desc', TRUE));
        $res = [];
        if (!in_array($conflict_type, array(1, 2, 3)) && empty($conflict_desc)) {
            $res['code'] = 500;
        } else {
            $result = $this->schedule_model->delConflictSchedule($uid, $conflict_id, $conflict_type, $conflict_desc);
            if (!empty($result) && $result['code']) {
                $res['code'] = $result['code'];
                $result_code = get_options('result_code');
                if (isset($result_code[$result['code']])) {
                    $res['msg'] = $result_code[$result['code']];
                }
            }
        }
        $this->response->json($res);
    }

    /**
     * 获取指定用户的直播出勤率 总出勤率
     */
    public function checkInReport()
    {
        $uid = intval($this->input->get('uid'));
        $schedule_id = intval($this->input->get('schedule'));
        $planId = intval($this->input->get('plan'));
        $planStageId = intval($this->input->get('plan_stage'));
        if (empty($uid) || empty($schedule_id) || empty($planId) || empty($planStageId)) {
            $this->response->formatJson(500, [], '参数错误');
        } else {
            $this->load->model('learning_system/learning_report_model');

            $total = $this->learning_report_model->checkInReport($uid, 1, $schedule_id, $planId, $planStageId);
            $live = $this->learning_report_model->checkInReport($uid, 2, $schedule_id, $planId, $planStageId);

            $this->response->formatJson(200, ['total' => $total, 'live' => $live]);
        }
    }

    /**
     * zdtalk报到
     */
    public function checkIn()
    {
        $sessionId = $this->input->post('session_id');
        if ($this->passport->init($sessionId)) {
            $uid = $this->passport->getUid();
            $params = [
                'schedule_id' => intval($this->input->post('schedule_id')),
                'schedule_lesson_id' => intval($this->input->post('lesson_id')),
                'uid' => $uid,
                'plan_stage_id' => 0,
                'plan_id' => 0,
                'opt_type' => 1,
                'interactive' => intval($this->input->post('interactive')),
                'learn' => intval($this->input->post('learn')),
                'teach' => intval($this->input->post('teach')),
                'comment' => $this->input->post('comment'),
                'check_in_source' => 2
            ];
            $this->load->model('public/lesson_model');
            $ret = $this->lesson_model->userCheckIn($params);
            if (!$ret || $ret['code'] !== 200) {
                $this->response->formatJson($ret['code'], [], $ret['msg']);
            } else {
                $this->response->formatJson(200, ['score' => $ret['score'], 'live_report' => $ret['live_report'], 'over_limit' => isset($ret['over_limit']) ? 1 : 0, 'no_reward' => isset($ret['no_reward']) ? 1 : 0]);
            }
        } else {
            $this->response->formatJson(401, [], '用户未登录');
        }
    }

    /**
     * 处理用户行为
     */
    public function processUserAction()
    {
        $action_id = intval($this->input->post('action_id'));
        $this->load->model('public/schedule_model');
        $res = $this->schedule_model->handleUserScheduleAction($action_id);
        if (!empty($res)) {
            $this->response->formatJson($res['code'], [], $res['msg']);
        } else {
            $this->response->formatJson(500, [], '接口发生错误');
        }
    }
}
