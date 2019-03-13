<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grade extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('grade_model');
        $this->load->library('thrift');
    }
    /**
     * 未测试首页、级别说明、定升级恭喜
     *
     */
    public function main()
    {
        //用户信息
        $isPassMax = 0;
        $isMaxGrade = 0;
        $is_selection = 0;
        $type = $this->input->cookie("cookie_grade_type");
        $user['data'] = $user_info = $this->thrift->service('Grade')->getUserGradeInfo($this->_uid);
        if(!empty($user_info)){
            $learn_program = $this->grade_model->learnProgram(1,$user_info['learn_program']); //有主修课有选课button
            if($learn_program){
                $is_selection = 1;
            }
        }
        $apply = $this->thrift->service('Grade')->isUnGradeApply($this->_uid);
        $user['c_grade_type'] = 0;
        if(isset($type)){
            $user['c_grade_type'] = $type;
        }
        if(!empty($user['data'])){
            $max_grade = $this->grade_model->_getMaxGrade();
            if(intval($user_info['grade_id']) === $max_grade){
                $isMaxGrade = 1;
                if($this->grade_model->_isPassMaxGrade($this->_uid,$user_info['grade_id'])){
                    $isPassMax = 1;
                }
            }
        }
        $user['is_max_grade'] = $isMaxGrade;
        $user['is_pass_max'] = $isPassMax;
        $user['apply'] = $apply;
        $user['grade_exam_name'] = '日语等级测试';
        $user['is_selection'] = $is_selection;
        $result = $this->layout->load('level_index_main', 'level_test', $user);
        $this->response->html($result);
    }
    /**
     * 等级信息ajax
     */
    public function level_info_ajax(){
        $results = $this->grade_model->getGradeDetails($this->_uid);
        if(empty($results)){
            $this->response->formatJson(500);
        }
        $this->response->formatJson(200, $results);
    }
    /**
     * 考试
     */
    public function exam()
    {
        header('Cache-Control:no-cache,must-revalidate');
        header('Pragma:no-cache');
        header("Expires:0");
        $prepare = $this->thrift->service('Grade')->gradeExamPrepare($this->_uid);
        if (empty($prepare)) {
            redirect('grade/main');
        }
        $result = $this->layout->load('level_test_main', 'level_test', $prepare);
        $this->response->html($result);
    }

    /**
     * 结果回顾列表
     */
    public function examList()
    {
        $data['data'] = $this->thrift->service('Grade')->getExamList($this->_uid);
        $data['grade_exam_name'] = '历史测评结果';
        $result = $this->layout->load('level_result_main', 'level_test', $data);
        $this->response->html($result);
    }

    /**
     * 生成考题 ajax
     */
    public function generateExam()
    {
        $gradeId = $this->input->post('grade');
        $generate = $this->thrift->service('Grade')->generateExamQuestion($this->_uid, intval($gradeId));
        if($generate === TRUE){
            $this->response->formatJson(200);
        }
        $this->response->formatJson(500);
    }

    public function subjectDetail()
    {
        $cate = $this->input->get('cate');
        $result = $this->thrift->service('Grade')->getExamCate($this->_uid, $cate);
        $this->response->formatJson(200, json_decode($result, TRUE));
    }

    public function makeChoice()
    {
        $questionId = intval($this->input->post('question_id'));
        $answerId = intval($this->input->post('answer_id'));
        $result = $this->thrift->service('Grade')->makeChoice($this->_uid, $questionId, $answerId);
        if($result === TRUE){
            $this->response->formatJson(200);
        }
        $this->response->formatJson(500);
    }

    public function review()
    {
        $resultId = intval($this->input->get('result_id'));
        $result = $this->thrift->service('Grade')->reviewPrepare($this->_uid, $resultId);
        if(empty($result)){
            redirect('grade/main');
        }
        $result = $this->layout->load('level_review_main', 'level_test', $result);
        $this->response->html($result);
    }

    /**
     * review回顾ajax
     */
    public function reviewCate()
    {
        $cate = $this->input->get('cate');
        $resultId = intval($this->input->get('result_id'));
        $result = $this->thrift->service('Grade')->reviewExamCate($this->_uid, $resultId, $cate);
        $this->response->formatJson(200, json_decode($result, TRUE));
//        print_r(json_decode($result, TRUE));
    }

    /*
     * 做题提交方法
     */
    public function submit()
    {
        $result = $this->thrift->service('Grade')->submitAnswer($this->_uid, [
            'source' => 1
        ]);
        if($result->code == 200){
            $data = $result->data;
            if(intval($data['is_pass']) === 1){  //合格
                $this->input->set_cookie('cookie_grade_type',$data['grade_type'],600);
            }else{
                $this->input->set_cookie('cookie_grade_type','');
            }
            $this->response->formatJson(200, $result->data);
        }
        $this->response->formatJson(500, $result->msg);
    }
    /**
     * 加载通用 选经历
     */
    public function generate(){
        $result = $this->layout->load('level_generate_main', 'level_test', ['grade_exam_name' => '选择学习经历']);
        $this->response->html($result);
    }
    /**
     *升级须知
     */
    public function notice(){
        $ret = $this->thrift->service('Grade')->getUpgradeInfo($this->_uid);
        if($ret->code === 200){
            $data['data'] = $ret->data;
            $data['data']['is_max_grade'] = 0;
            if(intval($data['data']['grade_id']) === $this->grade_model->_getMaxGrade()){
                $data['data']['is_max_grade'] = 1;
            }
            $data['grade_exam_name'] = '考前须知';
        }else{
            redirect('/grade/main');
        }
        $result = $this->layout->load('level_notice_main', 'level_test', $data);
        $this->response->html($result);
    }

    /**
     * 判断零基础
     * @return bool
     */
    public function setUserZeroBased(){
        $result = $this->thrift->service('Grade')->setUserZeroBased($this->_uid);
        if($result === TRUE){
            $this->input->set_cookie('cookie_grade_type',3,600);
            $this->response->formatJson(200);
        }
        $this->response->formatJson(500);
    }

    /**
     * 加载学习经历
     */
    public function learningExperience(){
        $data['book_info'] = $this->thrift->service('Grade')->getBookList();
        $this->response->formatJson(200, $data);
    }
    /**
     * 加载教材进度联动
     */
    public function loadBookProgress(){
        $book_id = intval($this->input->post('book_id'));
        $data['book_id'] = $book_id ? $book_id : 0;
        $data['progress_info'] = $this->thrift->service('Grade')->getProgressByBook($book_id);
        if(!empty($data['progress_info'])){
            $data['no_progress'] = 1;
        }else{
            $data['no_progress'] = 0;
        }
        $this->response->formatJson(200, $data);
    }

    /**
     * 加载用户阶段课程信息
     */
    public function getUserScheduleInfo(){
        $data['user_schedule_info'] = $this->thrift->service('Schedule')->getUserLearnSchedule($this->_uid);
        $this->response->formatJson(200, $data);
    }

    /**
     * 等级验证
     */
    public function checkGrade(){
        $book_id = intval($this->input->post('book_id'));
        $progress_id = intval($this->input->post('progress_id'));
        $data = $this->thrift->service('Grade')->getGradeByBook($book_id, $progress_id);
        $this->response->json($data);
    }

    /**
     * 测试专用
     */
    public function getQuestionIds()
    {
        $questionIds = $this->grade_model->getQuestionIds($this->_uid);
        echo implode(',', $questionIds);
    }
    /**
     * 申请调级
     */
    public function applyChangeGrade(){
        $data['grade_id'] = $this->input->post('grade_id');
        $data['reason'] = $this->input->post('reason');
        $data['source'] = 1;
        $result = $this->thrift->service('Grade')->getUserApplyGrade($this->_uid, $data);
        if($result->code === 200){
            $this->response->formatJson(200);
        }
        $this->response->formatJson(500, $result->msg);
    }

}