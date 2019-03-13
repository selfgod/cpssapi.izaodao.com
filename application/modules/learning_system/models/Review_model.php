<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Review_model extends MY_Model {

    /**
     * 录播回顾详情页数据
     * @param $schedule_lesson_id
     * @param bool $isMajor
     * @param $planId
     * @param $planStageId
     * @param $curricular
     * @return mixed
     */
    public function reviewInfo($schedule_lesson_id, $isMajor = TRUE, $planId, $planStageId, $curricular)
    {
        $query = parent::$main_db->select('ssle.id, ssle.schedule_id, ssle.start_time, ssle.end_time,
        ssle.record_link, ssle.name, ssle.alias, zti.name as teacher_name, ssle.test_id, ss.name as schedule_name, ss.curricular_system')
            ->from('sty_schedule_lesson ssle')
            ->join('zd_class.zd_teacher_info zti', 'zti.uid = ssle.teacher_id', 'left')
            ->join('sty_schedule ss', 'ss.id = ssle.schedule_id', 'left')
            ->where(array(
                'ssle.id' => $schedule_lesson_id,
                'ssle.is_del' => 0,
                'ss.is_del' => 0
            ))->limit(1)->get();
        if ($lessonInfo = $query->row_array()) {
            if (!empty($lessonInfo['alias'])) {
                $lessonInfo['name'] = $lessonInfo['alias'];
            }
            if ($lessonInfo['curricular_system'] !== '1') {//非主修课
                $lessonInfo['name'] = $lessonInfo['schedule_name'] . ': ' . $lessonInfo['name'];
            }
            $lessonInfo['record_link'] = $this->generateDownUrl($lessonInfo['record_link']);
        }
        return $lessonInfo;
    }

    /**
     * 根据阶段课程id得到相关课件列表
     * @param $schedule_id
     * @param $uid
     * @return mixed
     */
    public function reviewlist($schedule_id,$uid)
    {
        //判断是否是直播课
        $query = parent::$main_db->select('class_mode,curricular_system')
            ->from('sty_schedule_lesson')
            ->where(array(
                'schedule_id' => $schedule_id,
                'is_del' => 0,
            ))->limit(1)->get();
        $lessonInfo = $query->row_array();
        if($lessonInfo['class_mode'] === '1' ){//直播课
            if($lessonInfo['curricular_system'] === '1'){//主修课
                $query = parent::$main_db->select('id,name')
                    ->from('sty_schedule_lesson')
                    ->where(array(
                        'schedule_id' => $schedule_id,
                        'is_del' => 0,
                        'record_link <>'=> '',
                        'start_time >' => 1000
                    ))
                    ->order_by('start_time desc')->get();
                $list = $query->result_array();
            }else{//预约制
                $query = parent::$main_db->select('schedule_lesson_id as id,name')
                    ->from('sty_user_schedule_lesson sus')
                    ->join('sty_schedule_lesson ss', 'ss.id = sus.schedule_lesson_id', 'left')
                    ->where(array(
                        'sus.uid' => $uid,
                        'sus.schedule_id' => $schedule_id,
                        'sus.is_del' => 0,
                        'sus.reservation_status' => 1,
                        'sus.reservation_time >' => 1000,
                        'ss.record_link <>'=> ''
                    ))
                    ->order_by('sus.reservation_time desc')->get();
                $list = $query->result_array();
            }
        }else{//录播课
            $query = parent::$main_db->select('id,name')
                ->from('sty_schedule_lesson')
                ->where(array(
                    'schedule_id' => $schedule_id,
                    'is_del' => 0,
                    'record_link <>'=> ''
                ))
                ->order_by('order asc')->get();
            $list = $query->result_array();
        }
        return $list;
    }

    /**
     * 通过百度bos生成下载地址
     * @param $originUrl
     * @return string
     */
    public function generateDownUrl($originUrl)
    {
        $filename = pathinfo($originUrl, PATHINFO_FILENAME);
        if ($filename === NULL) {
            return '';
        }
        $filename .= '.mp4';
        $bucket = 'course-mct';
        $object = '/media/' . $filename;

        $this->load->library('bos');
//        if (!$this->bos->check_file_exists($object, $bucket)) {
//            return '';
//        }
        $url = $this->bos->generate_get_object_url($bucket, $object);
        return $url ? $url : '';
    }
}