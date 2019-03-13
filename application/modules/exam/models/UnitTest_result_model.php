<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UnitTest_result_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('exam/unitTest_model');
    }

    /**
     * 考试结果页
     * @param $uid
     * @param $id
     * @return array
     */
    public function result($uid, $id)
    {
        $paperInfo = $this->unitTest_model->getPaperInfo($id);
        if (empty($paperInfo['total_num'])) {
            return [];
        }
        $examResult = $this->unitTest_model->getExamResult($uid, $id, TRUE);
        if (empty($examResult)) {
            return [];
        }
        if ($examResult['xuefen'] === '0') {
            //只有第一次发送得分请求
            $this->unitTest_model->updateExamResult($examResult['id'], ['xuefen' => 1]);
        }
        $this->load->helper('date');
        $questionScore = $this->unitTest_model->getQuestionScore($id);
        //总分
        $total_score = array_sum($questionScore);

        $data['questions']['单词'] = $this->getResultCate($uid, $id, $paperInfo['word'], $examResult['id']);
        $data['questions']['文法'] = $this->getResultCate($uid, $id, $paperInfo['grammar'], $examResult['id']);
        $data['questions']['读解'] = $this->getResultCate($uid, $id, $paperInfo['reading'], $examResult['id']);
        $data['questions']['听解'] = $this->getResultCate($uid, $id, $paperInfo['listening'], $examResult['id']);

        $data['score'] = $examResult['score'];
        $data['total_score'] = $total_score;
        $data['avg_score'] = $this->getAvgScore($id);
        $data['win_per'] = $this->getWinPer($id, $examResult['score']);
        $data['right_num'] = $examResult['right_num'];
        $data['right_per'] = round($examResult['right_num'] / $paperInfo['total_num'], 2) * 100;
        $data['use_time'] = remain_time($examResult['used_time']);
        $data['paper'] = $paperInfo;
        $data['xuefen'] = $examResult['xuefen'];
        $data['schedule_id'] = $examResult['schedule_id'];
        $data['lesson_id'] = $examResult['schedule_lesson_id'];
        $data['plan_id'] = $examResult['plan_id'];
        $data['plan_stage_id'] = $examResult['plan_stage_id'];
        $data['scoreChart'] = '/exam/unitTest/scoreChart?paper_id=' . $paperInfo['id'] . '&score=' . $data['score'];
        $data['name'] = $paperInfo['name'];
        return $data;
    }

    /**
     * 获取这套题的平均分
     * @param $paperId
     * @return int
     */
    public function getAvgScore($paperId)
    {
        $data = parent::$minor_db->select_avg('score')
            ->from('jh_zdt_exam_result')
            ->where(['paper_id' => $paperId])
            ->get()->row_array();
        return round($data['score'] ? $data['score'] : 0);
    }

    /**
     * 获取击败考生人数
     * @param $paperId
     * @param $score
     * @return float|int
     */
    public function getWinPer($paperId, $score)
    {
        $winNum = parent::$minor_db->from('jh_zdt_exam_result')
            ->where([
                'paper_id' => $paperId,
                'score<' => $score,
                'status' => 1
            ])
            ->count_all_results();

        $totalNum = parent::$minor_db->from('jh_zdt_exam_result')
            ->where([
                'paper_id' => $paperId,
                'status' => 1
            ])
            ->count_all_results();
        if ($totalNum === 0) {
            return 0;
        }
        return round($winNum / $totalNum, 2) * 100;
    }

    /**
     * 获取某个分类下的做题结果
     * @param $uid
     * @param $paperId
     * @param $qids
     * @param $resultId
     * @return array
     */
    public function getResultCate($uid, $paperId, $qids, $resultId)
    {
        $result = [];
        if (empty($qids)) {
            return [];
        }
        $totalNum = count($qids);
        $wrongNum = $rightNum = $i = $n = 0;
        $userExamLog = $this->unitTest_model->getUserExamCateLog($uid, $paperId, $resultId, $qids);
        foreach ($qids as $qid) {
            $i++;
            if (isset($userExamLog[$qid])) {
                if ($userExamLog[$qid]['status'] === '1') {
                    $rightNum++;
                    $result['questions'][$n][$i] = 1;
                } else {
                    $wrongNum++;
                    $result['questions'][$n][$i] = 0;
                }
            } else {
                $result['questions'][$n][$i] = -1;
            }
            if ($i % 6 === 0) {
                $n++;
            }
        }
        $result['right_per'] = round($rightNum / $totalNum, 2) * 100;
        $result['right_num'] = $rightNum;
        $result['wrong_num'] = $wrongNum;
        $result['undo_num'] = $totalNum - $rightNum - $wrongNum;
        return $result;
    }

    /**
     * 结果页得分分布图
     * @param $score
     * @param $paperId
     */
    public function scoreChart($score, $paperId)
    {
        $questionScore = $this->unitTest_model->getQuestionScore($paperId);
        //总分
        $total_score = array_sum($questionScore);
        $peopleScore = $this->getUserPaperScore($paperId);

        $num = intval(floor($total_score / 10));
        $n = $num > 12 ? 9 : 6;
        $legend[0] = '低于' . ($n * 10) . " ";
        $data[0] = 0;

        //生成分数段
        for ($i = $n; $i < $num; $i++) {
            if ($i + 1 === $num || ($n === 9 && $i + 2 === $num)) {
                $legend[$i - $n + 1] = ' ' . ($i * 10) . '以上';
            } else {
                $step = $num > 12 ? 19 : 9;
                $legend[$i - $n + 1] = ($i * 10) . '-' . ($i * 10 + $step);
            }
            $data[$i - $n + 1] = 0;
            if ($num > 12) {
                $i++;
            }
        }

        foreach ($peopleScore as $val) {
            $a = intval($val / 10);
            $b = $a < $n ? $n - 1 : $a;
            $b = $b - $n + 1;
            if (!isset($data[$b])) {
                $b--;
            }
            $data[$b]++;
        }

        $a = intval($score / 10);
        $b = $a < $n ? $n - 1 : $a;
        $b = $b - $n + 1;
        if (!isset($data[$b])) {
            $b--;
        }
        $my = $legend[$b];
        $datay = $datax = [];
        foreach ($data as $key => $val) {
            $v = ($val / count($peopleScore)) * 100;
            $data[$key] = round($v, 1);
            $datay[] = $data[$key];
            $datax[] = $legend[$key];
        }
        $this->createImage($datay, $datax, 440, 20, 251, $my);
    }

    /**
     * 创建图片
     * @param $datay
     * @param $datax
     * @param $twidth
     * @param $tspace
     * @param $height
     * @param $my
     */
    public function createImage($datay, $datax, $twidth, $tspace, $height, $my)
    {
        $path = dirname(APPPATH) . '/static/lib/msyh.ttf';
        $i = 0;
        $j = 0;
        $num = sizeof($datay);
        $dataname = $datax;
        $datavalue = $datay;
        $width = $twidth;
        $im = imagecreate($width, $height);//创建图像
        $bgcolor=imagecolorallocate($im,255,255,255);//背景色
        $jcolor = imagecolorallocate($im, 195, 227, 203);//矩形的背景色
        $jcolor1 = imagecolorallocate($im, 105, 185, 126);//矩形的背景色
        $acolor = imagecolorallocate($im, 105, 185, 126);//线的颜色
        $fontcolor = imagecolorallocate($im, 135, 135, 135);
        $fontcolor1 = imagecolorallocate($im, 124, 124, 124);
        imageline($im, 40, $height - 40, $width - 25, $height - 40, $acolor);//X轴
        imageline($im, 40, $height - 40, 40, 25, $acolor);//Y轴
        imagefilledrectangle($im, $width - 125, $height - 14, $width - 110, $height - 5, $jcolor1);//画矩形
        imagettftext($im, 8, 0, $width - 105, $height - 5, $fontcolor, $path, "我所在的分数段");//在柱子下面写出值
        while ($j <= 5) {
            $top = 25 + $j * ($height - 40 - 25) / 5;
            $text = 100 - $j * 20 . "%";
            $left = 15 + ($j - strlen($text) * 2);
            imageline($im, 40, $top, 43, $top, $acolor);//画出刻度
            imagettftext($im, 8, 0, $left, $top + 6, $fontcolor, $path, $text);//标出刻度值
            $j++;
        }
        while ($i < $num) {
            $temcolor = $dataname[$i] == $my ? $jcolor1 : $jcolor;
            $left = $width - 40 - 25;
            $left = $left / $num * $i + 40;
            $temheight = $height - 41 - $datavalue[$i] / (100 / ($height - 40 - 25));
            $step = ($width - 40 - 25) / $num;
            imagefilledrectangle($im, $left + ($step - $tspace) / 2, $temheight, $left + ($step - $tspace) / 2 + $tspace, $height - 41, $temcolor);//画矩形
            $text = $datavalue[$i] . "%";
            imagettftext($im, 8, 0, $left + ($step - strlen($text) * 6) / 2, $temheight - 3, $fontcolor1, $path, $text);//在柱子上面写出值
            $text = $dataname[$i];
            imagettftext($im, 8, 0, $left + ($step - strlen($text) * 4) / 2, $height - 25, $fontcolor, $path, $text);//在柱子下面写出值
            $i++;
        }
        imagejpeg($im);
    }

    /**
     * 获取该套题的所有人的分
     * @param $paperId
     * @return array
     */
    public function getUserPaperScore($paperId)
    {
        $data = [];
        $result = parent::$minor_db->select('score')
            ->from('jh_zdt_exam_result')
            ->where([
                'paper_id' => $paperId,
                'status' => 1
            ])->get()->result_array();
        foreach ($result as $score) {
            $data[] = $score['score'];
        }
        asort($data);
        return $data;
    }

    /**
     * 完成单元测试，提交得分数据
     * @param $uid
     * @param $paperId
     * @param $scheduleId
     * @param $scheduleLessonId
     * @param $planId
     * @param $planStageId
     * @return array
     */
    public function done($uid, $paperId, $scheduleId, $scheduleLessonId, $planId, $planStageId)
    {
        $params = array(
            'schedule_id' => $scheduleId,
            'schedule_lesson_id' => $scheduleLessonId,
            'uid' => $uid,
            'exercise_id' => $paperId,
            'type' => 'unit',
            'plan_id' => $planId,
            'plan_stage_id' => $planStageId,
            'opt_type' => 1
        );
        $valid = $this->validation->validate('exercise_complete', $params);
        if ($valid !== TRUE) {
            return ['code' => $valid['code'], 'msg' => $valid['msg'], 'data' => []];
        }
        $examResult = $this->unitTest_model->getExamResult($uid, $paperId, TRUE);
        if (empty($examResult)) {
            return ['code' => 500, 'msg' => '没有做题记录', 'data' => []];
        }
        $params['unit_id'] = $paperId;
        $params['result_id'] = intval($examResult['id']);
        $this->load->model('learning_system/exercise_model');
        $ret = $this->exercise_model->submitUnitTest($params);
        if ($ret) {
            return ['code' => 200, 'data' => [
                'score' => $ret['score'],
                'over_limit' => isset($ret['over_limit']) ? 1 : 0,
                'no_reward' => isset($ret['no_reward']) ? 1 : 0
            ], 'msg' => ''];
        } else {
            return ['code' => 500, 'msg' => '提交做题任务失败', 'data' => []];
        }
    }
}
