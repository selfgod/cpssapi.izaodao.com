<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $noSuspend = $this->validation->validate('suspend', ['uid' => $this->_uid]);
        if ($noSuspend !== TRUE) {
            $this->response->formatJson($noSuspend['code'], [], $noSuspend['msg']);
        }
        $this->load->model('public/download_model');
    }

    public function index($schedule_lesson_id)
    {
        $planId = intval($this->input->get('plan'));
        $plan_stageId = intval($this->input->get('stage'));
        $scheduleId = intval($this->input->get('schedule'));
        $uid = $this->_uid;

        $link = $this->download_model->getLink(
            $uid,
            $planId,
            $plan_stageId,
            $scheduleId,
            $schedule_lesson_id
        );

        if (!empty($link)) {
            $code = 200;
            $data = $link;
            $msg = '';
        } else {
            $code = 404;
            $data = [];
            $msg = '参数错误';
        }

        $this->response->formatJson($code, $data, $msg);
    }
}
