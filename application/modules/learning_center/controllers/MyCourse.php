<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyCourse extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('learnCourse_model');
    }

    /**
     * 我的课程某个阶段课程详情
     */
    public function info()
    {
        $uid = $this->_uid;
        $scheduleId = $this->input->get('schedule_id');
        $result = $this->learnCourse_model->baseInfo($uid, $scheduleId);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }

    /**
     * 我的课程某个阶段课程课件，做题，资料详细列表
     */
    public function detailList()
    {
        $uid = $this->_uid;
        $scheduleId = $this->input->get('schedule_id');
        $cate = $this->input->get('cate');
        if (empty($cate)) {
            $cate = 'learn';
        }
        if (!in_array($cate, ['learn', 'exercise', 'datum', 'report', 'guide'])) {
            $cate = 'learn';
        }
        $result = $this->learnCourse_model->detailList($uid, $scheduleId, $cate);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }

    /**
     * 获取阶段课程课件的下载链接
     */
    public function download()
    {
        $uid = $this->_uid;
        $scheduleId = $this->input->get('schedule_id');
        $lessonId = $this->input->get('lesson_id');
        $result = $this->learnCourse_model->getDownloadLink($uid, $scheduleId, $lessonId);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }

    /**
     * talk教室地址
     */
    public function talkRoomUrl()
    {
        $uid = $this->_uid;
        $lesson_id = intval($this->input->cookie('zdtalkLessonId', TRUE));
        $data = $this->learnCourse_model->getLessonTalkRoom($uid, $lesson_id);
        $this->response->formatJson(200, $data);
    }
    /**
     * 更新首次计入班级字段
     */
    public function updateFirstView()
    {
        $this->load->model('schedule_model');
        $scheduleId = $this->input->get('schedule_id');
        $uid = $this->_uid;
        $data = $this->schedule_model->markFirstView($uid, $scheduleId);
        $this->response->formatJson(200, $data);
    }
}
