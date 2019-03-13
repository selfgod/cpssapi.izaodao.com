<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exercise_model extends MY_Model
{
    public $unitFinishType;

    /**
     * 获取一课一练, 单元测试列表
     * @param $schedule_id
     * @param $uid
     * @param $pageCount
     * @param int $pageNum
     * @param int $statusType 默认全部|1 已完成|2 未完成
     * @param string $exerciseType test | unit
     * @param $plan 计划id
     * @param $planStage 计划阶段id
     * @return array
     */
    public function getExerciseList($schedule_id, $uid, $pageCount, $pageNum = 1,
                                    $statusType = 0, $exerciseType = 'test', $plan, $planStage)
    {
        $offset = ($pageNum - 1) * $pageCount;
        $uidJoinStr = sprintf('susls.uid = %d', $uid);
        $type = $exerciseType === 'test' ? 1 : 2;
        $id = $exerciseType . '_id';
        $now = date('Y-m-d 23:59:59');

        $where = array(
            'sl.schedule_id' => $schedule_id,
            "sl.$id >" => 0,
            'sl.is_del' => 0
        );
        $this->load->model('public/schedule_model');
        $isRecord = $this->schedule_model->isRecordSchedule($schedule_id);

        parent::$main_db->select("sl.$id, sl.id AS schedule_lesson_id, sl.start_time, sl.alias, sl.name,
                susls.score, susls.schedule_lesson_id as f_lesson_id, susls.schedule_id as f_schedule_id,
                susls.plan_id as f_plan_id, susls.plan_stage_id as f_plan_stage_id, susls.finish_time")
            ->join('sty_user_schedule_lesson_score susls',
                "{$uidJoinStr} AND susls.type = {$type} AND susls.sub_id = sl.{$id} AND susls.is_del = 0",
                'left')
            ->where($where);
        if (!$isRecord) {
            //直播阶段课程统计开始时间小于当天24点的
            parent::$main_db->where('sl.start_time <', $now);
            parent::$main_db->where('sl.start_time >', '1000-01-01 00:00:00');
        }

        if ($statusType === 1) {//已完成
            parent::$main_db->where('susls.score >=', 0);
        } elseif ($statusType === 2) {//未完成
            parent::$main_db->where('susls.score is NULL');
        }
        $totalCount = parent::$main_db->count_all_results('sty_schedule_lesson sl', FALSE);

        if ($isRecord) {//录播阶段课程没有开始时间
            parent::$main_db->order_by('sl.order', 'ASC');
        } else {
            parent::$main_db->order_by('sl.start_time', 'DESC');
        }
        $query = parent::$main_db->limit($pageCount, $offset)->get();
        $results = [];
        foreach ($query->result_array() as $exercise) {
            if ($exerciseType === 'test') {
                $userExerInfo = $this->getUserPracticeInfo($exercise[$id], $uid);
            } else {
                $userExerInfo = $this->getUserUnitTestInfo($exercise[$id], $uid);
                if (isset($userExerInfo['already_did'])) {
                    $exercise['unit_result_id'] = $userExerInfo['result_id'];
                }
            }
            if (!empty($userExerInfo)) {
                //有alias的话显示alias, 否则显示name
                if (!empty($exercise['alias'])) {
                    $exercise['name'] = $exercise['alias'];
                }
                unset($exercise['alias']);
                if ($exercise['score'] !== null) {//已完成
                    //是否是在同一个阶段课程下完成的
                    if ($schedule_id === intval($exercise['f_schedule_id'])) {
                        $exercise['current_finish'] = TRUE;
                    }

                    $exercise['status'] = '1';
                    $exercise['finish_date'] = date('Y/m/d', strtotime($exercise['finish_time']));
                } else {
                    $exercise['status'] = '0';
                    $current = time();
                    if ($exerciseType == 'unit') {
                        $exercise['ontime_unit'] = $this->isOnTimeUnitTest(null, $userExerInfo['exam_type'], FALSE);
                        if ($exercise['ontime_unit']) {
                            //按时完成类型
                            if ($current < strtotime($exercise['start_time']) + 3600) {
                                $exercise['score'] = 5;
                            } else {
                                $exercise['score'] = 3;
                            }
                            if (!$isRecord && $current < strtotime($exercise['start_time']) - 1800) {
                                $exercise['ontime_unit_notstart'] = TRUE;
                            }
                            //按时完成类型单元测试超过30min进入考场提示
                            if (!$isRecord && $current > strtotime($exercise['start_time']) + 1800 &&
                                $current < strtotime($exercise['start_time']) + 3600) {
                                $exercise['over_30min'] = TRUE;
                            }
                        } else {
                            //随到随考类型
                            if (isset($userExerInfo['already_did'])) {
                                $exercise['score'] = 3;
                            } else {
                                $exercise['score'] = 5;
                            }
                        }
                    } else {
                        $exercise['score'] = 3;
                    }
                }

                if (!$isRecord) {
                    $exercise['start_time'] = strtotime($exercise['start_time']);
                }
                $exercise['item_count'] = $userExerInfo['total_count'];
                $exercise['total_time'] = $userExerInfo['total_time'];
                if (isset($userExerInfo['already_did'])) {
                    $exercise['last_time'] = $userExerInfo['time_diff'];
                }
                $results['data'][] = $exercise;
            } else {
                //test_id 或 unit_id错误
                if ($totalCount > 0) {
                    $totalCount--;
                }
            }

        }
        if (!empty($results)) {
            $results['total_count'] = $totalCount;
            $results['total_page'] = intval(ceil($totalCount / $pageCount));
            $results['page_limit'] = $pageCount;
            $results['current_page'] = $pageNum;
            $results['type'] = $exerciseType;
            $results['current'] = time();
            $results['is_record'] = $isRecord;
        }
        return $results;
    }

    /**
     * 获取做题任务通知状态(未完成)
     * @param $schedule_id
     * @param $uid
     * @param string $exerciseType
     * @return mixed
     */
    public function latestExercise($schedule_id, $uid, $exerciseType = 'test')
    {
        $uidJoinStr = sprintf('susl.uid = %d', $uid);
        $id = $exerciseType . '_id';

        $where = array(
            'sl.schedule_id' => $schedule_id,
            "sl.$id >" => 0,
            'sl.is_del' => 0,
            'sl.start_time <' => date('Y-m-d 23:59:59')
        );

        $query = parent::$main_db->select("sl.$id, sl.start_time")
            ->from('sty_schedule_lesson sl')
            ->join('sty_user_schedule_lesson susl',
                $uidJoinStr . ' AND susl.schedule_lesson_id = sl.id AND susl.is_del = 0',
                'left')
            ->where($where)
            ->where("(susl.{$exerciseType}_status is NULL OR susl.{$exerciseType}_status = 0)")
            ->order_by('sl.start_time', 'DESC')
            ->limit(1)
            ->get();
        return $query->row_array();
    }

    /**
     * 格式化一课一练，单元测试信息
     * @param $exercise
     * @param $type string test 一课一练|unit 单元测试
     * @return mixed
     */
    public function formatExerciseInfo(&$exercise, $type)
    {
        if ($type === 'test') {
            //一课一练
            $exercise['total_count'] = $this->getPracticeCount($exercise['qids']);
            $exercise['total_time'] = ceil($exercise['total_count'] / 2);

            //计算完成率，做题时间
            if (isset($exercise['usetime']) && $exercise['usetime'] > 0) {
                $exercise['already_did'] = true;
                $right_num = intval($exercise['rightnum']);
                $first_right_num = intval($exercise['first_rightnum']);
                $exercise['right_num'] = $right_num > $first_right_num ? $right_num : $first_right_num;
                $exercise['right_rate'] = floor($exercise['right_num'] / $exercise['total_count'] * 100);

                $this->load->helper('date');
                $diffStr = human_time_diff($exercise['result_time'], time());
                $exercise['time_diff'] = $diffStr;
            }
        } else {
            //单元测试
            $wordCount = count(explode(',', $exercise['word']));
            $grammarCount = count(explode(',', $exercise['grammar']));
            $readingCount = count(explode(',', $exercise['reading']));
            $listeningCount = count(explode(',', $exercise['listening']));
            $exercise['total_count'] = $wordCount + $grammarCount + $readingCount + $listeningCount;

            if (isset($exercise['insert_num']) && $exercise['insert_num'] > 0) {
                if ($exercise['insert_num'] == 1) {
                    $exercise['first_time'] = true;
                }
                $exercise['already_did'] = true;
                $this->load->helper('date');
                $diffStr = human_time_diff($exercise['start_time'], time());
                $exercise['time_diff'] = $diffStr;
            }
        }

        return $exercise;
    }

    /**
     * 获取用户一课一练信息
     * @param $id
     * @param $uid
     * @param bool $getCompletion 是否获取完成状态
     * @return array 测试题数
     */
    public function getUserPracticeInfo($id, $uid, $getCompletion = FALSE)
    {
        $joinStr = sprintf('ztr.uid = %d', $uid);
        $practiceInfo = parent::$minor_db->select('ztu.qids, ztu.name, ztr.rightnum, ztr.first_rightnum, ztr.result_time, ztr.usetime')
            ->from('zd_testplan_unit ztu')
            ->join('zd_testplan_results ztr', 'ztu.id = ztr.tid AND ztr.usetime > 0 AND ' . $joinStr, 'left')
            ->where(array(
                'ztu.id' => $id,
            ))
            ->limit(1)
            ->get()->row_array();

        if ($getCompletion) {
            if ($completion = $this->getUserExerStatus($uid, $id, 1)) {
                $practiceInfo = array_merge($practiceInfo, $completion);
            }
        }

        if ($practiceInfo) {
            $this->formatExerciseInfo($practiceInfo, 'test');
        }
        return $practiceInfo;
    }

    /**
     * 获取用户单元测试信息
     * @param $id
     * @param $uid
     * @param bool $getCompletion 是否获取完成状态
     * @return array 总题数， 总时间
     */
    public function getUserUnitTestInfo($id, $uid, $getCompletion = FALSE)
    {
        $joinStr = sprintf('zder.uid = %d', $uid);
        $unitTestInfo = parent::$minor_db->select('zdtp.name, zdtp.word, zdtp.grammar,
                zdtp.reading, zdtp.listening, zdtp.total_time, zdtp.exam_type,
                zder.start_time, zder.insert_num, zder.id as result_id')
            ->from('jh_zdt_exam_topic_paper zdtp')
            ->join('jh_zdt_exam_result zder',
                'zdtp.id = zder.paper_id AND zder.status = 1 AND ' . $joinStr, 'left')
            ->where(array(
                'zdtp.id' => $id
            ))
            ->limit(1)
            ->order_by('zder.id', 'DESC')
            ->get()->row_array();

        if ($getCompletion) {
            if ($completion = $this->getUserExerStatus($uid, $id, 2)) {
                $unitTestInfo = array_merge($unitTestInfo, $completion);
            }
        }

        if ($unitTestInfo) {
            $this->formatExerciseInfo($unitTestInfo, 'unit');
        }
        return $unitTestInfo;
    }

    /**
     * 获取用户一课一练，单元测试完成情况
     * @param $uid
     * @param $id
     * @param $type 1一课一练 2单元测试
     * @return array
     */
    public function getUserExerStatus($uid, $id, $type)
    {
        if ($type !== 1 && $type !== 2) {
            return array();
        }
        return parent::$main_db->select('score, finish_time, schedule_id, plan_id, plan_stage_id')
            ->from('sty_user_schedule_lesson_score')
            ->where(array(
                'uid' => $uid,
                'type' => $type,
                'sub_id' => $id,
                'is_del' => 0
            ))->limit(1)->get()->row_array();

    }

    /**
     * 获取单元测试基本信息
     * @param $id
     * @return bool
     */
    public function getUnitTestInfo($id)
    {
        $query = parent::$minor_db->select('word, grammar,
                reading, listening, total_time, exam_type')
            ->from('jh_zdt_exam_topic_paper')
            ->where(array(
                'id' => $id
            ))
            ->limit(1)
            ->get();
        if ($unitTestInfo = $query->row_array()) {
            return $unitTestInfo;
        }
        return FALSE;
    }

    /**
     * 获取一课一练基本信息
     * @param $id
     * @return mixed
     */
    public function getPracticeInfo($id)
    {
        $query = parent::$minor_db->select('name, qids')
            ->from('zd_testplan_unit')
            ->where(array(
                'id' => $id
            ))
            ->limit(1)
            ->get();
        $result = $query->row_array();
        if ($result) {
            $result['count'] = $this->getPracticeCount($result['qids']);
        }
        return $result;
    }

    /**
     * 获取一课一练题数
     * @param $items
     * @return int
     */
    public function getPracticeCount($items)
    {
        return count(explode(',', $items));
    }

    /**
     * 单元测试是否为按时完成类型
     * @param null $id
     * @param null $type
     * @param bool $forceQuery
     * @return bool
     */
    public function isOnTimeUnitTest($id = null, $type = null, $forceQuery = TRUE)
    {
        if ($forceQuery === TRUE) {
            $unitTest = $this->getUnitTestInfo($id);
            if ($unitTest && intval($unitTest['exam_type']) !== 2) {
                return TRUE;
            }
        } elseif (intval($type) !== 2) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取报到sql字段
     * @param $type
     * @return array|bool
     */
    public function getExerciseFields($type)
    {
        if ($type === 'test') {
            //一课一练
            return array(
                'insert' => array(
                    'uid', 'schedule_id', 'schedule_lesson_id', 'category',
                    'plan_id', 'plan_stage_id', 'opt_type', 'class_mode', 'test_status',
                    'test_time', 'test_score', 'modify_time', 'is_del'
                ),
                'update' => array(
                    'test_status', 'test_time', 'test_score', 'modify_time'
                )
            );
        } elseif ($type === 'unit') {
            //单元测试
            return array(
                'insert' => array(
                    'uid', 'schedule_id', 'schedule_lesson_id', 'category',
                    'plan_id', 'plan_stage_id', 'opt_type', 'class_mode', 'unit_status',
                    'unit_time', 'unit_type', 'unit_score', 'modify_time', 'is_del'
                ),
                'update' => array(
                    'unit_status', 'unit_time', 'unit_type', 'unit_score',
                    'modify_time'
                )
            );
        }
        return FALSE;
    }

    /**
     * 提交一课一练结果
     * @param $params
     * 'uid', 'schedule_id', 'schedule_lesson_id'
     * 'plan_id', 'plan_stage_id', 'opt_type', 'class_mode',
     *
     * @return mixed
     */
    public function submitPractice($params)
    {
        $params['modify_time'] = $params['test_time'] = date('Y-m-d H:i:s');
        $params['category'] = 1;//主修
        $params['test_status'] = 1;
        $params['is_del'] = 0;
        $params['test_score'] = $this->calPracticeScore($params['uid'], $params['test_id']);
        $result['score'] = $params['test_score'];
        if ($result['score'] === 0) {
            return $result;
        }
        $scheduleInfo = $this->getScheduleVal($params['schedule_id']);
        //阶段课程是否设置奖励学分
        if (intval($scheduleInfo['credit_type']) === 0) {
            $result['no_reward'] = 1;//不奖励学分
            $params['test_score'] = 0;
        }
        $params['class_mode'] = $scheduleInfo['class_mode'];
        $practiceFields = $this->getExerciseFields('test');
        $this->load->model('public/schedule_model');
        $this->load->model('public/lesson_model');
        //当前学分上限判断
        $this->load->model('public/member_model');
        if (!isset($result['no_reward'])) {
            if ($this->member_model->overScoreLimit($params['uid'])) {
                $params['test_score'] = 0;
                $result['over_limit'] = 1;
            }
        }
        parent::$main_db->trans_start();
        $upsertResult = $this->lesson_model->upsertScheduleLesson(
            $practiceFields['insert'],
            $practiceFields['update'], $params);
        if ($upsertResult) {
            $scoreData = array(
                'uid' => $params['uid'],
                'score_type' => 1,
                'sub_id' => $params['test_id'],
                'score' => $params['test_score'],
                'schedule_id' => $params['schedule_id'],
                'schedule_lesson_id' => $params['schedule_lesson_id'],
                'plan_id' => $params['plan_id'],
                'plan_stage_id' => $params['plan_stage_id'],
                'finish_time' => $params['test_time'],
                'is_del' => 0
            );
            $result['score'] = $scoreData['score'];
            if ($scoreData['score'] === 0) {
                $this->lesson_model->addCompleteStatusLog($scoreData);
            } else {
                $this->member_model->incUserScore($scoreData);
            }
            //新增用户行为
            parent::$main_db->insert('sty_user_schedule_action', [
                'uid' => $params['uid'],
                'schedule_id' => $params['schedule_id'],
                'lesson_id' => $params['schedule_lesson_id'],
                'test_id' => $params['test_id'],
                'type' => 6,
                'create_time' => $params['test_time']
            ]);
        }
        parent::$main_db->trans_complete();
        $this->load->library('thrift');
        //kafka生产消费数据
        $this->thrift->service('Kafka')->producer('sasschedule', 'userExercise', [
            'uid' => $params['uid'],
            'schedule_id' => $params['schedule_id'],
            'lesson_id' => $params['schedule_lesson_id'],
            'test' => TRUE,
            'score' => $params['test_score'],
            'dateTime' => $params['test_time']
        ]);
        return $result;
    }

    /**
     * 阶段课程信息
     * @param $scheduleId
     * @return mixed
     */
    public function getScheduleVal($scheduleId)
    {
        $this->load->model('public/schedule_model');
        $schedule = $this->schedule_model->getScheduleInfo($scheduleId, 'class_mode, credit_type');
        if (!empty($schedule)) {
            return $schedule;
        } else {
            show_error('阶段课程id错误' . $scheduleId);
        }
    }

    /**
     * 提交单元测试结果
     * @param $params
     * @return array
     */
    public function submitUnitTest($params)
    {
        $params['modify_time'] = $params['unit_time'] = date('Y-m-d H:i:s');
        $params['category'] = 1;//主修
        $params['unit_status'] = 1;
        $params['is_del'] = 0;
        $unitTest = $this->getUserUnitTestInfo($params['unit_id'], $params['uid']);
        $params['unit_score'] = $this->calUnitTestScore($params['result_id'], $unitTest, $params['schedule_lesson_id'], $params['unit_time']);
        $result['score'] = $params['unit_score'];
        if ($result['score'] === 0) {
            return $result;
        }
        $scheduleInfo = $this->getScheduleVal($params['schedule_id']);
        //阶段课程奖励学分
        if ($scheduleInfo['credit_type'] === '0') {
            $result['no_reward'] = 1;//不奖励学分
            $params['unit_score'] = 0;
        }
        $params['class_mode'] = $scheduleInfo['class_mode'];
        $params['unit_type'] = $this->unitFinishType;
        $unitTestFields = $this->getExerciseFields('unit');
        $this->load->model('public/schedule_model');
        $this->load->model('public/lesson_model');
        //当前学分上限判断
        $this->load->model('public/member_model');
        if (!isset($result['no_reward'])) {
            if ($this->member_model->overScoreLimit($params['uid'])) {
                $params['unit_score'] = 0;
                $result['over_limit'] = 1;
            }
        }

        parent::$main_db->trans_start();
        $upsertResult = $this->lesson_model->upsertScheduleLesson(
            $unitTestFields['insert'],
            $unitTestFields['update'], $params);
        if ($upsertResult) {
            $scoreData = array(
                'uid' => $params['uid'],
                'score_type' => 2,
                'sub_id' => $params['unit_id'],
                'score' => $params['unit_score'],
                'schedule_id' => $params['schedule_id'],
                'schedule_lesson_id' => $params['schedule_lesson_id'],
                'plan_id' => $params['plan_id'],
                'plan_stage_id' => $params['plan_stage_id'],
                'finish_time' => $params['unit_time'],
                'is_del' => 0
            );
            $result['score'] = $scoreData['score'];
            if ($scoreData['score'] === 0) {
                $this->lesson_model->addCompleteStatusLog($scoreData);
            } else {
                $this->member_model->incUserScore($scoreData);
            }
            //新增用户行为
            parent::$main_db->insert('sty_user_schedule_action', [
                'uid' => $params['uid'],
                'schedule_id' => $params['schedule_id'],
                'lesson_id' => $params['schedule_lesson_id'],
                'unit_id' => $params['unit_id'],
                'type' => 7,
                'create_time' => $params['unit_time']
            ]);
        }
        parent::$main_db->trans_complete();
        //kafka生产消费数据
        $this->load->library('thrift');
        $this->thrift->service('Kafka')->producer('sasschedule', 'userExercise', [
            'uid' => $params['uid'],
            'schedule_id' => $params['schedule_id'],
            'lesson_id' => $params['schedule_lesson_id'],
            'test' => FALSE,
            'score' => $params['unit_score'],
            'dateTime' => $params['unit_time']
        ]);
        return $result;
    }

    /**
     * 计算一课一练得分
     * 正确率大于等于80% +3, 否则+0
     * @param $uid
     * @param $test_id
     * @return int
     */
    public function calPracticeScore($uid, $test_id)
    {
        $practice = $this->getUserPracticeInfo($test_id, $uid);
        if (!empty($practice)) {
            if (isset($practice['already_did']) && $practice['right_rate'] >= 80) {
                return 3;
            }
        }
        return 0;
    }

    /**
     * 计算单元测试得分
     * 随到随考：
     *  首次做完正确率达到75% +5，不是首次做完正确率达到75% +3
     * 按时完成：
     *  上课前半小时到上课后一小时之间做完且正确率达到75% +5,
     *  上课一个小时之后做完且正确率达到75% +3
     * @param $resultId
     * @param $unitTest
     * @param $schedule_lesson_id
     * @param $complete_time
     * @return int
     */
    public function calUnitTestScore($resultId, $unitTest, $schedule_lesson_id, $complete_time)
    {
        $rate = $this->getUnitAccuracyRate($resultId);
        if (!empty($unitTest)) {
            if (!$this->isOnTimeUnitTest(null, $unitTest['exam_type'], false)) {
                //随到随考
                if (isset($unitTest['first_time']) && $rate >= 75) {
                    //首次完成
                    $this->unitFinishType = 3;
                    return 5;
                } elseif (!isset($unitTest['first_time']) &&
                    isset($unitTest['already_did']) &&
                    $rate >= 75) {
                    //非首次完成
                    $this->unitFinishType = 4;
                    return 3;
                }
            } else {
                //按时完成
                $this->load->model('learning_task_model');
                $schedule_lesson = $this->learning_task_model->getScheduleLessonInfo($schedule_lesson_id);
                if ($schedule_lesson) {
                    $start_time = strtotime($schedule_lesson['start_time']);
                    $complete_time = strtotime($complete_time);
                    if (isset($unitTest['already_did']) && $rate >= 75 &&
                        $complete_time > $start_time - 1800 &&
                        $complete_time < $start_time + 3600) {
                        //按时
                        $this->unitFinishType = 1;
                        return 5;
                    } elseif (isset($unitTest['already_did']) && $rate >= 75 &&
                        $complete_time >= $start_time + 3600) {
                        //非按时
                        $this->unitFinishType = 2;
                        return 3;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * 计算单元测试正确率
     * @param $id
     * @return float|int
     */
    public function getUnitAccuracyRate($id)
    {
        $accuracyRate = 0;
        $unitScore = parent::$minor_db->select('score, paper_id')
            ->from('jh_zdt_exam_result')
            ->where(array(
                'id' => $id,
            ))
            ->limit(1)
            ->get()->row_array();

        if ($unitScore) {
            $totalScore = parent::$minor_db->select_sum('score')
                ->from('jh_zdt_exam_paper_question_score')
                ->where('paper_id', $unitScore['paper_id'])
                ->get()->row_array();

            if ($totalScore && intval($totalScore['score'])) {
                $totalScore = intval($totalScore['score']);
                $accuracy = intval($unitScore['score']);

                $accuracyRate = intval(floor($accuracy / $totalScore * 100));
            }
        }
        return $accuracyRate;
    }

    /**
     * 为一课一练单元测试提供链接地址
     * @param $exerciseList
     * @param $scheduleId
     * @param $planId
     * @param $planStageId
     * @return
     * @internal param $lessons
     */
    public function exerciseLinkProvider($exerciseList, $scheduleId, $planId, $planStageId)
    {
        $testPrefix = MASTER_DOMAIN . 'test.php?mod=doing&';
        $unitPrefix = MASTER_DOMAIN . 'main.php/kaoshi/';
        $default = array(
            'schedule_id' => $scheduleId,
            'plan_id' => $planId,
            'plan_stage_id' => $planStageId,
            'tp' => 1
        );

        foreach ($exerciseList as $index => $lesson) {
            $lessonId = isset($lesson['id']) ? $lesson['id'] : $lesson['schedule_lesson_id'];
            if (!empty($lesson['test_id'])) {
                $params = array_merge($default, array(
                    'tid' => $lesson['test_id'],
                    'schedule_lesson_id' => $lessonId
                ));
                $exerciseList[$index]['test_begin_url'] = $testPrefix . http_build_query($params);
                $params = array_merge($params, array('act' => 'retest'));
                $exerciseList[$index]['test_retest_url'] = $testPrefix . http_build_query($params);
            }
            if (!empty($lesson['unit_id'])) {
                $params = array_merge($default, array(
                    'paper_id' => $lesson['unit_id'],
                    'schedule_lesson_id' => $lessonId
                ));
                $exerciseList[$index]['unit_begin_url'] = $unitPrefix . 'index?' . http_build_query($params);
                if (isset($lesson['unit_result_id'])) {
                    $params = array_merge($params, array('result_id' => $lesson['unit_result_id']));
                    $exerciseList[$index]['unit_review_url'] = $unitPrefix . 'review?' . http_build_query($params);
                    $exerciseList[$index]['unit_retest_url'] = $unitPrefix . 'index?' . http_build_query($params);
                }
            }
        }
        return $exerciseList;
    }

    /**
     * 更新question id对应的早知道id
     * @param $questionId
     * @param $knowId
     * @return mixed
     */
    public function setKnowId($questionId, $knowId)
    {
        $find = parent::$minor_db->from('zd_test_question')
            ->where('id', $questionId)
            ->count_all_results();
        if ($find === 0) {
            return FALSE;
        }
        return parent::$minor_db->set('zdknow_id', $knowId)
            ->where('id', $questionId)
            ->update('zd_test_question');
    }
}
