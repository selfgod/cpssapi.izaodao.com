<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UnitTest extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('unitTest_model');
    }

    /**
     * 单元测试准备页
     * @param $id
     */
    public function prepare($id)
    {
        $uid = $this->_uid;
        $scheduleId = intval($this->input->get('schedule_id'));
        $lessonId = intval($this->input->get('lesson_id'));
        $paperInfo = $this->unitTest_model->unitTestPrepare($uid, $id, $scheduleId);
        if (empty($paperInfo)) {
            redirect(LINK_HOST_CPSS_JP);
        } elseif (isset($paperInfo['over_limit'])) {
            $result = $this->layout->load('unit_exam_main', 'exam', $paperInfo);
            $this->response->html($result);
            return ;
        } else {
            $paperInfo['schedule_id'] = $scheduleId;
            $paperInfo['lesson_id'] = $lessonId;
            $result = $this->layout->load('unit_prepare_main', 'exam', $paperInfo);
            $this->response->html($result);
        }
    }

    /**
     * 检测用户的做题状态
     */
    public function checkStatus()
    {
        $paperId = intval($this->input->get('paper_id'));
        $status = $this->unitTest_model->checkStatus($this->_uid, $paperId);
        $this->response->formatJson(200, ['status' => $status]);
    }

    /**
     * 添加单元测试结果记录
     */
    public function addResult()
    {
        $paperId = intval($this->input->post('paper_id'));
        $scheduleId = intval($this->input->post('schedule_id'));
        $lessonId = intval($this->input->post('lesson_id'));
        $result = $this->unitTest_model->addResult($this->_uid, $paperId, $scheduleId, $lessonId);
        $this->response->formatJson(200, ['ret' => $result]);
    }

    /**
     * 开始考试
     * @param $id
     */
    public function index($id)
    {
        $data = $this->unitTest_model->exam($this->_uid, $id);
        if (empty($data)) {
            redirect(LINK_HOST_CPSS_JP);
        }
        if (isset($data['over_limit'])) {
            $result = $this->layout->load('unit_exam_main', 'exam', $data);
            $this->response->html($result);
            return ;
        }
        if ($data['exam_status'] === '1') {
            //已经交卷，跳到回顾
            redirect('/exam/unitTest/review?paper_id=' . $id);
        } else {
            $result = $this->layout->load('unit_exam_main', 'exam', $data);
            $this->response->html($result);
        }
    }

    /**
     * 获取单元测试某个分类下的题目
     */
    public function subjectDetail()
    {
        $paperId = intval($this->input->get('paper_id'));
        $cate = $this->input->get('cate');
        $result = $this->unitTest_model->getExamCate($this->_uid, $paperId, $cate);
        $this->response->formatJson(200, $result);
    }

    /**
     * 记录选择答案
     */
    public function makeChoice()
    {
        $paperId = intval($this->input->post('paper_id'));
        $questionId = intval($this->input->post('question_id'));
        $answerId = intval($this->input->post('answer_id'));
        $result = $this->unitTest_model->makeChoice($this->_uid, $paperId, $questionId, $answerId);
        $this->response->formatJson(200);
    }

    /**
     * 纠错
     */
    public function answerCorrection()
    {
        $paperId = intval($this->input->post('paper_id'));
        $questionId = intval($this->input->post('question_id'));
        $description = $this->input->post('description', TRUE);
        $this->unitTest_model->answerCorrection($this->_uid, $paperId, $questionId, $description);
        $this->response->formatJson(200);
    }

    /**
     * 交卷
     */
    public function submit()
    {
        $paperId = intval($this->input->post('paper_id'));
        $part = $this->input->post('part');
        $result = $this->unitTest_model->submit($this->_uid, $paperId, $part);
        if ($result) {
            $this->response->formatJson(200);
        } else {
            $this->response->formatJson(500);
        }
    }

    /**
     * 休息页
     */
    public function rest()
    {
        $paperId = intval($this->input->get('paper_id'));
        $data = $this->unitTest_model->rest($this->_uid, $paperId);
        if (empty($data)) {
            redirect(LINK_HOST_CPSS_JP . "exam/unitTest/$paperId");
        }
        $result = $this->layout->load('unit_rest_main', 'exam', $data);
        $this->response->html($result);
    }

    /**
     * 回顾页
     */
    public function review()
    {
        $paperId = intval($this->input->get('paper_id'));
        $data = $this->unitTest_model->review($this->_uid, $paperId);
        if (empty($data)) {
            redirect(LINK_HOST_CPSS_JP);
        }
        $result = $this->layout->load('unit_review_main', 'exam', $data);
        $this->response->html($result);
    }

    /**
     * 获取某个分类的回顾内容
     */
    public function reviewCate()
    {
        $paperId = intval($this->input->get('paper_id'));
        $cate = $this->input->get('cate');
        $data = $this->unitTest_model->reviewCate($this->_uid, $paperId, $cate);
        $this->response->formatJson(200, $data);
    }

    /**
     * 结果页
     */
    public function result()
    {
        $paperId = intval($this->input->get('paper_id'));
        $this->load->model('unitTest_result_model');
        $data = $this->unitTest_result_model->result($this->_uid, $paperId);
        $result = $this->layout->load('unit_result_main', 'exam', $data);
        $this->response->html($result);
    }

    /**
     * 结果页得分分布图
     */
    public function scoreChart()
    {
        $paperId = intval($this->input->get('paper_id'));
        $score = intval($this->input->get('score'));
        $this->load->model('unitTest_result_model');
        $this->output->set_content_type('jpeg');
        $this->unitTest_result_model->scoreChart($score, $paperId);
    }

    /**
     * 完成单元测试，提交得分数据
     */
    public function done()
    {
        $scheduleId = intval($this->input->post('schedule_id'));
        $scheduleLessonId = intval($this->input->post('schedule_lesson_id'));
        $paperId = intval($this->input->post('id'));
        $planId = intval($this->input->post('plan_id'));
        $planStageId = intval($this->input->post('plan_stage_id'));

        $this->load->model('unitTest_result_model');
        $result = $this->unitTest_result_model->done($this->_uid, $paperId, $scheduleId, $scheduleLessonId, $planId, $planStageId);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }
}
