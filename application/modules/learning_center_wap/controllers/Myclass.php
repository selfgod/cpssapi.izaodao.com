<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Myclass extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
    }

    //公共入口
    public function index()
    {
        $result = $this->layout->load('myclass', 'learning_center_wap');
        $this->response->html($result);
    }

    //wap学习中心精品课流程
    public function fine_class()
    {
        $this->load->model('fineclass_model');
        $result = $this->fineclass_model->getFineclassInfo($this->_uid);
        $this->response->json($result);
    }

    //学员精品课课程列表
    public function fine_class_schedule_list()
    {
        $this->load->model('fineclass_model');
        $result = $this->fineclass_model->getScheduleListInfo($this->_uid);
        $this->response->json($result);
    }

    //学员精品课课程表
    public function fine_class_schedule_lesson()
    {
        $this->load->model('fineclass_model');
        $result = $this->fineclass_model->getScheduleLessonInfo($this->_uid);
        $expire_time = $this->fineclass_model->getUserGoodsVaildTime($this->_uid);
        //取时间数组
        $week_day_array = $this->fineclass_model->getDayList($result['month_info']);
        $result['week_day_array'] = $week_day_array;
        $result['expire_time'] = $expire_time;
        $result['jump_url'] = MASTER_DOMAIN.'main.php/Wptouch';
        $this->response->json($result);
    }

    //学员精品课加入班级
    public function join_fine_class()
    {
        $this->load->model('fineclass_model');
        $result = $this->fineclass_model->joinFineClass($this->_uid);
        $this->response->json($result);
    }

    //获取班级信息
    public function get_classroom_info()
    {
        $this->load->model('fineclass_model');
        $result = $this->fineclass_model->getClassroomInfo();
        $this->response->json($result);
    }

}
