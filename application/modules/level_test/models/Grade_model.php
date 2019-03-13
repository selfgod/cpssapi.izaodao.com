<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grade_model extends MY_Model
{
    /**
     * 获取所有等级信息
     * @return array
     */
    public function getGradeDetails($uid)
    {
        $this->load->library('thrift');
        $userGradeInfo = $this->thrift->service('User')->getUserLearnInfo($uid);
        $userGrade = intval($userGradeInfo['grade_id']);
        $gradeList = $this->thrift->service('Grade')->getGradeList();
        $data = [];
        $n = 0;
        foreach ($gradeList as $i => $grade) {
            $gradeId = intval($grade['grade_id']);
            $row['grade_id'] = $gradeId;
            $row['upgrade'] = $row['current'] = $row['is_max'] = $row['is_pass_max'] = 0;
            $row['upgrade_prompt'] = '';
            if ($userGrade >= $gradeId) {
                $row['gradeImg'] = img_url('level_test', 'level_big_on_0' . $gradeId . '.png');
                $row['upgrade'] = 1;//是否已升级
                if ($userGrade === $gradeId) $row['current'] = 1;//是否是当前等级
            } else {
                $row['gradeImg'] = img_url('level_test', 'level_big_off_0' . $gradeId . '.png');
            }
            if ($row['upgrade'] === 0) {
                if ($gradeId === 1) {
                    $grade_name = '零基础';
                } else {
                    $grade_name = 'L' . ($gradeId - 1);
                }
                $row['upgrade_prompt'] = '通过' . $grade_name . '等级测试可升级至该等级';
            }
            if ($gradeId === COUNT($gradeList)){
                $row['is_max'] = 1;//是否是最大等级
                if($this->_isPassMaxGrade($uid,$gradeId)){
                    $row['is_pass_max'] = 1;
                }
            }
            $row['grade_desc'] = $grade['description'];
            $data[$n][] = $row;
            if (($i + 1) % 3 === 0) {
                $n++;
            }
        }
        return $data;
    }
    /**
     * 获取最大等级
     * @return int
     */
    public function _getMaxGrade(){
        $result = $this->thrift->service('Grade')->getMaxGrade();
        return $result;
    }
    /**
     * 是否通过最大等级考试
     * @return bool
     */
    public function _isPassMaxGrade($uid,$grade_id){
        $result = $this->thrift->service('Grade')->isPassGrade($uid,$grade_id);
        return $result;
    }

    /**
     * 测试专用 返回题号
     * @param $uid
     * @return array
     */
    public function getQuestionIds($uid)
    {
        $questionIds = [];
        $unSubmitExam = parent::$main_db->from('sty_user_grade_exam_result')
            ->select('id,grade_id')
            ->where([
                'uid' => $uid,
                'is_submit' => 0,
                'is_del' => 0
            ])->get()->row_array();
        if (!empty($unSubmitExam)) {
            $questions = parent::$main_db->from('sty_user_grade_exam_log')
                ->select('question_id')
                ->where([
                    'uid' => $uid,
                    'result_id' => $unSubmitExam['id'],
                    'is_del' => 0
                ])->get()->result_array();
            if (!empty($questions)) {
                $questionIds = array_column($questions, 'question_id');
            }
        }
        return $questionIds;
    }

    /**
     * 用户学习文案
     * @param $baseLevel
     * @param $level
     * @return bool
     */
    public function learnProgram($baseLevel, $level)
    {
        return (($baseLevel & $level) != 0) ? TRUE : FALSE;
    }
}