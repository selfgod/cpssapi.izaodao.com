<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UnitTest_model extends MY_Model
{
    /**
     * 单元测试未完成
     */
    const EXAM_NOT_FINISH = 'not_finish';
    /**
     * 没有单元测试记录
     */
    const EXAM_NO_STATUS = 'no_status';
    /**
     * 单元测试完成
     */
    const EXAM_FINISHED = 'finished';
    /**
     * 中场休息时间 10分钟
     */
    const REST_TIME_TOTAL = 600;

    const TITLE_STR = [
        'word' => '单词',
        'grammar' => '文法',
        'reading' => '读解',
        'listening' => '听解'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 单元测试
     * @param $uid
     * @param $paperId
     * @param $scheduleId
     * @return array
     */
    public function unitTestPrepare($uid, $paperId, $scheduleId)
    {
        if (!empty($scheduleId)) {
            if (($isValid = $this->validation->validate('learn_details', ['uid' => $uid, 'schedule_id' => $scheduleId])) !== TRUE) {
                return [];
            }
            $paperInfo = $this->getPaperInfo($paperId);
        } else {
            //早题库入口
            $paperInfo = $this->getPaperInfo($paperId);
            $valid = $this->validation->validate('zdt_unit_test', ['paper' => $paperInfo, 'uid' => $uid]);
            if ($valid !== TRUE) {
                if ($valid['code'] === 300) {
                    return ['over_limit' => 1, 'name' => '人数已满'];
                } else {
                    return [];
                }
            }
        }
        if (empty($paperInfo['total_num'])) {
            return [];
        }
        return $paperInfo;
    }

    /**
     * 获取单元测试试卷信息
     * @param $id
     */
    public function getPaperInfo($id)
    {
        $this->load->library('thrift');
        $paperInfo = $this->thrift->service('Exam')->getPaperInfo($id, []);

        if (empty($paperInfo)) {
            $paperInfo['word_num'] = $paperInfo['grammar_num'] = $paperInfo['reading_num'] = $paperInfo['listening_num'] = 0;
        } else {
            $paperInfo['word'] = empty($paperInfo['word']) ? [] : explode(',', $paperInfo['word']);
            $paperInfo['grammar'] = empty($paperInfo['grammar']) ? [] : explode(',', $paperInfo['grammar']);
            $paperInfo['reading'] = empty($paperInfo['reading']) ? [] : explode(',', $paperInfo['reading']);
            $paperInfo['listening'] = empty($paperInfo['listening']) ? [] : explode(',', $paperInfo['listening']);
            $paperInfo['word_num'] = count($paperInfo['word']);
            $paperInfo['grammar_num'] = count($paperInfo['grammar']);
            $paperInfo['reading_num'] = count($paperInfo['reading']);
            $paperInfo['listening_num'] = count($paperInfo['listening']);
            $paperInfo['total_time'] = $paperInfo['total_time_1'] + $paperInfo['total_time_2'];
        }
        //第一部分题数
        $paperInfo['part1_num'] = $paperInfo['word_num'] + $paperInfo['grammar_num'] + $paperInfo['reading_num'];
        //第二部分题数
        $paperInfo['part2_num'] = $paperInfo['listening_num'];
        //总题数
        $paperInfo['total_num'] = $paperInfo['part1_num'] + $paperInfo['part2_num'];
        return $paperInfo;
    }

    /**
     * 获取用户的单元测试结果
     * @param $uid
     * @param $id
     * @param bool $finished
     * @return
     */
    public function getExamResult($uid, $id, $finished = FALSE)
    {
        parent::$minor_db->select('id, start_time, word_right_num, grammar_right_num,
        reading_right_num, listening_right_num, status, score, insert_num, used_time, xuefen,
        schedule_id, schedule_lesson_id, plan_id, plan_stage_id')
            ->from('jh_zdt_exam_result')
            ->where([
                'uid' => $uid,
                'paper_id' => $id
            ]);
        if ($finished) {
            parent::$minor_db->where(['status' => 1]);
        }
        $data = parent::$minor_db->limit(1)->order_by('start_time', 'DESC')
            ->get()->row_array();
        if (!empty($data)) {
            $data['right_num'] = $data['word_right_num'] + $data['grammar_right_num'] + $data['reading_right_num'] + $data['listening_right_num'];
        }
        return $data;
    }

    /**
     * 检测用户做题状态
     * @param $uid
     * @param $paperId
     * @param bool $examResult
     * @return string
     */
    public function checkStatus($uid, $paperId, $examResult = FALSE)
    {
        if ($examResult === FALSE) {
            $examResult = $this->getExamResult($uid, $paperId);
        }
        if (empty($examResult)) {
            return self::EXAM_NO_STATUS;
        }
        if ($examResult['status'] === '0') {
            return self::EXAM_NOT_FINISH;
        } else {
            return self::EXAM_FINISHED;
        }
    }

    /**
     * 获取用户最后一次的做题日志
     * @param $uid
     * @param $paperId
     * @param $resultId
     */
    public function getLastExamLog($uid, $paperId, $resultId)
    {
        return parent::$minor_db->select('insert_time, question_id')
            ->from('jh_zdt_exam_log')
            ->where([
                'uid' => $uid,
                'paper_id' => $paperId,
                'result_id' => $resultId
            ])->limit(1)
            ->order_by('insert_time', 'DESC')
            ->get()->row_array();
    }

    /**
     * 查询该用户是否有该题的答题日志
     * @param $uid
     * @param $paperId
     * @param $resultId
     * @return
     */
    public function hasExamLog($uid, $paperId, $resultId)
    {
        return parent::$minor_db->from('jh_zdt_exam_log')
            ->where([
            'uid' => $uid,
            'paper_id' => $paperId,
            'result_id' => $resultId
        ])->count_all_results();
    }

    /**
     * 添加考试记录
     * @param $uid
     * @param $paperId
     * @param $scheduleId
     * @param $lessonId
     * @return bool
     */
    public function addResult($uid, $paperId, $scheduleId = 0, $lessonId = 0)
    {
        $paperInfo = $this->getPaperInfo($paperId);
        $examResult = $this->getExamResult($uid, $paperId);
        $insertNum = empty($examResult) ? 1 : $examResult['insert_num'] + 1;
        $data = [
            'uid' => $uid,
            'exam_topic_id' => $paperInfo['exam_topic_id'],
            'paper_id' => $paperId,
            'start_time' => time(),
            'insert_num' => $insertNum
        ];
        if (!empty($scheduleId)) {
            if (($isValid = $this->validation->validate('learn_details', ['uid' => $uid, 'schedule_id' => $scheduleId])) !== TRUE) {
                return FALSE;
            }
            /*$schedulePlan = $this->schedule_model->viewUserPlanStageJoinSchedule([
                'uid' => $uid,
                'schedule_id' => $scheduleId,
                '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')",
            ], ['plan_id', 'plan_stage_id'], [], [1]);*/
            $data['schedule_id'] = $scheduleId;
            $data['schedule_lesson_id'] = $lessonId;
            /*$data['plan_id'] = $schedulePlan['plan_id'];
            $data['plan_stage_id'] = $schedulePlan['plan_stage_id'];*/
        }
        return parent::$minor_db->insert('jh_zdt_exam_result', $data);
    }

    /**
     * 做题
     * @param $uid
     * @param $paperId
     * @return mixed
     */
    public function exam($uid, $paperId)
    {
        $data['paper'] = $this->getPaperInfo($paperId);
        if (empty($data['paper']['total_num'])) {
            return [];
        }
        $valid = $this->validation->validate('zdt_unit_test', ['paper' => $data['paper'], 'uid' => $uid]);
        if ($valid !== TRUE) {
            if ($valid['code'] === 300) {
                return ['over_limit' => 1, 'name' => '人数已满'];
            } else {
                return [];
            }
        }
        $cate = $this->initCate($data['paper']);
        $data['part2_remain_time'] = $data['part1_submit'] = $data['answer_num'] = 0;
        $data['part1_remain_time'] = $data['paper']['total_time_1'] * 60;
        foreach ($cate as $name => $val) {
            if ($val === TRUE) {
                if (!isset($data['default_cate'])) {
                    $data['default_cate'] = $name;
                }
                if ($name !== 'listening') {
                    $data['part1_last_cate'] = $name;
                }
                $data['last_cate'] = $name;
            }
        }
        $examResult = $this->getExamResult($uid, $paperId);
        if (empty($examResult)) {
            return [];
        }
        $data['exam_status'] = $examResult['status'];
        $data['cate'] = $cate;
        $data['cate_title'] = self::TITLE_STR;
        $data['notice'] = $this->examNotice($data['paper'], $cate);
        $data['name'] = $data['paper']['name'];
        $now = time();
        if ($this->checkStatus($uid, $paperId, $examResult) === self::EXAM_NOT_FINISH) {
            $paperTimer = $this->getPaperTimer($uid, $paperId, $examResult['id']);
            if (!empty($paperTimer) && $paperTimer['part_one_submit'] === '1') {
                //第一部分已经提交
                $data['part1_submit'] = 1;
                $data['default_cate'] = 'listening';
                $data['cate']['word'] = $data['cate']['grammar'] = $data['cate']['reading'] = FALSE;
                $qids = $this->getPartQuestions('two', $data['paper']);

                if ($paperTimer['part_two_start_time'] === '0') {//设置听力部分开始时间
                    $this->upsertPaperTimer($paperTimer['id'], ['part_two_start_time' => $now]);
                    $paperTimer['part_two_start_time'] = $now;
                }
                $data['part2_remain_time'] = $data['paper']['total_time_2'] * 60 - ($now - $paperTimer['part_two_start_time']);
                if ($data['part2_remain_time'] < 0) {
                    $data['part2_remain_time'] = 0;
                }
            } else {
                //第一部分的剩余时间
                $data['part1_remain_time'] = $data['paper']['total_time_1'] * 60 - ($now - $examResult['start_time']);
                $data['part1_remain_time'] = $this->deductRemainTime($data['paper'], $data['part1_remain_time'], $examResult);
                if ($data['part1_remain_time'] < 0) {
                    $data['part1_remain_time'] = 0;
                }
                $qids = $this->getPartQuestions('one', $data['paper']);
            }
            $data['answer_num'] = $this->getUserAnswerNum($uid, $paperId, $examResult['id'], $qids);
        }

        if ($data['default_cate'] === 'listening') {
            $data['part1_str'] = '';
        } else {
            $data['part1_str'] = $this->part1Str($cate);
        }

        return $data;
    }

    /**
     * 按时完成类型，上课后半个小时以后，上课后一个小时之前进入考场，扣除30分钟测试时长
     * 题库考场类型，超过开始时间，就扣除
     * @param $paper
     * @param $remainTime
     * @param $examResult
     * @return int
     */
    public function deductRemainTime($paper, $remainTime, $examResult)
    {
        $examType = $paper['exam_type'];
        if ($examType === '1' || $examType === '3') {
            $now = time();
            if ($examResult['schedule_lesson_id'] !== '0') {//课件时间
                //按时完成类型
                $lessonId = $examResult['schedule_lesson_id'];
                $this->load->model('public/lesson_model');
                $lessonInfo = $this->lesson_model->getScheduleLessonCacheData($lessonId);
                $startTime = strtotime($lessonInfo['start_time']);
                if ($now >= $startTime + 1800 && $now < $startTime + 3600) {
                    return $remainTime - 1800;
                }
            } elseif (!empty($paper['open_date_start'])) {//题库考场
                $startTime = strtotime(date('Y-m-d') . ' ' . $paper['open_time_start']);
                if ($now >= $startTime && $now < $startTime + 1800) {
                    return $remainTime - ($now - $startTime);
                }
            }
        }
        return $remainTime;
    }

    /**
     * 获取用户已经回答的题目数
     * @param $uid
     * @param $paperId
     * @param $resultId
     * @param $qids
     */
    public function getUserAnswerNum($uid, $paperId, $resultId, $qids)
    {
        return parent::$minor_db->from('jh_zdt_exam_log')
            ->where([
                'uid' => $uid,
                'paper_id' => $paperId,
                'result_id' => $resultId,
            ])
            ->where_in('question_id', $qids)
            ->count_all_results();
    }

    /**
     * 获取第一，或第二部分的题目id
     * @param $part
     * @param $paperInfo
     * @return array
     */
    public function getPartQuestions($part, $paperInfo)
    {
        if ($part === 'one') {
            $wordQids = $paperInfo['word'];
            $grammarQids = $paperInfo['grammar'];
            $readingQids = $paperInfo['reading'];
            return array_merge($wordQids, $grammarQids, $readingQids);
        } else {
            return $paperInfo['listening'];
        }
    }

    /**
     * 获取单元测试第一部分，第二部分用时
     * @param $uid
     * @param $paperId
     * @param $resultId
     */
    public function getPaperTimer($uid, $paperId, $resultId)
    {
        return parent::$minor_db->select('id, part_one_submit_time, part_two_submit_time, part_one_submit, part_two_submit,
               rest_start_time, part_two_start_time')
            ->from('jh_zdt_exam_paper_timer')
            ->where([
                'uid' => $uid,
                'paper_id' => $paperId,
                'result_id' => $resultId
            ])
            ->limit(1)->get()->row_array();
    }

    /**
     * 获取考试提醒文案
     * @param $paperInfo
     * @param $cate
     * @return string
     */
    public function examNotice($paperInfo, $cate)
    {
        $cateStr = $this->part1Str($cate);
        $notice = '本模拟试卷总考试时间 ' . $paperInfo['total_time'] . ' 分钟，共 ' . $paperInfo['total_num'] . ' 题。其中';
        if (!empty($cateStr)) {

            $notice .= $cateStr . '部分答题时间为 ' . $paperInfo['total_time_1'];
            $notice .= ' 分钟，共 ' . $paperInfo['part1_num'] . ' 题。<br>';
        }
        if ($cate['listening']) {
            $notice .= '听解部分的答题时间为 ' . $paperInfo['total_time_2'] . ' 分钟，共 ' . $paperInfo['listening_num'] . ' 题。';
        }
        $notice .= '到达答题规定时间，系统将自动交卷。';
        return $notice;
    }

    /**
     * 获取第一部分
     * @param $cate
     * @return string
     */
    protected function part1Str($cate)
    {
        $cateArr = [];
        foreach ($cate as $name => $exist) {
            if ($exist === TRUE && $name !== 'listening') {
                $cateArr[] = self::TITLE_STR[$name];
            }
        }
        return implode('、', $cateArr);
    }

    /**
     * 获取问题信息
     * @param $ids
     * @return array|bool
     */
    public function getQuestions($ids)
    {
        $this->load->driver('cache');
        $fields = ['id', 'topic_id', 'question', 'rightanswer', 'analysis', 'hearing', 'zdknow_id'];
        $data = $this->cache->redis->mGetByIds('paper_questions', $ids, function ($missKeys, $field) use ($ids) {
            $idStr = implode(',', $ids);
            $questions = parent::$minor_db->select($field)
                ->from('zd_test_question')
                ->where_in('id', $missKeys)
                ->order_by("FIELD ( id, {$idStr})", '', FALSE)
                ->get()->result_array();

            $options = $this->getQuestionOpt($ids);
            foreach ($questions as $i => $question) {
                $tmp = strip_tags(trim($question['question']), '<img><u><br><p>');
                preg_match('/<img.+(width=\"?\d*\"?).+>/i', $tmp, $match);
                if (!empty($match) && (trim($match[1]) > 'width="666"' || trim($match[1]) > "width='666'")) {
                    $tmp = str_replace(trim($match[1]), ' width=666 ', $tmp);
                }
                //图片域名修改为jp站
                $tmp = preg_replace('/(<img.+src=\"?.+)(\/zdschool\/)(.+\.(jpg|png)\"?.+>)/i',"\${1}". RESOURCE_HOST_JP . "zdschool/\${3}", $tmp);
                $questions[$i]['question'] = $tmp;
                $questions[$i]['hearing'] = preg_replace('/(<img src=\"?.+)(\/zdschool\/)(.+\.(jpg|png)\"?.+>)/i',"\${1}". RESOURCE_HOST_JP . "zdschool/\${3}", $question['hearing']);
                $questions[$i]['option'] = $options[$question['id']]['options'];
            }
            return $questions;

        }, $fields);

        return $data;
    }

    /**
     * 获取专题信息
     * @param $ids
     * @return array
     */
    public function getTopics($ids)
    {
        $ids = array_unique($ids);
        $topics = parent::$minor_db->select('id, topic, mediafile, poly_id')
            ->from('zd_test_topic')
            ->where_in('id', $ids)
            ->get()->result_array();
        $data = [];
        $media_file = $topics[0]['mediafile'];
        $poly_id = $topics[0]['poly_id'];
        foreach ($topics as $i => $topic) {
            $tmp = trim(strip_tags($topic['topic'], '<img><u><br><p>'));
            $tmp = str_replace(['听力原文下载', '听力原文链接'], '', $tmp);
            //图片域名修改为jp站
            $tmp = preg_replace('/(<img.+src=\"?.+)(\/zdschool\/)(.+\.(jpg|png)\"?.+>)/i',"\${1}". RESOURCE_HOST_JP . "zdschool/\${3}", $tmp);
            $data[$topic['id']]['topic'] = $tmp;
        }
        return ['data' => $data, 'media_file' => $media_file, 'poly_id' => $poly_id];
    }

    /**
     * 获取问题的选项
     * @param $ids
     * @return array
     */
    public function getQuestionOpt($ids)
    {
        $this->load->driver('cache');
        $fields = ['wa_id', 'wa_answer', 'wa_question_id'];
        $options = $this->cache->redis->mGetByIds('question_opts', $ids, function ($missKeys, $field) {
            $options = parent::$minor_db->select($field)
                ->from('zd_test_answer')
                ->where_in('wa_question_id', $missKeys)
                ->order_by('wa_id', 'ASC')
                ->get()->result_array();
            $data = [];
            foreach ($options as $i => $option) {
                $data[$option['wa_question_id']]['wa_question_id'] = $option['wa_question_id'];
                $data[$option['wa_question_id']]['options'][] = [
                    'wa_id' => $option['wa_id'],
                    'wa_answer' => trim(strip_tags($option['wa_answer'], '<u>'))
                ];
            }
            return $data;
        }, $fields, 'wa_question_id');
        return $options;
    }

    /**
     * 获取某个分类下的问题内容
     * @param $uid
     * @param $paperId
     * @param $cate string word|grammar|reading|listening
     * @return array
     */
    public function getExamCate($uid, $paperId, $cate)
    {
        $paperInfo = $this->getPaperInfo($paperId);
        if (!empty($paperInfo[$cate])) {
            $questionsArr = $paperInfo[$cate];
        } else {
            return [];
        }
        $questions = $this->getQuestions($questionsArr);
        $topicIds = [];
        foreach ($questions as $question) {
            $topicIds[] = $question['topic_id'];
        }

        $topicInfo = $this->getTopics($topicIds);
        $result = $this->getExamResult($uid, $paperId);
        $userExamLog = $this->getUserExamCateLog($uid, $paperId, $result['id'], $questionsArr);
        return $this->genSubjectList(FALSE, $questions, $topicInfo, $userExamLog);
    }

    /**
     * 获取用户的答题记录
     * @param $uid
     * @param $paperId
     * @param $resultId
     * @param $qids
     * @return array
     */
    public function getUserExamCateLog($uid, $paperId, $resultId, $qids = [])
    {
        parent::$minor_db->select('question_id, answer_id, status')
            ->from('jh_zdt_exam_log')
            ->where([
                'uid' => $uid,
                'paper_id' => $paperId,
                'result_id' => $resultId
            ]);

        if (!empty($qids)) {
            parent::$minor_db->where_in('question_id', $qids);
        }

        $userExamLog = parent::$minor_db->get()->result_array();
        $data = [];
        foreach ($userExamLog as $log) {
            $data[$log['question_id']] = ['answer_id' => $log['answer_id'], 'status' => $log['status']];
        }
        return $data;
    }

    /**
     * 生成题目列表数据
     * @param $isReview
     * @param $questions
     * @param $topics
     * @param $userExamLog
     * @param array $fallibleOpts
     * @param array $accuracyQues
     * @return array
     */
    protected function genSubjectList($isReview, $questions, $topics, $userExamLog, $fallibleOpts = [], $accuracyQues = [])
    {
        $subject = $question_card = [];
        $i = $n = 0;
        foreach ($questions as $question) {
            $i++;
            $subject[$question['topic_id']]['tid'] = $question['topic_id'];
            $subject[$question['topic_id']]['title'] = $topics['data'][$question['topic_id']]['topic'];
            $temp = [
                'index' => $i,
                'question_id' => $question['id'],
                'question' => $question['question'],
                'option' => $question['option'],
                'answer' => isset($userExamLog[$question['id']]) ? $userExamLog[$question['id']]['answer_id'] : 0
            ];
            $cardTemp = [
                'index' => $i,
                'id' => intval($question['id']),
                'answer' => isset($userExamLog[$question['id']])
            ];
            if ($isReview) {//回顾
                $temp['fallible_opt'] = empty($fallibleOpts[$question['id']]['answer_id']) ? '无' : $fallibleOpts[$question['id']]['answer_id'];
                $temp['accuracy'] = $accuracyQues[$question['id']];
                $temp['analysis'] = $question['analysis'];
                $temp['hearing'] = $question['hearing'];
                $temp['zdknow_id'] = $question['zdknow_id'];
                foreach ($question['option'] as $oi => $option) {
                    if ($question['rightanswer'] === $option['wa_id']) {
                        $temp['right_index'] = $oi + 1;
                    }
                    if ($option['wa_id'] === $temp['answer']) {
                        $temp['answer_index'] = $oi + 1;
                    }
                    if ($option['wa_id'] === $temp['fallible_opt']) {
                        $temp['fallible_opt'] = $oi + 1;
                    }
                }
                $temp['right'] = $question['rightanswer'];
                $cardTemp['status'] = isset($userExamLog[$question['id']]) ? $userExamLog[$question['id']]['status'] : 0;
            }
            $subject[$question['topic_id']]['questions'][] = $temp;
            $question_card[$n][] = $cardTemp;
            if ($i % 6 === 0) {
                $n++;
            }
        }

        return [
            'subject' => array_values($subject),
            'question_card' => $question_card,
            'media_file' => $topics['media_file'],
            'poly_id' => $topics['poly_id'],
//            'answer' => $isReview ? $userExamLog : array_keys($userExamLog)
        ];
    }

    /**
     * 记录选择答案
     * @param $uid
     * @param $paperId
     * @param $questionId
     * @param $answerId
     * @return bool
     */
    public function makeChoice($uid, $paperId, $questionId, $answerId)
    {
        $examResult = $this->getExamResult($uid, $paperId);
        if (empty($examResult)) {
            return FALSE;
        }
        $rightId = $this->getRightAnswer($questionId);
        $right = $rightId === intval($answerId);
        return $this->upsertAnswerLog($uid, $paperId, $questionId, $examResult['id'], $answerId, $right);
    }

    /**
     * 获取某道题的正确答案
     * @param $questionId
     * @return int
     */
    public function getRightAnswer($questionId)
    {
        $question = parent::$minor_db->select('rightanswer')
            ->from('zd_test_question')
            ->where([
                'id' => $questionId
            ])->get()->row_array();
        if (empty($question)) {
            return 0;
        } else {
            return intval($question['rightanswer']);
        }
    }

    /**
     * 添加或更新选择答案
     * @param $uid
     * @param $paperId
     * @param $questionId
     * @param $resultId
     * @param $answerId
     * @param $right bool 是否是正确答案
     */
    public function upsertAnswerLog($uid, $paperId, $questionId, $resultId, $answerId, $right)
    {
        $exist = parent::$minor_db->select('id')
            ->from('jh_zdt_exam_log')
            ->where([
                'uid' => $uid,
                'result_id' => $resultId,
                'paper_id' => $paperId,
                'question_id' => $questionId
            ])->limit(1)->get()->row_array();

        if (!empty($exist)) {
            return parent::$minor_db->where(['id' => $exist['id']])
                ->update('jh_zdt_exam_log', [
                    'answer_id' => $answerId,
                    'status' => $right ? 1 : 0,
                    'insert_time' => time()
                    ]);
        } else {
            return parent::$minor_db->insert('jh_zdt_exam_log', [
                'uid' => $uid,
                'result_id' => $resultId,
                'paper_id' => $paperId,
                'question_id' => $questionId,
                'answer_id' => $answerId,
                'status' => $right ? 1 : 0,
                'insert_time' => time()
            ]);
        }
    }

    /**
     * 纠错
     * @param $uid
     * @param $paperId
     * @param $questionId
     * @param $desc
     */
    public function answerCorrection($uid, $paperId, $questionId, $desc)
    {
        parent::$minor_db->insert('jh_zdt_error_correction', [
            'uid' => $uid,
            'paper_id' => $paperId,
            'question_id' => $questionId,
            'correction' => $desc,
            'correction_time' => time()
        ]);
    }

    /**
     * 获取题目的分数
     * @param $paperId
     * @param $qids
     * @return array
     */
    public function getQuestionScore($paperId, $qids = [])
    {
         parent::$minor_db->select('question_id, score')
            ->from('jh_zdt_exam_paper_question_score')
            ->where([
                'paper_id' => $paperId
            ]);
        if (!empty($qids)) {
            parent::$minor_db->where_in('question_id', $qids);
        }
        $questionScore = parent::$minor_db->get()->result_array();
        $data = [];
        foreach ($questionScore as $item) {
            $data[$item['question_id']] = intval($item['score']);
        }
        return $data;
    }

    /**
     * 更新考试结果
     * @param $id
     * @param $data
     */
    public function updateExamResult($id, $data)
    {
        return parent::$minor_db->where(['id' => $id])
            ->update('jh_zdt_exam_result', $data);
    }

    /**
     * 更新或插入paper timer
     * id传0是插入
     * @param $id
     * @param $data
     */
    public function upsertPaperTimer($id, $data)
    {
        if ($id === 0) {
            return parent::$minor_db->insert('jh_zdt_exam_paper_timer', $data);
        } else {
            return parent::$minor_db->where(['id' => $id])
                ->update('jh_zdt_exam_paper_timer', $data);
        }
    }

    /**
     * 交卷
     * @param $uid
     * @param $paperId
     * @param $part string one|two
     * @return bool
     */
    public function submit($uid, $paperId, $part)
    {
        $examResult = $this->getExamResult($uid, $paperId);
        if (empty($examResult) || $examResult['status'] === '1') {
            return FALSE;
        }
        $paperTimer = $this->getPaperTimer($uid, $paperId, $examResult['id']);
        if ($part === 'one' && !empty($paperTimer)) {
            return FALSE;
        }
        if ($part === 'two' && !empty($paperTimer) && $paperTimer['part_two_submit'] === '1') {
            return FALSE;
        }
        $paperInfo = $this->getPaperInfo($paperId);
        $qids = $this->getPartQuestions($part, $paperInfo);
        $questionScore = $this->getQuestionScore($paperId, $qids);
        $examLog = $this->getUserExamCateLog($uid, $paperId, $examResult['id'], $qids);
        $wordRightNum = $grammarRightNum = $readingRightNum = $listeningRightNum = $score = 0;
        foreach ($examLog as $qid => $log) {
            if ($log['status'] === '1') {
                $score += $questionScore[$qid];
                if ($part === 'one') {
                    if (in_array($qid, $paperInfo['word'])) {
                        $wordRightNum++;
                    } elseif (in_array($qid, $paperInfo['grammar'])) {
                        $grammarRightNum++;
                    } elseif (in_array($qid, $paperInfo['reading'])) {
                        $readingRightNum++;
                    }
                } else {
                    $listeningRightNum++;
                }

            }

        }
        parent::$minor_db->trans_start();
        $now = time();
        if ($part === 'one') {//第一部分提交
            $resultData = [
                'word_right_num' => $wordRightNum,
                'grammar_right_num' => $grammarRightNum,
                'reading_right_num' => $readingRightNum,
                'score' => $score
            ];
            $part1Time = $now - intval($examResult['start_time']);
            if ($part1Time > intval($paperInfo['total_time_1']) * 60) {
                $part1Time = intval($paperInfo['total_time_1']) * 60;
            }
            $timerData = [
                'uid' => $uid,
                'paper_id' => $paperId,
                'result_id' => $examResult['id'],
                'part_one_time' => $part1Time,
                'used_time' => $part1Time,
                'part_one_submit' => 1,
                'part_one_submit_time' => $now
            ];
            if (empty($paperInfo['part2_num'])) {
                //没有听力题
                $resultData['status'] = 1;
            }
            $this->upsertPaperTimer(0, $timerData);
            $resultData['used_time'] = $part1Time;
        } else {
            $resultData = [
                'listening_right_num' => $listeningRightNum,
                'score' => intval($examResult['score']) + $score,
                'status' => 1
            ];
            if (empty($paperInfo['part1_num'])) {
                //只有听力题
                $totalTime = $now - intval($examResult['start_time']);
                if ($totalTime > intval($paperInfo['total_time']) * 60) {
                    $totalTime = intval($paperInfo['total_time']) * 60;
                }
                $this->upsertPaperTimer(0, [
                    'uid' => $uid,
                    'paper_id' => $paperId,
                    'result_id' => $examResult['id'],
                    'part_two_time' => $totalTime,
                    'used_time' => $totalTime,
                    'part_two_submit' => 1,
                    'part_two_submit_time' => $now
                ]);
                $resultData['used_time'] = $totalTime;
            } else {
                $part2Time = $now - intval($paperTimer['part_two_start_time']);
                if ($part2Time > intval($paperInfo['total_time_2']) * 60) {
                    $part2Time = intval($paperInfo['total_time_2']) * 60;
                }
                $this->upsertPaperTimer($paperTimer['id'], [
                    'part_two_time' => $part2Time,
                    'used_time' => $part2Time + $examResult['used_time'],
                    'part_two_submit' => 1,
                    'part_two_submit_time' => $now
                ]);
                $resultData['used_time'] = $part2Time + $examResult['used_time'];
            }

        }
        $resultData['end_time'] = $now;
        $this->updateExamResult($examResult['id'], $resultData);
        parent::$minor_db->trans_complete();
        return parent::$minor_db->trans_status();
    }

    /**
     * 单元测试中场休息页
     * @param $uid
     * @param $id
     * @return mixed
     */
    public function rest($uid, $id)
    {
        $paperInfo = $this->getPaperInfo($id);
        if (empty($paperInfo['total_num'])) {
            return [];
        }
        $examResult = $this->getExamResult($uid, $id);
        if (empty($examResult) || $examResult['status'] === '1') {//已经完成考试
            return [];
        }
        $paperTimer = $this->getPaperTimer($uid, $id, $examResult['id']);
        if (empty($paperTimer) || $paperTimer['part_one_submit'] === '0') {//第一部分没交卷
            return [];
        }
        $now = time();
        if ($paperTimer['rest_start_time'] === '0') {
            $this->upsertPaperTimer($paperTimer['id'], ['rest_start_time' => $now]);
            $paperTimer['rest_start_time'] = $now;
        }

        $cate = $this->initCate($paperInfo);
        $data['notice'] = $this->examNotice($paperInfo, $cate);
        $data['paper'] = $paperInfo;
        $data['name'] = $data['paper']['name'];
        $data['remain'] = self::REST_TIME_TOTAL - ($now - $paperTimer['rest_start_time']);
        if ($data['remain'] <= 0) {
            return [];
        }
        return $data;
    }

    /**
     * 单元测试回顾页
     * @param $uid
     * @param $id
     * @return array
     */
    public function review($uid, $id)
    {
        $paperInfo = $this->getPaperInfo($id);
        if (empty($paperInfo['total_num'])) {
            return [];
        }
        $defaultCate = 'word';
        $cate = $this->initCate($paperInfo);
        foreach ($cate as $name => $val) {
            if ($val === TRUE) {
                $defaultCate = $name;
                break;
            }
        }
        $examResult = $this->getExamResult($uid, $id, TRUE);
        if (empty($examResult)) {
            return [];
        }
        $data['score'] = $examResult['score'];
        $data['total_score'] = $paperInfo['score'];
        $data['name'] = $paperInfo['name'];
        $data['cate'] = $cate;
        $data['cate_title'] = self::TITLE_STR;
        $data['default_cate'] = $defaultCate;
        $data['notice'] = $this->examNotice($paperInfo, $cate);
        $data['paper'] = $paperInfo;
        return $data;
    }

    /**
     * 获取回顾页的分类信息
     * @param $uid
     * @param $id
     * @param $cate
     * @return array
     */
    public function reviewCate($uid, $id, $cate)
    {
        $paperInfo = $this->getPaperInfo($id);
        if (!empty($paperInfo[$cate])) {
            $questionsArr = $paperInfo[$cate];
        } else {
            return [];
        }
        $examResult = $this->getExamResult($uid, $id, TRUE);
        if (empty($examResult)) {
            return [];
        }
        $questions = $this->getQuestions($questionsArr);
        $topicIds = [];
        foreach ($questions as $question) {
            $topicIds[] = $question['topic_id'];
        }

        $topicInfo = $this->getTopics($topicIds);
        $userExamLog = $this->getUserExamCateLog($uid, $id, $examResult['id'], $questionsArr);
        $fallibleOpt = $this->getFallibleOpt($id, $questionsArr);
        $accuracyQues = $this->getAccuracy($id, $questionsArr);
        return $this->genSubjectList(TRUE, $questions, $topicInfo, $userExamLog, $fallibleOpt, $accuracyQues);
    }

    /**
     * 获取题目的易错选项
     * @param $paperId
     * @param $qids
     * @return array
     */
    public function getFallibleOpt($paperId, $qids)
    {
        $result = parent::$minor_db->select('question_id, answer_id, count(*) as cnt')
            ->from('jh_zdt_exam_log')
            ->where(['paper_id' => $paperId, 'status' => 0])
            ->where_in('question_id', $qids)
            ->group_by('answer_id')
            ->get()->result_array();
        $data = [];
        foreach ($qids as $qid) {
            $data[$qid] = ['answer_id' => 0, 'cnt' => 0];
        }
        foreach ($result as $item) {
            if ($item['cnt'] > $data[$item['question_id']]['cnt']) {
                $data[$item['question_id']]['answer_id'] = $item['answer_id'];
                $data[$item['question_id']]['cnt'] = intval($item['cnt']);
            }
        }
        return $data;
    }

    /**
     * 获取题目正确率
     * @param $paperId
     * @param $qids
     * @return array
     */
    public function getAccuracy($paperId, $qids)
    {
        $total = parent::$minor_db->select('question_id, count(*) as total_num')
            ->from('jh_zdt_exam_log')
            ->where(['paper_id' => $paperId])
            ->where_in('question_id', $qids)
            ->group_by('question_id')
            ->get()->result_array();

        $right = parent::$minor_db->select('question_id, count(*) as right_num')
            ->from('jh_zdt_exam_log')
            ->where(['paper_id' => $paperId, 'status' => 1])
            ->where_in('question_id', $qids)
            ->group_by('question_id')
            ->get()->result_array();
        $rightRet = $result = [];
        foreach ($qids as $qid) {
            $result[$qid] = 0;
        }
        foreach ($right as $que) {
            $rightRet[$que['question_id']] = intval($que['right_num']);
        }
        foreach ($total as $que) {
            if (isset($rightRet[$que['question_id']])) {
                $result[$que['question_id']] = (round(($rightRet[$que['question_id']] / intval($que['total_num'])), 2)) * 100;
            }
        }
        return $result;
    }

    /**
     * 初始化分类信息
     * @param $paperInfo
     * @return mixed
     */
    protected function initCate($paperInfo)
    {
        $cate['word'] = $paperInfo['word_num'] > 0 ? TRUE : FALSE;
        $cate['grammar'] = $paperInfo['grammar_num'] > 0 ? TRUE : FALSE;
        $cate['reading'] = $paperInfo['reading_num'] > 0 ? TRUE : FALSE;
        $cate['listening'] = $paperInfo['listening_num'] > 0 ? TRUE : FALSE;
        return $cate;
    }
}
