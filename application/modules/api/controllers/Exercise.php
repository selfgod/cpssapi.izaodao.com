<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exercise extends CI_Controller
{
    /**
     * 完成一课一练，单元测试
     * @param $exerciseType
     */
    public function done($exerciseType)
    {
        $uid = intval($this->input->get('uid'));
        $scheduleId = intval($this->input->get('schedule_id'));
        $scheduleLessonId = intval($this->input->get('schedule_lesson_id'));
        $execId = intval($this->input->get('id'));
        $planId = intval($this->input->get('plan_id'));
        $planStageId = intval($this->input->get('plan_stage_id'));
        $this->load->model('learning_system/exercise_model');

        $result = array(
            'code' => 500,
        );
        $params = array(
            'schedule_id' => $scheduleId,
            'schedule_lesson_id' => $scheduleLessonId,
            'uid' => $uid,
            'exercise_id' => $execId,
            'type' => $exerciseType,
            'plan_id' => $planId,
            'plan_stage_id' => $planStageId,
            'opt_type' => 1
        );
        $valid = $this->validation->validate('exercise_complete', $params);
        if ($valid !== TRUE) {
            $result['msg'] = $valid['msg'];
            $result['data']['score'] = 0;
        } else {
            $ret = FALSE;
            if ($exerciseType === 'practice') {
                $params['test_id'] = $execId;
                $ret = $this->exercise_model->submitPractice($params);
            } elseif ($exerciseType === 'unit') {
                $params['unit_id'] = $execId;
                $params['result_id'] = intval($this->input->get('result_id'));
                $ret = $this->exercise_model->submitUnitTest($params);
            }

            if ($ret) {
                $result['code'] = 200;
                $result['data']['score'] = $ret['score'];
                $result['data']['over_limit'] = isset($ret['over_limit']) ? 1 : 0;
                $result['data']['no_reward'] = isset($ret['no_reward']) ? 1 : 0;
            } else {
                $result['msg'] = '提交做题任务失败';
            }
        }

        $this->response->jsonp($result);
    }

    /**
     * 系统取消预约
     */
    public function systemCancelReservation()
    {
        $this->load->model('public/lesson_model');
        $uid = intval($this->input->post('uid'));
        $lesson_id = intval($this->input->post('lesson_id'));
        $catgory = intval($this->input->post('catgory'));
        $result = $this->lesson_model->userCancelReserveLesson($uid, $catgory, $lesson_id, TRUE);
        $this->response->json($result);
    }

    /**
     * 计算用户商品有效期更新message_user
     */
    public function messageUserExpireCompute()
    {
        $result = [];
        $uid = intval($this->input->post('uid'));
        $this->load->model('public/member_model');
        $res = $this->member_model->goodsExpireMessageUserUpdate($uid);
        if ($res !== FALSE) {
            $result['code'] = 200;
        } else {
            $result_code = get_options('result_code');
            $result['code'] = 282;
            $result['msg'] = isset($result_code[$result['code']]) ? $result_code[$result['code']] : 'FAIL';
        }
        $this->response->json($result);
    }

    /**
     * 关联早知道id与问题id
     */
    public function mapKnowId()
    {
        $knowId = intval($this->input->post('updateId'));
        $questionId = intval($this->input->post('exercisesId'));
        $this->load->model('learning_system/exercise_model');
        $result = $this->exercise_model->setKnowId($questionId, $knowId);
        if ($result) {
            $this->response->raw('1');
        } else {
            $this->response->raw('0');
        }
    }
}
