<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Review extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/business_validation_model');
        $noSuspend = $this->validation->validate('suspend', ['uid' => $this->_uid]);
        if ($noSuspend !== TRUE) {
            redirect('/');
        }
        $this->load->library('layout');
    }

    /**
     * 录播回顾详情页
     * @param $schedule_lesson_id
     */
    public function reviewPage($schedule_lesson_id)
    {
        $curricular = $this->input->get('curricular');

        //TODO validation
        $checkedIn = $this->business_validation_model->validateCheckedIn($this->_uid, $schedule_lesson_id);

        $this->load->model('review_model');
        $isMajor = empty($curricular) ? TRUE : FALSE;
        $reviewInfo = $this->review_model->reviewInfo($schedule_lesson_id,
            $isMajor, '', '', $curricular);
        $ableTo = TRUE;
        $isReserved = TRUE;
        if (!empty($reviewInfo)) {
            if ($reviewInfo['curricular_system'] === '1') {
                //是否加入阶段课程
                $ableTo = $this->business_validation_model->validateUserJoinedSchedule($this->_uid, $reviewInfo['schedule_id']);
            }else {
                //是否预约
                $isReserved = $this->business_validation_model->validateReserved($this->_uid, $schedule_lesson_id);
            }
            if ($ableTo === FALSE) {
                show_404();
            }
            $reviewlist = $this->review_model->reviewlist($reviewInfo['schedule_id'], $this->_uid);
            $data = array(
                'title' => $reviewInfo['name'],
                'teacher' => $reviewInfo['teacher_name'],
                'schedule_id' => $reviewInfo['schedule_id'],
                'lesson_id' => $schedule_lesson_id,
                'checkedIn' => $checkedIn,
                'link' => $reviewInfo['record_link'],
                'list' => $reviewlist,
                'show_list' => $curricular ? FALSE : TRUE,
                'is_reserved' => $isReserved ? TRUE : FALSE
            );
            $ret = $this->layout->load('review_page', 'learning_system', $data);
            echo $ret;
        } else {
            show_404();
        }
    }
}