<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learning_report_model extends MY_Model
{
    protected $totalUpdateCount = null;
    protected $isRecordSchedule = array();

    public function __construct()
    {
        $this->load->driver('cache');
    }

    /**
     * 获取用户学分
     * @param $uid
     * @param string $type week|total
     * @param $schedule_id 不传的话获取所有阶段学分
     * @return int
     */
    public function getUserScore($uid, $type, $schedule_id = null)
    {
        $key = $this->getRankKey($type, $schedule_id);
        $score = $this->getScore($key, $uid);
        return empty($score) ? 0 : intval($score);
    }

    /**
     * 获取用户排名
     * @param $uid
     * @param string $type week|total
     * @param null $schedule_id
     * @return int|mixed
     */
    public function getUserRank($uid, $type, $schedule_id = null)
    {
        $key = $this->getRankKey($type, $schedule_id);
        $rank = $this->getRank($key, $uid);
        return empty($rank) ? 0 : intval($rank);
    }

    /**
     * 总出勤率和直播出勤率
     * 录播课程的直播出勤率为0
     * 直播阶段课程，只统计当前时间晚于下课时间前30分钟的课程
     * @param $schedule_id
     * @param $uid
     * @param $type 1 总出勤率|2 直播出勤率
     * @return array
     */
    public function checkInReport($uid, $type, $schedule_id, $planId, $planStageId)
    {
        $total = $checked = $unchecked = $percentage = 0;
        $resultReport = array(
            'total' => $total,
            'checked' => $checked,
            'unchecked' => $unchecked,
            'percentage' => $percentage
        );
        $now = date('Y-m-d H:i:s', time() + 1800);
        if (!isset($this->isRecordSchedule[$schedule_id])) {
            $this->load->model('public/schedule_model');
            $isRecord = $this->schedule_model->isRecordSchedule($schedule_id);
            $this->isRecordSchedule[$schedule_id] = $isRecord;
        } else {
            $isRecord = $this->isRecordSchedule[$schedule_id];
        }

        //是否是录播
        if (!$isRecord) {
            $where = array(
                'susl.uid' => $uid,
                'ssle.schedule_id' => $schedule_id,
                'ssle.is_del' => 0,
                'susl.is_del' => 0,
                'ssle.end_time <' => $now,
                'ssle.start_time >' => '1000-01-01 00:00:00',
                'susl.check_in_status' => 1
            );
            if ($type == 2) {//按时报到
                $where['susl.check_in_type'] = 1;
            }

            if ($this->totalUpdateCount === null) {
                $total = $this->totalScheduleLessonCount($schedule_id, $now);
            }

            if ($total !== 0) {
                $checked = parent::$main_db->from('sty_schedule_lesson ssle')
                    ->join('sty_user_schedule_lesson susl', 'ssle.id=susl.schedule_lesson_id', 'left')
                    ->where($where)
                    ->count_all_results();
            }
        } elseif ($isRecord && $type === 1) {
            $total = $this->totalScheduleLessonCount($schedule_id);
            if ($total !== 0) {
                $checked = parent::$main_db->from('sty_schedule_lesson ssle')
                    ->join('sty_user_schedule_lesson susl', 'ssle.id=susl.schedule_lesson_id', 'left')
                    ->where(array(
                        'susl.uid' => $uid,
                        'ssle.schedule_id' => $schedule_id,
                        'susl.check_in_status' => 1,
                        'ssle.is_del' => 0,
                        'susl.is_del' => 0
                    ))
                    ->count_all_results();
            }
        }
        $unchecked = abs($total - $checked);
        //计算百分比
        if ($total !== 0) {
            $percentage = intval(round(($checked / $total) * 100));
        }

        $resultReport['total'] = $total;
        $resultReport['checked'] = $checked;
        $resultReport['checked_link'] = $this->genLearnLink($schedule_id, $planId, $planStageId, 1);
        $resultReport['unchecked'] = $unchecked;
        $resultReport['unchecked_link'] = $this->genLearnLink($schedule_id, $planId, $planStageId, 2);
        $resultReport['percentage'] = $percentage;
        return $resultReport;
    }

    /**
     * 不考虑上课时间，计算阶段课程总出勤率
     * @param $uid
     * @param $schedule_id
     * @return float|int
     */
    public function attendanceNoTime($uid, $schedule_id)
    {
        $total = $this->totalScheduleLessonCount($schedule_id);
        if ($total !== 0) {
            $checked = parent::$main_db->from('sty_user_schedule_lesson')
                ->where(array(
                    'uid' => $uid,
                    'schedule_id' => $schedule_id,
                    'check_in_status' => 1,
                    'is_del' => 0
                ))
                ->count_all_results();
            return intval(round(($checked / $total) * 100));
        } else {
            return 0;
        }
    }

    /**
     * 获取阶段课程里的课件总数
     * @param $schedule_id
     * @param null $now 是否考虑时间范围
     * @return int
     */
    public function totalScheduleLessonCount($schedule_id, $now = null)
    {
        $where = array(
            'schedule_id' => $schedule_id,
            'is_del' => 0,
        );
        if ($now) {
            $where['end_time <'] = $now;
            $where['start_time >'] = '1000-01-01 00:00:00';
        }
        $count = parent::$main_db->from('sty_schedule_lesson')
            ->where($where)
            ->count_all_results();
        return $count;
    }

    /**
     * 做题任务完成率
     * @param $schedule_id
     * @param $uid
     * @param $type test 一课一练|unit 单元测试
     * @return array
     */
    public function exerciseReport($uid, $type, $schedule_id, $planId, $planStageId)
    {
        $finished = $unfinished = 0;
        $id = $type === 'test' ? 'test_id' : 'unit_id';

        if (!isset($this->isRecordSchedule[$schedule_id])) {
            $this->load->model('public/schedule_model');
            $isRecord = $this->schedule_model->isRecordSchedule($schedule_id);
            $this->isRecordSchedule[$schedule_id] = $isRecord;
        } else {
            $isRecord = $this->isRecordSchedule[$schedule_id];
        }

        parent::$main_db->from('sty_schedule_lesson')
            ->where(array(
                'schedule_id' => $schedule_id,
                "$id >" => 0,
                'is_del' => 0
            ));
        if (!$isRecord) {
            parent::$main_db->where('start_time <', date('Y-m-d 23:59:59'));
            parent::$main_db->where('start_time >', '1000-01-01 00:00:00');
        }
        $total = parent::$main_db->count_all_results();
        if ($total > 0) {
            //已完成次数
            parent::$main_db->from('sty_schedule_lesson ssle')
                ->join('sty_user_schedule_lesson_score sysls',
                    "ssle.{$id} = sysls.sub_id", 'left')
                ->where(array(
                    'ssle.schedule_id' => $schedule_id,
                    'sysls.uid' => $uid,
                    'sysls.type' => $type === 'test' ? 1 : 2,
                    'ssle.is_del' => 0,
                    'sysls.is_del' => 0
                ));
            if (!$isRecord) {
                parent::$main_db->where('ssle.start_time <', date('Y-m-d 23:59:59'));
                parent::$main_db->where('ssle.start_time >', '1000-01-01 00:00:00');
            }
            $finished = parent::$main_db->count_all_results();
            $unfinished = abs($total - $finished);
        }

        //计算百分比
        if ($total !== 0) {
            $percentage = round(($finished / $total) * 100);
        } else {
            $percentage = 0;
        }

        return array(
            'total' => $total,
            'checked' => $finished,
            'checked_link' => $this->genExerciseLink($schedule_id, $planId, $planStageId, $type, 1),
            'unchecked' => $unfinished,
            'unchecked_link' => $this->genExerciseLink($schedule_id, $planId, $planStageId, $type, 2),
            'percentage' => $percentage
        );
    }

    /**
     * 获取阶段课程学分排行
     * @param $schedule_id
     * @param $pageNum
     * @param $pageCount
     * @param string $type week|total
     * @return mixed
     */
    public function scheduleRankList($schedule_id, $pageNum, $pageCount, $type)
    {
        $offset = ($pageNum - 1) * $pageCount;
        $index = 1;
        $key = $this->getRankKey($type, $schedule_id);
        $uids = $users = array();
        $this->load->model('public/member_model');
        $rankList = $this->redisRankList($key, $offset, $offset + $pageCount - 1);
        if (!empty($rankList)) {
            foreach ($rankList as $uid => $score) {
                $uids[] = $uid;
                $users[$uid]['score'] = $score;
                //pageCount 不能小于3
                if ($pageNum === 1) {
                    if ($index === 1) {
                        $users[$uid]['title'] = '学神';
                    } elseif ($index === 2 || $index === 3) {
                        $users[$uid]['title'] = '学霸';
                    }
                }
                $users[$uid]['name'] = '用户' . $uid;
                $users[$uid]['avatar'] = avatar($uid, 'small');
                $index++;
            }

            //获取用户名
            $names = $this->member_model->getNameLevel($uids);
            foreach ($names as $user) {
                $users[$user['uid']]['name'] = $user['username'];
                if (!empty($user['user_level'])) {
                    $users[$user['uid']]['level'] = get_options('user_level_icon', intval($user['user_level']));
                }
            }
        }
        return $users;
    }

    /**
     * 获取全部学分排行
     * @param $pageNum
     * @param $pageCount
     * @param string $type week|total
     * @return mixed
     */
    public function allRankList($pageNum, $pageCount, $type)
    {
        $offset = ($pageNum - 1) * $pageCount;
        $key = $this->getRankKey($type);

        $rankList = $this->redisRankList($key, $offset, $offset + $pageCount - 1);
        return $rankList;
    }

    public function redisRankList($key, $start, $end)
    {
        return $this->cache->redis->zRevRange($key, $start, $end, true);
    }

    /**
     * 获取学分排行key值
     * @param $type
     * @param null $schedule_id
     * @return string
     */
    public function getRankKey($type, $schedule_id = null)
    {
        $key = '';
        if ($type === 'week') {
            $key = $this->getWeeklyRankKey($schedule_id);
        } elseif ($type === 'total') {
            $key = $this->getTotalRankKey($schedule_id);
        }
        return $key;
    }

    /**
     * 获取周学分排行key值
     * @param null $schedule_id null全部阶段
     * @return string
     */
    protected function getWeeklyRankKey($schedule_id = null)
    {
        if ($schedule_id === null) {
            $key = 'weekly_rank';
        } else {
            $key = 'weekly_rank:' . $schedule_id;
        }
        return $key;
    }

    /**
     * 获取总学分排行key值
     * @param null $schedule_id
     * @return string
     */
    protected function getTotalRankKey($schedule_id = null)
    {
        if ($schedule_id === null) {
            $key = 'total_rank';
        } else {
            $key = 'total_rank:' . $schedule_id;
        }
        return $key;
    }

    /**
     * 获取用户学分
     * @param $key
     * @param $uid
     * @return float|boolean 没有取到返回false
     */
    public function getScore($key, $uid)
    {
        return $this->cache->redis->zScore($key, $uid);
    }

    /**
     * 获取用户排名
     * @param $key
     * @param $uid
     * @return mixed
     */
    public function getRank($key, $uid)
    {
        return $this->cache->redis->zRevRank($key, $uid);
    }

    /**
     * 获取排行榜上的总人数
     * @param $schedule_id
     * @param $type
     * @return mixed
     */
    public function getTotalCount($schedule_id, $type)
    {
        $key = $this->getRankKey($type, $schedule_id);
        return $this->cache->redis->zSize($key);
    }

    /**
     * 生成跳转到上课任务的链接
     * @param $scheduleId
     * @param $planId
     * @param $planStageId
     * @param $type 1 已完成，2 未完成
     * @return string
     */
    public function genLearnLink($scheduleId, $planId, $planStageId, $type)
    {
        $params = array(
            'plan' => $planId,
            'stage' => $planStageId,
            'nav' => 'book',
            'category' => 'record',
            'type' => $type
        );
        return "/learningsystem/schedule/{$scheduleId}?" . http_build_query($params);
    }

    /**
     * 生成跳转到做题任务的链接
     * @param $scheduleId
     * @param $planId
     * @param $planStageId
     * @param $type 1 已完成，2 未完成
     * @return string
     */
    public function genExerciseLink($scheduleId, $planId, $planStageId, $category, $type)
    {
        $params = array(
            'plan' => $planId,
            'stage' => $planStageId,
            'nav' => 'tadk',
            'category' => $category,
            'type' => $type
        );
        return "/learningsystem/schedule/{$scheduleId}?" . http_build_query($params);
    }

}

