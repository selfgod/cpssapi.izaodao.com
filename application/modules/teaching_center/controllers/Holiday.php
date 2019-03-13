<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Holiday extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('leave_model');

        $this->start_time = $this->input->post('start_time', TRUE) ? addslashes($this->input->post('start_time', TRUE)) : '';
        $this->end_time = $this->input->post('end_time', TRUE) ? addslashes($this->input->post('end_time', TRUE)) : '';
        $this->reason = $this->input->post('reason', TRUE) ? addslashes($this->input->post('reason', TRUE)) : '';
    }

    /**
     * 请假
     */
    public function leave()
    {
        $data = $this->leave_model->leaveDetail($this->_uid);
        $result = $this->layout->load('leave', 'teaching_center', $data);
        $this->response->html($result);
    }

    /**
     * 请假提交操作
     */
    public function leave_submit()
    {


        $result = $this->leave_model->leaveSubmit($this->_uid, $this->start_time, $this->end_time, $this->reason);
        $this->response->json($result);
    }

    public function cancel_leave()
    {
        $leave_id = $this->input->post('leave_id', TRUE) ? intval($this->input->post('leave_id', TRUE)) : 0;
        $result = $this->leave_model->cancelLeave($this->_uid, $leave_id);
        $this->response->json($result);
    }

    /**
     * 休学
     */
    public function suspend()
    {
        $this->load->model('public/member_model');
        $learn = $this->member_model->getLearnInfo($this->_uid);
        $data = $this->leave_model->suspendDetail($this->_uid);
        $data['last_expire'] = $learn['last_expire'] ?: '';
        $result = $this->layout->load('suspend', 'teaching_center', $data);
        $this->response->html($result);
    }

    /**
     * 休学操作
     */
    public function suspend_submit()
    {
        $result = $this->leave_model->suspendSubmit($this->_uid, $this->start_time, $this->end_time, $this->reason);
        $this->response->json($result);
    }

    /**
     * 停止休学
     */
    public function suspend_stop()
    {
        $result = $this->leave_model->suspendStop($this->_uid);
        $this->response->json($result);
    }
}