<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Survey_model extends MY_Model {

    /**
     * 提交用户上课学习感受
     * @param $uid
     * @param $teacherId
     * @param $scheduleId
     * @param $scheduleLessonId
     * @param $interactive
     * @param $learn
     * @param $teach
     * @param $comment
     */
    public function submitLearnFeeling($uid, $teacherId, $scheduleId, $scheduleLessonId,
        $interactive, $learn, $teach, $comment,$check_in_source)
    {
        $result = parent::$main_db->from('sty_user_feeling')
            ->where(array(
                'uid' => $uid,
                'schedule_lesson_id' => $scheduleLessonId,
                'is_del' => 0
            ))
            ->count_all_results();

        if ($result === 0) {
            $data = array(
                'uid' => $uid,
                'teacher_id' => $teacherId,
                'schedule_id' => $scheduleId,
                'schedule_lesson_id' => $scheduleLessonId,
                'interactive' => $interactive,
                'learn' => $learn,
                'teach' => $teach,
                'comment' => $comment,
                'check_in_source' => $check_in_source
            );
            parent::$main_db->insert('sty_user_feeling', $data);
        }
    }
}