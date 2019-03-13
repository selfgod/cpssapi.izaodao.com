<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download_model extends MY_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('learning_task_model');
        $this->load->model('review_model');
    }

    /**
     * 获取下载地址
     * @param $uid
     * @param $planId
     * @param $planStageId
     * @param $scheduleId
     * @param $scheduleLessonId
     * @return string
     */
    public function getLink($uid, $planId, $planStageId, $scheduleId, $scheduleLessonId)
    {
        if (empty($scheduleId)) {//非主修课
            $this->load->model('public/schedule_model');
            $info = $this->schedule_model->getCategoryScheduleLessonInfo($scheduleLessonId);
            if (empty($info)) {
                return '';
            }
            $cate = get_options('reservation_type', $info['content_cat']);
            if ($cate === FALSE || $cate === 1) {
                return '';
            }
            $count = parent::$main_db->from('sty_user_goods_info')
                ->where([
                    'uid' => $uid,
                    'type' => $cate,
                    'is_del' => 0
                ])->count_all_results();
            if (empty($count)) {
                return '';
            }
        } else {//主修课
            $validData = array(
                'uid' => $uid,
                'plan_id' => $planId,
                'plan_stage_id' => $planStageId,
                'schedule_id' => $scheduleId
            );
            $valid = $this->validation->validate('major_download', $validData);
            if ($valid !== TRUE) {
                $result['code'] = $valid;
                $result['msg'] = $valid['msg'];
                $this->response->json($result);
                return;
            }
        }
        $schedule_lesson_id = intval($scheduleLessonId);
        if (!empty($schedule_lesson_id)) {
            $scheduleInfo = $this->learning_task_model->getScheduleLessonInfo($schedule_lesson_id);
            if (!empty($scheduleInfo)) {
                if (!empty($scheduleInfo['record_link'])) {
                    return $this->review_model->generateDownUrl($scheduleInfo['record_link']);
                }
            }
        }
        return '';
    }
}