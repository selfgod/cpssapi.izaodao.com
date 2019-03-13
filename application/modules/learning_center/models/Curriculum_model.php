<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Curriculum_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/common_model');
        $this->load->model('public/goods_model');
        $this->load->model('public/schedule_model');
        $this->load->model('public/lesson_model');
    }

    /**
     * 直播课表
     * @param $uid
     * @param string $xDate
     * @return mixed
     */
    public function liveSchedule($uid, $xDate = '')
    {
        $result = [];
        //验证用户是否报名
        $goods_status = $this->goods_model->getUserGoodsStatus($uid);
        $goods_status['selectCourse'] = FALSE;
        if ($goods_status['hasGoods'] === TRUE) {
            //获取一周内数据
            if (empty($xDate) && empty(strtotime($xDate))) {
                $xDate = date('Y-m-d');
            }
            $result['current'] = $xDate;
            $result['current_year'] = explode('-', $xDate)[0];
            $result['current_month'] = explode('-', $xDate)[1];
            $weekDate = $this->common_model->onWeekDate($xDate);
            //验证商品是否过期
            if ($goods_status['hasExpire'] === TRUE) {
                //验证是否选课
                $goods_status['selectCourse'] = $this->userIsSelectCourse($uid);
                if ($goods_status['selectCourse'] === TRUE) {
                    //这一周中每天是否有课
                    foreach ($weekDate as $wk => $wv) {
                        $weekDate[$wk]['hasCourse'] = FALSE;
                        if ($wv['date'] == $xDate) {
                            $resData = $this->userNowDaySchedule($uid, $wv['date']);
                            if (!empty($resData)) {
                                if (!empty($resData['asoftData'])) {
                                    $weekDate[$wk]['hasCourse'] = TRUE;
                                }
                                $result = array_merge($result, $resData);
                            }
                        } else {
                            $weekDayDate = $this->userThisDayHasSchedule($uid, $wv['date']);
                            if (!empty($weekDayDate)) {
                                $weekDate[$wk]['hasCourse'] = $weekDayDate['hasCourse'];
                            }
                        }
                    }
                }
                $result['weekDate'] = $weekDate;
                $result['xdate'] = $xDate;
            }
        }
        $result = array_merge($result, $goods_status);
        return $result;
    }

    /**
     * 用户在这天是否有课
     * @param $uid
     * @param $day
     * @return array
     */
    public function userThisDayHasSchedule($uid, $day)
    {
        $result = [];
        $hasCourse = FALSE;
        if (!empty($day) && !empty(strtotime($day))) {
            $day = date('Y-m-d', strtotime($day));
            //验证是否有课
            $start_time = date('Y-m-d 00:00:00', strtotime($day));
            $end_time = date('Y-m-d 23:59:59', strtotime($day));
            $hasReservationLesson = $this->lesson_model->getUserScheduleLessonDetial($uid, [
                'susl.reservation_status' => 1,
                'ssl.start_time>' => $start_time,
                'ssl.start_time<' => $end_time
            ], [
                'ssl.name',
                'ssl.alias',
                'ssl.start_time',
                'ssl.end_time',
            ], ['ssl.start_time ASC'], [1]);
            if (!empty($hasReservationLesson)) {
                $hasCourse = TRUE;
            }
            if ($hasCourse === FALSE) {
                $ableSchedule = $this->schedule_model->getUserAbleScheduleArr($uid);
                if (!empty($ableSchedule)) {
                    $where = [
                        'ssl.start_time>' => $start_time,
                        'ssl.start_time<' => $end_time,
                        'ss.class_mode' => 1,
                        'ss.curricular_system' => 1,
                        'where_in' => ['ss.id', $ableSchedule]
                    ];
                    //用户存在常规课去除精品课、体验课显示
                    $this->load->model('public/plan_model');
                    $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
                    if (!empty($planHave)) {
                        $where['_string'] = '(ss.business_type NOT IN (2, 5))';
                    }
                    $hasLesson = $this->lesson_model->getScheduleLessonDetail($where, [
                        'ssl.name',
                        'ssl.alias',
                        'ssl.start_time',
                        'ssl.end_time'
                    ], ['ssl.start_time ASC'], [1]);
                    if (!empty($hasLesson)) {
                        $hasCourse = TRUE;
                    }
                }
            }
        }
        $result['hasCourse'] = $hasCourse;
        return $result;
    }

    /**
     * 当天课程表（包括主修直播课，预约制已预约课）
     * @param $uid
     * @param $day
     * @return array
     */
    public function userNowDaySchedule($uid, $day)
    {
        $now_time = date('Y-m-d H:i:s');
        $result = ['asoftData' => [], 'lessonData' => []];
        $curricular = array_flip(get_options('curricular_system'));
        $schedulePlanStage = [];
        $_string = '';
        if (!empty($day) && !empty(strtotime($day))) {
            $day = date('Y-m-d', strtotime($day));
            $start_time = date('Y-m-d 00:00:00', strtotime($day));
            $end_time = date('Y-m-d 23:59:59', strtotime($day));
            $scheduleInfoArr = $teacherArr = $asoftData = [];
            $ableSchedule = $this->schedule_model->getUserAbleScheduleArr($uid);
            if (!empty($ableSchedule)) {
                //用户存在常规课去除精品课、体验课显示
                $this->load->model('public/plan_model');
                $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
                if (!empty($planHave)) {
                    $_string = '(ss.business_type NOT IN (2, 5))';
                }
                $schedule_lesson = $this->lesson_model->getScheduleLessonDetail([
                    'ssl.start_time>' => $start_time,
                    'ssl.start_time<' => $end_time,
                    'ss.class_mode' => 1,
                    'ss.curricular_system' => 1,
                    'where_in' => ['ss.id', $ableSchedule],
                    '_string' => $_string
                ], [
                    'ssl.name',
                    'ssl.alias',
                    'ssl.teacher_id',
                    'ssl.is_record',
                    'ssl.record_link',
                    'ssl.record_download',
                    'ssl.start_time',
                    'ssl.end_time',
                    'ssl.curricular_system',
                    'ssl.test_id',
                    'ssl.unit_id',
                    'ss.name as schedule_name',
                    'ss.room_name',
                    'ss.room_pwd',
                    'ss.enable_zdtalk'
                ], ['ssl.start_time ASC']);
                if (!empty($schedule_lesson)) {
                    foreach ($schedule_lesson as $slv) {
                        $lesson_data = [];
                        $lesson_id = intval($slv['id']);
                        $schedule_id = intval($slv['schedule_id']);
                        $teacher_id = intval($slv['teacher_id']);
                        if (!$lesson_id) continue;
                        if ($teacher_id && !isset($teacherArr[$teacher_id])) {
                            $teacher_info = $this->schedule_model->getTeacherInfo($teacher_id);
                            if (!empty($teacher_info)) {
                                $teacherArr[$teacher_id] = $teacher_info;
                            }
                        }
                        if (!isset($schedulePlanStage[$schedule_id])) {
                            $joinSchedule = $this->schedule_model->viewUserPlanStageJoinSchedule([
                                'uid' => $uid,
                                'schedule_id' => $schedule_id,
                                '_string' => "(unlimit_expire = 1 OR expire > '" . date('Y-m-d H:i:s') . "')"
                            ], ['plan_id', 'plan_stage_id'], [], [1]);
                            if (!empty($joinSchedule)) {
                                $schedulePlanStage[$schedule_id]['plan_id'] = $joinSchedule['plan_id'];
                                $schedulePlanStage[$schedule_id]['plan_stage_id'] = $joinSchedule['plan_stage_id'];
                            }
                        }
                        $userLessonStatus = $this->lesson_model->getUserScheduleLessonInfo($uid, $lesson_id);
                        if (!isset($asoftData[$lesson_id])) {
                            $asoftData[$lesson_id] = $slv['start_time'];
                            $lesson_data['lesson_id'] = $lesson_id;
                            $lesson_data['schedule_id'] = $schedule_id;
                            $lesson_data['plan_id'] = 0;
                            $lesson_data['plan_stage_id'] = 0;
                            if (isset($schedulePlanStage[$schedule_id]) && !empty($schedulePlanStage[$schedule_id])) {
                                $lesson_data['plan_id'] = $schedulePlanStage[$schedule_id]['plan_id'];
                                $lesson_data['plan_stage_id'] = $schedulePlanStage[$schedule_id]['plan_stage_id'];
                            }
                            $lesson_data['is_reservation'] = FALSE;
                            $lesson_data['room_name'] = $slv['room_name'];
                            $lesson_data['room_pwd'] = $slv['room_pwd'];
                            $lesson_data['enable_zdtalk'] = $slv['enable_zdtalk'];
                            $lesson_data['title'] = !empty($slv['alias']) ? $slv['alias'] : $slv['name'];
                            if (!empty($slv['schedule_name'])) {
                                $lesson_data['title'] = $slv['schedule_name'] . '：' . $lesson_data['title'];
                            }
                            $lesson_data['teacher_name'] = '';
                            $lesson_data['teacher_avatar'] = '';
                            if (!empty($teacherArr[$teacher_id])) {
                                $lesson_data['teacher_name'] = $teacherArr[$teacher_id]['name'];
                                $lesson_data['teacher_avatar'] = $teacherArr[$teacher_id]['avatar'];
                            }
                            $lesson_data['check_in_status'] = 0;
                            $lesson_data['check_in_type'] = 0;
                            $lesson_data['check_in_score'] = 0;
                            if (!empty($userLessonStatus)) {
                                $lesson_data['check_in_status'] = $userLessonStatus['check_in_status'];
                                $lesson_data['check_in_type'] = $userLessonStatus['check_in_type'];
                                $lesson_data['check_in_score'] = $userLessonStatus['check_in_score'];
                            }
                            $lesson_data['start_time'] = $slv['start_time'];
                            $lesson_data['end_time'] = $slv['end_time'];
                            $lesson_data['curricular'] = isset($curricular[$slv['curricular_system']]) ? $curricular[$slv['curricular_system']] : '';
                            $lesson_data['is_record'] = $slv['is_record'];
                            $lesson_data['record_link'] = $slv['record_link'];
                            $lesson_data['record_download'] = $slv['record_download'];
                            $lesson_data['zdtalk'] = $this->schedule_model->enableZDTalk($uid, [
                                'enable_zdtalk' => $lesson_data['enable_zdtalk'],
                                'schedule_id' => $lesson_data['schedule_id']
                            ], $lesson_data['lesson_id'], $lesson_data['plan_id'], $lesson_data['plan_stage_id']);
                            $lesson_data['test_id'] = $slv['test_id'];
                            $lesson_data['unit_id'] = $slv['unit_id'];
                            $lesson_data['button'] = $this->lessonBlueButtonVal($uid, $lesson_data);
                            $lesson_res = $this->lessonPrompt($lesson_data);
                            $lesson_data = array_merge($lesson_data, $lesson_res);
                            $result['lessonData'][$lesson_id] = $lesson_data;
                        }
                    }
                }
            }

            //预约列表
            $reservation_lesson = $this->lesson_model->getUserScheduleLessonDetial($uid, [
                'susl.reservation_status' => 1,
                'ssl.start_time>' => $start_time,
                'ssl.start_time<' => $end_time
            ], [
                'susl.reservation_status',
                'susl.check_in_status',
                'susl.check_in_type',
                'susl.check_in_score',
                'ssl.name',
                'ssl.alias',
                'ssl.teacher_id',
                'ssl.is_record',
                'ssl.record_link',
                'ssl.record_download',
                'ssl.start_time',
                'ssl.end_time',
                'ssl.business_type',
                'ssl.curricular_system'
            ], ['ssl.start_time ASC']);
            if (!empty($reservation_lesson)) {
                foreach ($reservation_lesson as $rlv) {
                    $lesson_data = [];
                    $lesson_id = intval($rlv['schedule_lesson_id']);
                    $schedule_id = intval($rlv['schedule_id']);
                    $teacher_id = intval($rlv['teacher_id']);
                    if (!$lesson_id) continue;
                    if ($schedule_id && !isset($scheduleInfoArr[$schedule_id])) {
                        $schedule_info = $this->schedule_model->getScheduleBaseInfo($schedule_id);
                        if (!empty($schedule_info)) {
                            $scheduleInfoArr[$schedule_id] = $schedule_info;
                        }
                    }
                    if ($teacher_id && !isset($teacherArr[$teacher_id])) {
                        $teacher_info = $this->schedule_model->getTeacherInfo($teacher_id);
                        if (!empty($teacher_info)) {
                            $teacherArr[$teacher_id] = $teacher_info;
                        }
                    }
                    if (!isset($asoftData[$lesson_id])) {
                        $asoftData[$lesson_id] = $rlv['start_time'];
                        $lesson_data['lesson_id'] = $lesson_id;
                        $lesson_data['schedule_id'] = $schedule_id;
                        $lesson_data['plan_id'] = 0;
                        $lesson_data['plan_stage_id'] = 0;
                        $lesson_data['is_reservation'] = TRUE;
                        $lesson_data['room_name'] = '';
                        $lesson_data['room_pwd'] = '';
                        $lesson_data['enable_zdtalk'] = 0;
                        $lesson_data['title'] = !empty($rlv['alias']) ? $rlv['alias'] : $rlv['name'];
                        if (!empty($scheduleInfoArr[$schedule_id]) && !empty($scheduleInfoArr[$schedule_id]['name'])) {
                            $lesson_data['title'] = $scheduleInfoArr[$schedule_id]['name'] . '：' . $lesson_data['title'];
                            $lesson_data['room_name'] = $scheduleInfoArr[$schedule_id]['room_name'];
                            $lesson_data['room_pwd'] = $scheduleInfoArr[$schedule_id]['room_pwd'];
                            $lesson_data['enable_zdtalk'] = $scheduleInfoArr[$schedule_id]['enable_zdtalk'];
                        }
                        $lesson_data['teacher_name'] = '';
                        $lesson_data['teacher_avatar'] = '';
                        if (!empty($teacherArr[$teacher_id])) {
                            $lesson_data['teacher_name'] = $teacherArr[$teacher_id]['name'];
                            $lesson_data['teacher_avatar'] = $teacherArr[$teacher_id]['avatar'];
                        }
                        $lesson_data['start_time'] = $rlv['start_time'];
                        $lesson_data['end_time'] = $rlv['end_time'];
                        $lesson_data['curricular'] = isset($curricular[$rlv['curricular_system']]) ? $curricular[$rlv['curricular_system']] : '';
                        $lesson_data['check_in_status'] = $rlv['check_in_status'];
                        $lesson_data['check_in_type'] = $rlv['check_in_type'];
                        $lesson_data['check_in_score'] = $rlv['check_in_score'];
                        $lesson_data['reservation_status'] = $rlv['reservation_status'];
                        $lesson_data['is_record'] = $rlv['is_record'];
                        $lesson_data['record_link'] = $rlv['record_link'];
                        $lesson_data['record_download'] = $rlv['record_download'];
                        $lesson_data['zdtalk'] = $this->schedule_model->enableZDTalk($uid, [
                            'enable_zdtalk' => $lesson_data['enable_zdtalk'],
                            'schedule_id' => $lesson_data['schedule_id']
                        ], $lesson_data['lesson_id']);
                        $lesson_data['button'] = $this->lessonBlueButtonVal($uid, $lesson_data);
                        $lesson_res = $this->lessonPrompt($lesson_data);
                        $lesson_data = array_merge($lesson_data, $lesson_res);
                        $result['lessonData'][$lesson_id] = $lesson_data;
                    }
                }
            }
            if (!empty($asoftData)) {
                asort($asoftData);//根据开课时间升序排序
                $result['asoftData'] = $asoftData;
            } else {
                //如果恰好是今天没课程 查询下一次上课时间
                if ($day == date('Y-m-d')) {
                    $result['afterLesson'] = [];
                    $afterReservationLesson = $this->lesson_model->getUserScheduleLessonDetial($uid, [
                        'susl.reservation_status' => 1,
                        'ssl.start_time>' => $end_time,
                    ], [
                        'ssl.name',
                        'ssl.alias',
                        'ssl.start_time',
                        'ssl.end_time',
                    ], ['ssl.start_time ASC'], [1]);
                    if (!empty($afterReservationLesson)) {
                        $result['afterLesson']['lesson_id'] = $afterReservationLesson['schedule_lesson_id'];
                        $result['afterLesson']['start_time'] = $afterReservationLesson['start_time'];
                    }
                    if (!empty($ableSchedule)) {
                        $afterLesson = $this->lesson_model->getScheduleLessonDetail([
                            'ssl.start_time>' => $end_time,
                            'ss.class_mode' => 1,
                            'where_in' => ['ss.id', $ableSchedule],
                            '_string' => $_string
                        ], [
                            'ssl.name',
                            'ssl.alias',
                            'ssl.start_time',
                            'ssl.end_time'
                        ], ['ssl.start_time ASC'], [1]);
                        if (!empty($afterLesson)) {
                            if (empty($result['afterLesson']['start_time']) || $afterLesson['start_time'] < $result['afterLesson']['start_time']) {
                                $result['afterLesson']['lesson_id'] = $afterLesson['id'];
                                $result['afterLesson']['start_time'] = $afterLesson['start_time'];
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 课程提示
     * @param $lesson
     * @return string
     */
    public function lessonPrompt($lesson)
    {
        $now_time = time();
        $result = ['report_prompt' => '', 'score_class' => 'color_e8e8e8', 'report_score' => 0];
        if (intval($lesson['check_in_status']) === 1) {
            $prompt = '已报到';
            $result['report_icon'] = img_url('learning_center', 'status_img_off_v5.png');
            if (intval($lesson['check_in_score']) > 0) {
                $result['report_score'] = $lesson['check_in_score'];
                $result['score_class'] = 'color_f9b600';
            }
        } else {
            $prompt = '未报到';
            $result['report_icon'] = img_url('learning_center', 'status_img_ov_v5.png');
            if ($now_time > strtotime($lesson['end_time']) + 60 * 90) {
                $prompt = '旷课';
            }
        }
        $result['report_prompt'] = $prompt;
        return $result;
    }

    /**
     * 课程蓝色按钮
     * @param $lesson
     */
    public function lessonBlueButtonVal($uid, $lesson, $cancelReservation = FALSE)
    {
        $button = '';
        $now_time = time();
        $lesson_title = $lesson['title'];
        $sevenDayAgo = strtotime($lesson['start_time'] . '-7 day');
        $twoHourAgo = strtotime($lesson['start_time'] . '-2 hour');
        $review_btn = '<a href="javascript:;" class="cpss_large_btn blue flR ml10 lesson_review" data-exist="1" ga-type="看录播课" ga-title="' . $lesson_title . '">看录播课</a>';
        $review_gray_btn = '<a href="javascript:;" title="课件未上传" class="cpss_large_btn gray flR ml10" data-exist="0">看录播课</a>';
        $not_review_btn = '<a href="javascript:;" class="cpss_large_btn dark flR">无录播课</a>';
        $live_end_btn = '<p class="fz12 color_bbb mt07 flR mr10">直播已结束</p>';
        $repoet_btn = '<a href="javascript:;" class="cpss_large_btn flR report_lesson" ga-type="报到" ga-title="' . $lesson_title . '">报 到</a>';
        $reported_btn = '<a href="javascript:;" class="cpss_large_btn dark flR">已报到</a>';
        $today_live_btn = '<p class="fz12 color_bbb mt07 flR mr10">今天直播 ' . date('H:i', strtotime($lesson['start_time'])) . '-' . date('H:i', strtotime($lesson['end_time'])) . '</p>';
        $wait_live_btn = '<p class="fz12 color_bbb mt07 flR mr10">等待直播 ' . date('H:i', strtotime($lesson['start_time'])) . '-' . date('H:i', strtotime($lesson['end_time'])) . '</p>';
        $exercise_btn = '<a target="_blank" href="%s"><i class="exercise_ov curP flR mt05 mr10" ga-type="做题图标" ga-title="' . $lesson_title . '"></i></a>';
        $download_btn = $download_gray_btn = '';
        if (intval($lesson['record_download']) === 1) {
            $download_btn = '<i class="cpss_icon_download_ov curP flR mt05 lesson_download" data-exist="1" ga-type="下载图标" ga-title="' . $lesson_title . '"></i>';
            $download_gray_btn = '<i class="cpss_icon_download_off flR mt05" data-exist="0"></i>';
        }
        $reservationClass = 'reservation_lesson';
        $reservation_time = date('m月d日 H:s', $sevenDayAgo);
        if ($now_time < $sevenDayAgo) {
            $reservationClass = 'not_reservation_lesson';
        }
        $reservation_btn = '<a href="javascript:;" data-reservation_time="' . $reservation_time . '" class="button_w78 btn_line_3399ff flL ' . $reservationClass . '" ga-type="预约" ga-title="' . $lesson_title . '">预约</a>';
        $unable_reservation_btn = '<a href="javascript:;" class="button_w80 btn_f5f5f5 flL ml10">预约</a>';
        $tow_hour_reservation_btn = '<p class="fz12 color_aaa mt05">开课前两小时以内不可预约</p>';
        $cancel_reservation_btn = '<p class="cpss_cancel_text cancel_reservation_lesson flR" ga-type="取消预约" ga-title="' . $lesson_title . '">取消预约</p>';
        $able_reservation_btn = '<p class="fz12 color_4bb866 mt05">' . $reservation_time . '可预约</p>';
        $reservation_full_btn = '<a href="javascript:;" class="button_w80 btn_f5f5f5 flL ml10">已满</a>';
        $room_title = '进入教室';
        $look_room_time = date('m月d日H点', $twoHourAgo);
        $zdtalkUrl = $download = '';
        if ($lesson['is_reservation'] === TRUE) {
            $classroomClass = 'not_look_room';
            if ($lesson['zdtalk']['enable'] === TRUE) {
                $classroomClass = 'look_room zdtalk';
                $zdtalkUrl = $lesson['zdtalk']['href'];
                $download = get_options('zdtalk_download');
            } elseif ($now_time > $twoHourAgo) {
                $classroomClass = 'look_room';
            }
        } else {
            $classroomClass = 'look_room';
            if ($lesson['zdtalk']['enable'] === TRUE) {
                $classroomClass .= ' zdtalk';
                $zdtalkUrl = $lesson['zdtalk']['href'];
                $download = get_options('zdtalk_download');
            }
        }
        $classroom_btn = '<a href="javascript:;" data-zdtalk="' . $zdtalkUrl . '" data-download="' . $download . '" data-look_time="' . $look_room_time . '" class="cpss_large_btn blue flR ml10 ' . $classroomClass . '" ga-type="' . $room_title . '" ga-title="' . $lesson_title . '">' . $room_title . '</a>';

        if ($lesson['is_reservation'] === TRUE) {
            //已预约
            if (intval($lesson['reservation_status']) === 1) {
                //已结束
                if ($now_time > strtotime($lesson['end_time']) + 60 * 90 || $lesson['start_time'] < date('Y-m-d 00:00:00')) {
                    if ($lesson['is_record'] == 1) {
                        if (!empty($lesson['record_link'])) {
                            $button .= $review_btn . $download_btn;
                        } else {
                            $button .= $review_gray_btn . $download_gray_btn;
                        }
                    } else {
                        $button .= $not_review_btn;
                    }
                    if ($cancelReservation !== TRUE) {
                        $button .= $live_end_btn;
                    }
                } else {
                    $button .= $classroom_btn;
                    if (intval($lesson['check_in_status']) === 1) {
                        $button .= $reported_btn;
                    } else {
                        $button .= $repoet_btn;
                    }
                    if ($cancelReservation !== TRUE) {
                        if (date('Y-m-d', strtotime($lesson['start_time'])) === date('Y-m-d')) {
                            $button .= $today_live_btn;
                        } else {
                            $button .= $wait_live_btn;
                        }
                    }
                    if (intval($lesson['check_in_status']) !== 1 && $cancelReservation === TRUE) {
                        if ($now_time < $twoHourAgo) {
                            $button .= $cancel_reservation_btn;
                        }
                    }
                }
            } else {
                if ($now_time > $twoHourAgo) {
                    $button .= $unable_reservation_btn . $tow_hour_reservation_btn;
                } else {
                    if (intval($lesson['max_num']) > 0 && intval($lesson['lesson_reservation_count']) >= intval($lesson['max_num'])) {
                        //预约人数已满
                        $button .= $reservation_full_btn;
                    } else {
                        $button .= $reservation_btn;
                    }
                    if ($now_time < $sevenDayAgo) {
                        $button .= $able_reservation_btn;
                    }
                }
            }
        } else {
            if ($now_time > strtotime($lesson['end_time']) + 60 * 90) {
                if ($lesson['is_record'] == 1) {
                    if (!empty($lesson['record_link'])) {
                        $button .= $review_btn . $download_btn;
                    } else {
                        $button .= $review_gray_btn . $download_gray_btn;
                    }
                } else {
                    $button .= $not_review_btn;
                }
                $this->load->model('learning_system/exercise_model');
                $test_id = intval($lesson['test_id']);
                if ($test_id > 0) {
                    $exercise = $this->exercise_model->getUserPracticeInfo($test_id, $uid);
                    $exercise_url = MASTER_DOMAIN . 'test.php?mod=doing&schedule_id=' . $lesson['schedule_id'] . '&plan_id=' . $lesson['plan_id'] . '&plan_stage_id=' . $lesson['plan_stage_id'] . '&tp=1&tid=' . $test_id . '&schedule_lesson_id=' . $lesson['lesson_id'];
                    if (isset($exercise['already_did'])) {
                        $exercise_url .= '&act=retest';
                    }
                    $button .= sprintf($exercise_btn, $exercise_url);
                }
                if ($cancelReservation !== TRUE) {
                    $button .= $live_end_btn;
                }
            } else {
                $button .= $classroom_btn;
                if (intval($lesson['check_in_status']) === 1) {
                    $button .= $reported_btn;
                } else {
                    $button .= $repoet_btn;
                }
                if ($cancelReservation !== TRUE) {
                    if (date('Y-m-d', strtotime($lesson['start_time'])) === date('Y-m-d')) {
                        $button .= $today_live_btn;
                    } else {
                        $button .= $wait_live_btn;
                    }
                }
            }
        }
        return $button;
    }

    /**
     * 课程绿色按钮
     * @param $lesson
     * @return string
     */
    public function lessonGreenButtonVal($lesson)
    {
        $button = '';
        $zdtalkUrl = $download = '';
        $now_time = time();
        $sevenDayAgo = strtotime($lesson['start_time'] . '-7 day');
        $twoHourAgo = strtotime($lesson['start_time'] . '-2 hour');
        $lesson_title = $lesson['title'];
        if ($lesson['is_reservation'] === TRUE) {
            //已预约
            if (intval($lesson['reservation_status']) === 1) {
                //已结束
                if ($now_time > strtotime($lesson['end_time']) + 60 * 90 || $lesson['start_time'] < date('Y-m-d 00:00:00')) {
                    if ($lesson['is_record'] == 1) {
                        $exist_course = 0;
                        if (!empty($lesson['record_link'])) {
                            $exist_course = 1;
                        }
                        if (intval($lesson['record_download']) === 1) {
                            $button .= '<a href="javascript:;" class="button_lineGreen mr05 cpss_download lesson_download" data-exist="' . $exist_course . '" ga-type="点击下载" ga-title="' . $lesson_title . '">下载</a>';
                        }
                        $button .= '<a href="javascript:;" class="button_green w90 lesson_review" data-exist="' . $exist_course . '" ga-type="点击回顾" ga-title="' . $lesson_title . '">回顾</a>';
                    } else {
                        $button .= '<a href="javascript:;" class="btn_none_class flR">无录播课</a>';
                    }
                } else {
                    $title = '进入教室';
                    $classroomClass = 'not_look_room';
                    $look_room_time = date('m月d日H点', $twoHourAgo);
                    if ($lesson['zdtalk']['enable'] === TRUE) {
                        $classroomClass = 'look_room';
                        $classroomClass .= ' zdtalk';
                        $zdtalkUrl = $lesson['zdtalk']['href'];
                        $download = get_options('zdtalk_download');
                    } elseif ($now_time > $twoHourAgo) {
                        $classroomClass = 'look_room';
                    }
                    $button .= '<a href="javascript:;" data-zdtalk="' . $zdtalkUrl . '" data-download="' . $download . '" data-look_time="' . $look_room_time . '" class="button_green mr05 ' . $classroomClass . '" ga-type="点击' . $title . '" ga-title="' . $lesson_title . '">' . $title . '</a>';
                    if (intval($lesson['check_in_status']) === 1) {
                        $button .= '<a href="javascript:;" class="button_Hui curD">已报到</a>';
                    } else {
                        $button .= '<a href="javascript:;" class="button_lineGreen report_lesson" ga-type="点击报到" ga-title="' . $lesson_title . '">报到</a>';
                    }
                    if (intval($lesson['check_in_status']) !== 1) {
                        if ($now_time < $twoHourAgo) {
                            $button .= '<p class="cpss_cancel_text cancel_reservation_lesson mr20 mt10" ga-type="点击取消预约" ga-title="' . $lesson_title . '">取消预约</p>';
                        }
                    }
                }
            } else {
                if ($now_time > $twoHourAgo) {
                    $button .= '<a href="javascript:;" class="button_Hui curD">预约</a>';
                    $button .= '<p class="fz12 color_aaa mt05">开课前两小时以内不可预约</p>';
                } else {
                    $reservationClass = 'reservation_lesson';
                    $reservation_time = date('m月d日 H:s', $sevenDayAgo);
                    if ($now_time < $sevenDayAgo) {
                        $reservationClass = 'not_reservation_lesson';
                    }
                    if (intval($lesson['max_num']) > 0 && intval($lesson['lesson_reservation_count']) >= intval($lesson['max_num'])) {
                        //预约人数已满
                        $button .= '<a href="javascript:;" class="button_Hui mr05 curD">已满</a>';
                    } else {
                        $button .= '<a href="javascript:;" data-reservation_time="' . $reservation_time . '" class="button_green mr05 ' . $reservationClass . '" ga-type="点击预约" ga-title="' . $lesson_title . '">预约</a>';
                    }
                    if ($now_time < $sevenDayAgo) {
                        $button .= '<p class="fz12 color_4bb866 mt05">' . $reservation_time . '可预约</p>';
                    }
                }
            }
        }
        return $button;
    }

    /**
     * 某一天的直播课表
     * @param $uid
     * @param $day
     * @return array
     */
    public function liveScheduleDay($uid, $day)
    {
        $result = [];
        $goods_status = $this->goods_model->getUserGoodsStatus($uid);
        if ($goods_status['hasExpire'] === TRUE) {
            $goods_status['selectCourse'] = $this->userIsSelectCourse($uid);
            if ($goods_status['selectCourse'] === TRUE) {
                $data = $this->userNowDaySchedule($uid, $day);
                if (!empty($data)) {
                    $result = array_merge($result, $data);
                }
            }
        }
        $result = array_merge($result, $goods_status);
        return $result;
    }

    /**
     * 一个月内哪天有直播课
     * @param $uid
     * @param $year_month
     * @return array
     */
    public function liveUserMonthlyCalendar($uid, $year_month)
    {
        $result = [];
        $firstDay = $year_month . '-01 00:00:00';
        $lastDay = date('Y-m-t 23:59:59', strtotime($year_month));
        //验证是否有课
        $reservationLesson = $this->lesson_model->getUserScheduleLessonDetial($uid, [
            'susl.reservation_status' => 1,
            'ssl.start_time>' => $firstDay,
            'ssl.start_time<' => $lastDay
        ], [
            'ssl.start_time',
            'ssl.end_time',
        ], ['ssl.start_time ASC']);
        if (!empty($reservationLesson)) {
            foreach ($reservationLesson as $rlv) {
                $few = date('j', strtotime($rlv['start_time']));
                if (!isset($result[$few])) {
                    $result[$few] = 0;
                }
            }
        }
        $ableSchedule = $this->schedule_model->getUserAbleScheduleArr($uid);
        if (!empty($ableSchedule)) {
            $where = [
                'ssl.start_time>' => $firstDay,
                'ssl.start_time<' => $lastDay,
                'ss.class_mode' => 1,
                'ss.curricular_system' => 1,
                'where_in' => ['ss.id', $ableSchedule]
            ];
            //用户存在常规课去除精品课、体验课显示
            $this->load->model('public/plan_model');
            $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
            if (!empty($planHave)) {
                $where['_string'] = '(ss.business_type NOT IN (2, 5))';
            }
            $lesson = $this->lesson_model->getScheduleLessonDetail($where, [
                'ssl.start_time',
                'ssl.end_time'
            ], ['ssl.start_time ASC']);
            if (!empty($lesson)) {
                foreach ($lesson as $lv) {
                    $few = date('j', strtotime($lv['start_time']));
                    if (!isset($result[$few])) {
                        $result[$few] = 0;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 用户是否选课
     * @param $uid
     * @return bool
     */
    public function userIsSelectCourse($uid)
    {
        $result = FALSE;
        //用户存在常规课去除精品课、体验课验证
        $_string = NULL;
        $this->load->model('public/plan_model');
        $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
        if (!empty($planHave)) {
            $_string = '(ss.business_type NOT IN (2, 5))';
        }
        $query = parent::$main_db->from('sty_user_schedule as sus')
            ->join('sty_schedule as ss', 'ss.id = sus.schedule_id', 'left')
            ->where([
                'sus.uid' => $uid,
                'sus.is_del' => 0,
                'ss.is_del' => 0
            ]);
        if (!empty($_string)) {
            $query->where($_string);
        }
        $joinScheduleNum = $query->count_all_results();
        if ($joinScheduleNum > 0) {
            $result = TRUE;
        } else {
            $reservationNum = $this->lesson_model->userReservationLessonCount($uid);
            if ($reservationNum > 0) {
                $result = TRUE;
            }
        }
        return $result;
    }

    /**
     * 选择主修
     * @param $uid
     * @return array
     */
    public function selectCourseMajor($uid)
    {
        $result = ['goSelectPlan' => FALSE];
        //是否报名主修课商品
        $this->load->model('public/plan_model');
        $this->load->model('public/plan_stage_model');
        $this->load->model('public/member_model');
        $majorGoods = $this->goods_model->getUserGoodsStatus($uid, 1);
        if ($majorGoods['hasGoods'] === TRUE) {
            //是否已选择计划
            $have_plan = [];
            $planIdMap = [];
            $current_plan_id = 0;
            $user_message = $this->member_model->getUserMessage($uid);
            $plan_all = $this->plan_model->getUserHavePlan($uid);
            if (!empty($plan_all)) {
                foreach ($plan_all as $pv) {
                    if (!in_array(intval($pv['business_type']), [2, 5])) {
                        $have_plan[] = $pv;
                    }
                }
                if (empty($have_plan)) {
                    $have_plan = $plan_all;
                }
                foreach ($have_plan as $hv) {
                    if (intval($user_message['current_plan_id']) === intval($hv['plan_id'])) {
                        $current_plan_id = intval($hv['plan_id']);
                        break;
                    }
                    $planIdMap[] = intval($hv['plan_id']);
                }
                if ($current_plan_id === 0) {
                    if (in_array(1, $planIdMap) || in_array(2, $planIdMap)) {
                        $result['goSelectPlan'] = TRUE;
                        $result['planIdMap'] = $planIdMap;
                    } else {
                        $current_plan_id = intval($have_plan[0]['plan_id']);
                    }
                }
                if ($result['goSelectPlan'] === FALSE) {
                    $data = $this->plan_stage_model->getSelectPlanStageData($uid, $current_plan_id);
                    if (!empty($data)) {
                        $result = array_merge($result, $data);
                        if ($majorGoods['allUnActivate'] === FALSE) {
                            $this->load->model('public/goods_model');
                            $result['remain_count'] = $this->goods_model->getUserAbleNum($uid, 1);
                        } else {
                            //全部未激活
                            $result['remain_count'] = -2;
                        }
                    }
                }
                $result['have_plan'] = $have_plan;
                $result['current_plan_id'] = $current_plan_id;
            }
        }
        $result['hasGoods'] = $majorGoods['hasGoods'];
        return $result;
    }

    /**
     * 切换计划
     * @param $uid
     * @param $plan_id
     * @return array
     */
    public function switchPlan($uid, $plan_id)
    {
        $result = [];
        $this->load->model('public/plan_stage_model');
        $majorGoods = $this->goods_model->getUserGoodsStatus($uid, 1);
        if ($majorGoods['hasGoods'] === TRUE) {
            $data = $this->plan_stage_model->getSelectPlanStageData($uid, $plan_id);
            if (!empty($data)) {
                $result = array_merge($result, $data);
                if ($majorGoods['allUnActivate'] === FALSE) {
                    $this->load->model('public/goods_model');
                    $result['remain_count'] = $this->goods_model->getUserAbleNum($uid, 1);
                } else {
                    //全部未激活
                    $result['remain_count'] = -2;
                }
            }
        }
        $result['hasGoods'] = $majorGoods['hasGoods'];
        return $result;
    }

    /**
     * 阶段课程路由
     */
    public function scheduleRoutes($uid, $handle, array $params)
    {
        $result = [];
        $plan_id = $params['plan_id'];
        $plan_stage_id = $params['plan_stage_id'];
        $class_mode = $params['class_mode'];
        $class_schedule_type = get_options('class_schedule_type');
        $mode = 1;
        if (isset($class_schedule_type[$class_mode])) {
            $mode = $class_schedule_type[$class_mode];
        }
        $majorGoods = $this->goods_model->getUserGoodsStatus($uid, 1);
        if ($majorGoods['hasGoods'] === TRUE) {
            $result['hasPlanStage'] = FALSE;
            $hasPlanStage = $this->validation->validate('planAndStage', [
                'uid' => $uid,
                'plan_id' => $plan_id,
                'plan_stage_id' => $plan_stage_id
            ]);
            if ($hasPlanStage === TRUE) {
                $data = $this->$handle($uid, [
                    'plan_id' => $plan_id,
                    'plan_stage_id' => $plan_stage_id,
                    'mode' => $mode,
                    'time_type' => $params['time_type'],
                    'course_status' => $params['course_status']
                ]);
                if (!empty($data)) {
                    $result = array_merge($result, $data);
                }
                $result['hasPlanStage'] = $hasPlanStage;
            }
        }
        $result['hasGoods'] = $majorGoods['hasGoods'];
        return $result;
    }

    /**
     * 阶段课程信息
     * @param $uid
     * @param $params
     * @return array
     */
    protected function schedule_main($uid, $params)
    {
        $this->load->model('public/plan_model');
        $this->load->model('public/plan_stage_model');
        //计划 计划阶段信息
        $result = [
            'plan_id' => $params['plan_id'],
            'plan_stage_id' => $params['plan_stage_id'],
            'plan_name' => '',
            'plan_stage_name' => ''
        ];
        $plan = $this->plan_model->getPlanCacheData($params['plan_id']);
        if (!empty($plan)) $result['plan_name'] = $plan['name'];
        $plan_stage = $this->plan_stage_model->getStageInfo($params['plan_stage_id']);
        if (!empty($plan_stage)) $result['plan_stage_name'] = $plan_stage['name'];
        $data = $this->schedule_lable($uid, $params);
        if (!empty($data)) {
            $result = array_merge($result, $data);
        }
        return $result;
    }

    protected function schedule_lable($uid, $params)
    {
        $result = [];
        if (intval($params['mode']) === 1) {
            $result['class_time'] = $this->common_model->getCategoryVal(get_options('sys_category', 'class_time'));
            $result['class_status'] = [1 => '已经开课', 2 => '即将开课'];
        }
        $result['time_type'] = intval($params['time_type']);
        $result['course_status'] = intval($params['course_status']);
        $data = $this->schedule_data($uid, $params);
        if (!empty($data)) {
            $result = array_merge($result, $data);
        }
        return $result;
    }

    /**
     * 阶段课程相关数据
     * @param $uid
     * @param $params
     * @return array
     */
    // todo yoyo
    protected function schedule_data($uid, $params)
    {
        $result = [];
        $now_time = date('Y-m-d H:i:s');
        $label_all = $this->common_model->getCategoryVal(get_options('sys_category', 'label'));
        //已加入的阶段课程
        $join_schedule_ids = $this->schedule_model->getUserScheduleIds($uid);
        $where = [
            'sssi.plan_id' => $params['plan_id'],
            'sssi.plan_stage_id' => $params['plan_stage_id'],
            'ss.class_mode' => $params['mode'],
            'ss.curricular_system' => 1,
            'ss.status' => 1
        ];
        if (intval($params['mode']) === 1) {
            if (intval($params['time_type']) > 0) {
                $where['ss.school_time'] = intval($params['time_type']);
            }
            if (intval($params['course_status']) === 1) {
                $where['ss.start_time<'] = $now_time;
            } elseif (intval($params['course_status']) === 2) {
                $where['ss.start_time>'] = $now_time;
            }
            $where['ss.end_time>'] = $now_time;
        }
        $this->load->model('public/plan_model');
        $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
        if (!empty($planHave)) {
            $where['_string'] = '(ss.business_type NOT IN (2, 5))';
        }
        $schedule_lab = $this->schedule_model->getScheduleStageDetial($where, [
            'ss.name',
            'ss.class_num',
            'ss.start_time',
            'ss.end_time',
            'ss.class_start_time',
            'ss.class_end_time',
            'ss.target',
            'ss.max_curriculum_num',
            'ss.class_mode',
            'ss.enable_zdtalk',
            'ss.qq_group',
            'ss.join_people_number',
            'ss.label',
            'ss.curricular_system'
        ], ['ss.start_time DESC']);
        if (!empty($schedule_lab)) {
            foreach ($schedule_lab as $k => $v) {
                $lessonCount = 0;
                $lessonFinishedCount = 0;
                //主讲老师
                $schedule_lab[$k]['teachers'] = $this->schedule_model->getScheduleTeachers($v['id']);
                //使用教材
                $schedule_lab[$k]['use_books'] = '无';
                $text_books = $this->schedule_model->getTextbooks($v['id']);
                $textBooks = array();
                if (!empty($text_books)) {
                    foreach ($text_books as $tv) {
                        $textBooks[] = $tv['name'];
                    }
                    $schedule_lab[$k]['use_books'] = implode('、', $textBooks);
                }
                $schedule_lab[$k]['prompt_info'] = '';
                $schedule_lab[$k]['prompt_button'] = '加入课程';
                $schedule_lab[$k]['prompt_class'] = 'button_blue flR mt08 join_schedule';
                if (intval($params['mode']) === 1) {
                    $schedule_count = $this->schedule_model->joinScheduleCount($v['id']);
                    $remain_num = intval($v['max_curriculum_num']) - intval($schedule_count);
                    $schedule_lab[$k]['remain_num'] = $remain_num;
                    //课程标签
                    if($v['curricular_system'] == 1){
                        $schedule_lab[$k]['label'] = explode(',',$v['label']);
                    }

                    //上课日期
                    $schedule_lab[$k]['class_date'] = '';
                    if (intval($v['start_time']) > 0 && intval($v['end_time']) > 0) {
                        $end_time = date('Y-m-d 00:00:00',strtotime(substr($v['end_time'],0,10) .'+1 day'));
                        $cycle = $this->diffDate($end_time,$v['start_time']);
                        $m = 0;
                        $str = "（学习周期";
                        if(intval($cycle['y'])>0){
                            $m = intval($cycle['y'])*12;
                        }
                        $m = $m+$cycle['m'];
                        if($m) $str .= $m.'个月';

                        if($cycle['d']) {
                            if($m) $str .= '零';
                            $str .= $cycle['d'].'天';
                        }

                        $str .="）";
                        $schedule_lab[$k]['class_date'] = date('Y/m/d', strtotime($v['start_time'])) . '-' . date('Y/m/d', strtotime($v['end_time'])).$str;
                    }
                    //上课时间
                    $schedule_lab[$k]['class_time'] = '';
                    $class_week_cycle = $this->schedule_model->getScheduleWeekCycle($v['id']);
                    if (!empty($class_week_cycle)) {
                        $schedule_lab[$k]['class_time'] = implode('、', $class_week_cycle);
                        $count = count($class_week_cycle);
                    }
                    if (intval($v['class_start_time']) > 0 && intval($v['class_end_time']) > 0) {
                        $time = $this->diffDate($v['class_start_time'],$v['class_end_time']);
                        $str1 = "（";
                        if(intval($count)){
                            $str1 .= $count .'次课/周';
                        }
                        if(intval($count) && intval($time['h'])){
                            $str1 .= '，';
                        }
                        if(intval($time['i'])){
                            $time['h'] = intval($time['h'])+$time['i']/60;
                        }else{
                            $time['h'] = intval($time['h']);
                        }
                        if($time['h']){
                            $str1 .=$time['h'] .'小时/次';
                        }
                        $str1 .="）";
                        $schedule_lab[$k]['class_time'] .= ' ' . date('H:i', strtotime($v['class_start_time'])) . '-' . date('H:i', strtotime($v['class_end_time'])).$str1;
                    }
                    //上课进度
                    $schedule_lab[$k]['progress'] = '';
                    if ($v['start_time'] >= date('Y-m-d H:i:s')) {
                        $schedule_lab[$k]['progress'] = '即将开课/共'.intval($v['class_num']).'次课';
                    } else {
                        $lessonCount = intval($v['class_num']);
                        $lessonFinishedCount = $this->lesson_model->getScheduleLessonCount($v['id'], TRUE);
                        $schedule_lab[$k]['progress'] = '【' . $lessonFinishedCount . '/' . $lessonCount . '】';
                        $latest = parent::$main_db->from('sty_schedule_lesson')
                            ->select('id, name')
                            ->where([
                                'schedule_id' => $v['id'],
                                'start_time >' => date('Y-m-d H:i:s'),
                                'is_del' => 0
                            ])->order_by('start_time ASC')
                            ->limit(1)->get()->row_array();
                        if (empty($latest)) {
                            $latest['name'] = '没有新课了~';
                        }
                        $schedule_lab[$k]['progress'] = $latest['name'] . $schedule_lab[$k]['progress'];
                    }

//                    if ($remain_num > 0 && $remain_num <= 10) {
//                        $schedule_lab[$k]['prompt_info'] = '剩 ' . $remain_num . ' 个名额，抓紧时间加入哦~';
//                    }
                    $half = $lessonCount/2;
                    if($lessonFinishedCount >= $half && $v['start_time'] < date('Y-m-d H:i:s')){
                          $schedule_lab[$k]['prompt_info'] = '课程进度已过半，不建议现在加入学习哦~';
                    }

                    if (!in_array($v['id'], $join_schedule_ids) && $remain_num <= 0) {
                        $schedule_lab[$k]['prompt_button'] = '招生已满';
                        $schedule_lab[$k]['prompt_class'] = 'button_Hui_e5e5e5 flR mt08 curD';
                    }
                }else{

                    $schedule_lab[$k]['remain_num'] = -1;
                }
                //提示信息
                if (in_array($v['id'], $join_schedule_ids)) {
                    $schedule_lab[$k]['prompt_info']  = '';
                    $schedule_lab[$k]['prompt_button'] = '已经加入';
                    $schedule_lab[$k]['prompt_class'] = 'button_line_blue flR mt08 joined_schedule';
                }
            }
            $result['schedule_list'] = $schedule_lab;
            $result['label_all'] = $label_all;
        }
        $result['mode'] = intval($params['mode']);
        return $result;
    }
    function diffDate($date1,$date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = date_diff($datetime1, $datetime2);
//        $interval = $datetime1->diff($datetime2);
        $time['y']         = $interval->format('%Y');
        $time['m']         = $interval->format('%m');
        $time['d']         = $interval->format('%d');
        $time['a']         = $interval->format('%a');    // 两个时间相差总天数
        $time['h']         = $interval->format('%H');
        $time['i']         = $interval->format('%i');
        $time['s']         = $interval->format('%s');
        return $time;
    }

    /**
     * 选课中预约制路由
     * @param $uid
     * @param $handle
     * @return array
     */
    public function selectCourseReservationRoutes($uid, $handle, array $params)
    {
        $result = [];
        $curricular = $params['curricular'];
        $page = $params['page'] > 0 ? $params['page'] : 1;
        //验证是否有购买该课程体系商品
        $curricular_system = get_options('curricular_system');
        if (!isset($curricular_system[$curricular]) || $curricular_system[$curricular] == 1) {
            return $result;
        }
        $catgroy = $curricular_system[$curricular];
        $reservationGoods = $this->goods_model->getUserGoodsStatus($uid, $catgroy);
        if ($reservationGoods['hasGoods'] === TRUE) {
            $data = $this->$handle($uid, [
                'catgroy' => $catgroy,
                'curricular' => $curricular,
                'basic' => $params['basic'],
                'content_cat' => $params['content_cat'],
                'page' => $page
            ]);
            if (!empty($data)) {
                $result = array_merge($result, $data);
            }
        }
        $result['hasGoods'] = $reservationGoods['hasGoods'];
        $result['curricular_zh'] = get_options('curricular_system_zh', $curricular);
        return $result;
    }

    /**
     * 预约制主体
     * @param $uid
     * @param $params
     * @return array
     */
    protected function select_reservation($uid, $params)
    {
        $result = [];
        //有效期和剩余次数
        $result['used'] = $this->goods_model->getUserGoodsExpireAndNumInfo($uid, $params['catgroy']);
        $result['used']['detail_href'] = '/purchased/curricular/' . $params['curricular'];
        if ($params['catgroy'] !== 5) {
            //内容分类
            $result['curricular_category'] = $this->common_model->getScheduleCategory(get_options('curricular_category', $params['curricular']));
        }
        //预约课表
        $data = $this->reservation_main($uid, $params);
        if (!empty($data)) {
            $result = array_merge($result, $data);
        }
        return $result;
    }

    /**
     * 预约课表
     * @param $uid
     * @param $params
     * @return array
     */
    protected function reservation_main($uid, $params)
    {
        $result = [];
        $limit = 10;
        $offset = ($params['page'] - 1) * $limit;
        //适合基础
        $result['fit_basic'] = $this->common_model->getCategoryVal(get_options('sys_category', 'fit_basic'));
        //老师map
        $teacher = [];
        $where = [
            'ss.business_type' => 1,
            'ss.curricular_system' => $params['catgroy'],
            'ssl.business_type' => 1,
            'ssl.curricular_system' => $params['catgroy'],
            'ssl.end_time>' => date('Y-m-d H:i:s', time() - 60 * 90),
            'ss.status' => 1
        ];
        if (intval($params['basic']) > 0) {
            $where['ss.basic'] = $params['basic'];
        }
        if ($params['catgroy'] !== 5 && intval($params['content_cat']) > 0) {
            $where['ss.content_cat_second'] = $params['content_cat'];
        }
        $totalCount = $this->schedule_model->getScheduleJoinLessonCount($where);
        if ($totalCount > 0) {
            $reservation_list = $this->lesson_model->getScheduleLessonDetail($where, [
                'ssl.name',
                'ssl.alias',
                'ssl.teacher_id',
                'ssl.is_record',
                'ssl.record_link',
                'ssl.record_download',
                'ssl.max_num',
                'ssl.start_time',
                'ssl.end_time',
                'ss.name as schedule_name',
                'ss.basic',
                'ss.room_id',
                'ss.room_name',
                'ss.room_pwd',
                'ss.enable_zdtalk'
            ], ['ssl.start_time ASC', 'ssl.id ASC'], [$offset, $limit]);
            if (!empty($reservation_list)) {
                foreach ($reservation_list as $key => $val) {
                    $lesson = $val;
                    $lesson['is_reservation'] = TRUE;
                    $lesson['reservation_status'] = 0;
                    $lesson['check_in_status'] = 0;
                    $user_report = $this->lesson_model->getUserScheduleLessonInfo($uid, $val['id']);
                    if (!empty($user_report)) {
                        if (intval($user_report['reservation_status']) === 1) {
                            $lesson['reservation_status'] = 1;
                            if (intval($user_report['check_in_status']) === 1) {
                                $lesson['check_in_status'] = 1;
                            }
                        }
                    }
                    $lesson['lesson_reservation_count'] = $this->lesson_model->getReservationCount($val['id']);
                    $lesson['zdtalk'] = $this->schedule_model->enableZDTalk($uid, [
                        'enable_zdtalk' => $val['enable_zdtalk'],
                        'schedule_id' => $val['schedule_id']
                    ], $val['id']);
                    $lesson['title'] = !empty($val['alias']) ? $val['alias'] : $val['name'];
                    if (!empty($val['schedule_name'])) {
                        $lesson['title'] = $val['schedule_name'] . '：' . $lesson['title'];
                    }
                    $lesson['class_time'] = $this->common_model->formatRecentDate($val['start_time'], 'm月d日');
                    $lesson['class_date'] = date('H:i', strtotime($val['start_time'])) . '-' . date('H:i', strtotime($val['end_time']));
                    $lesson['class_week'] = get_options('week_day', date('w', strtotime($val['start_time'])));
                    if (!empty($lesson['class_week'])) {
                        $lesson['class_week'] = '周' . $lesson['class_week'];
                    }
                    $lesson['basic_val'] = intval($val['is_record']) === 1 ? '有录播' : '无录播';
                    if (intval($val['basic']) > 0) {
                        if (!empty($result['fit_basic'][$val['basic']])) {
                            $lesson['basic_val'] = $result['fit_basic'][$val['basic']] . '，' . $lesson['basic_val'];
                        }
                    }
                    $lesson['teacher_name'] = '';
                    $lesson['teacher_avatar'] = '';
                    if (intval($val['teacher_id']) > 0) {
                        if (!isset($teacher[$val['teacher_id']])) {
                            $lesson_teacher = $this->schedule_model->getTeacherInfo($val['teacher_id']);
                            if (!empty($lesson_teacher)) {
                                $teacher[$val['teacher_id']] = $lesson_teacher;
                            }
                        }
                        if (!empty($teacher[$val['teacher_id']])) {
                            $lesson['teacher_name'] = $teacher[$val['teacher_id']]['name'];
                            $lesson['teacher_avatar'] = $teacher[$val['teacher_id']]['avatar'];
                        }
                    }
                    if (intval($val['max_num']) > 0) {
                        $lesson['max_limit'] = $val['max_num'];
                    } else {
                        $lesson['max_limit'] = '不限';
                    }
                    $lesson['button'] = $this->lessonGreenButtonVal($lesson);
                    $lesson_list[$val['id']] = $lesson;
                }
                $result['reservation_list'] = $lesson_list;
            }
        }
        $result['total_count'] = $totalCount;
        $result['total_page'] = intval(ceil($result['total_count'] / $limit));
        $result['page_limit'] = $limit;
        $result['current_page'] = $params['page'];
        $result['basic'] = $params['basic'];
        $result['catgroy'] = $params['catgroy'];
        $result['curricular'] = $params['curricular'];
        $result['curricular_zh'] = get_options('curricular_system_zh', $params['curricular']);
        return $result;
    }

    /**
     * 我的课程（主修课路由）
     * @param $uid
     * @param $handle
     * @return array
     */
    public function myCourseMajorRoutes($uid, $handle)
    {
        $result = [];
        //是否报名主修课商品
        $majorGoods = $this->goods_model->getUserGoodsStatus($uid, 1);
        if ($majorGoods['hasGoods'] === TRUE) {
            $class_type = get_options('myMajorClassType');
            $type = $this->input->get('type', TRUE);
            $params = ['type' => $type];
            $data = $this->$handle($uid, $params);
            if (!empty($data)) {
                $result = array_merge($result, $data);
            }
            $result['curricular_class'] = 'curricular_major_type';
            $result['class_type'] = $class_type;
        }
        $result['hasGoods'] = $majorGoods['hasGoods'];
        return $result;
    }

    /**
     * 获取默认的主修课分类
     * 即有课状态下，默认显示正在进行分类下课程。
     * 如果正在进行下没有课程，则默认显示即将开始下课程，如果即将开始下没有课程，显示已经结束。
     * 如果三个分类下都没有课程，则默认显示正在进行。
     * @param $uid
     * @param array $data 通过data来判断
     * @param $excludeJP bool 是否排除精品课 体验课
     * @return string
     */
    public function getDefaultMajorCate($uid, $data = [], $excludeJP = FALSE)
    {
        if (!empty($data)) {
            $result = $data['result'];
        } else {
            $result = $this->getUserMajorCateCount($uid, $excludeJP);
        }
        if (empty($result)) {
            return 'doing';
        } else {
            return $result[0]['type'];
        }
    }

    /**
     * 获取用户主修课不同分类（正在进行，已经结束，即将开始）下是否有课
     * @param $uid
     * @param bool $excludeJP
     */
    public function getUserMajorCateCount($uid, $excludeJP = FALSE)
    {
        $now = date('Y-m-d H:i:s');
        $cates = [
            'doing' => ['ss.start_time<' => $now, 'ss.end_time>' => $now],
            'tostart' => ['ss.start_time>' => $now],
            'done' => ["(ss.end_time<{$now} or ss.class_mode=2)"]
        ];
        $subQueries = [];
        foreach ($cates as $cate => $where) {
            parent::$main_db->select("ss.id, '{$cate}' as type")
                ->from('sty_user_schedule as sus')
                ->join('sty_schedule as ss', 'ss.id = sus.schedule_id', 'left')
                ->where([
                    'sus.uid' => $uid,
                    'ss.curricular_system' => 1,
                    'ss.is_del' => 0,
                    'sus.is_del' => 0
                ])->where($where)->limit(1);
            if ($excludeJP) {
                parent::$main_db->where('(ss.business_type IN (1,3,4))');
            }
            $subQueries[] = '(' . parent::$main_db->get_compiled_select() . ')';
        }
        $sql = implode(' UNION ', $subQueries);
        return parent::$main_db->query($sql)->result_array();
    }

    /**
     * 我的课程（主修）
     * @param $uid
     * @param $params
     * @return array
     */
    protected function my_course_major($uid, $params)
    {
        $result = [];
        //是否排除精品课 体验课
        $excludeJP = FALSE;
        $now_time = date('Y-m-d H:i:s');
        $where['ss.curricular_system'] = 1;
        $where['_string'] = '1=1';
        //用户存在常规课去除精品课、体验课显示
        $this->load->model('public/plan_model');
        $planHave = $this->plan_model->getUserHavePlan($uid, ['_string' => '(sp.business_type NOT IN (2, 5))']);
        if (!empty($planHave)) {
            $where['_string'] .= ' AND (ss.business_type NOT IN (2, 5))';
            $excludeJP = TRUE;
        }
        $class_type = get_options('myMajorClassType');
        $majorCate = $this->getUserMajorCateCount($uid, $excludeJP);
        $defaultType = $this->getDefaultMajorCate($uid, ['result' => $majorCate]);
        if (!isset($class_type[$params['type']])) {
            $params['type'] = $defaultType;
        }
        if ($params['type'] === 'doing') {
            $where['ss.class_mode'] = 1;
            $where['ss.start_time<'] = $now_time;
            $where['ss.end_time>'] = $now_time;
        } elseif ($params['type'] === 'done') {
            $where['_string'] .= " AND (ss.class_mode = 2 OR ss.end_time < '" . $now_time . "')";
        } elseif ($params['type'] === 'tostart') {
            $where['ss.class_mode'] = 1;
            $where['ss.start_time>'] = $now_time;
        }
        $this->load->model('public/member_model');
        $user_message = $this->member_model->getUserMessage($uid);
        $schedule_list = [];
        $schedule = $this->schedule_model->getUserScheduleDetial($uid, $where, [
            'ss.name',
            'ss.class_mode',
            'ss.class_num',
            'ss.start_time',
            'ss.end_time',
            'ss.class_start_time',
            'ss.class_end_time',
            'ss.qq_group',
            'ss.business_type'
        ], ['sus.create_time DESC']);
        if (!empty($schedule)) {
            foreach ($schedule as $val) {
                $teacher_name = [];
                $teacher = $this->schedule_model->getScheduleTeachers($val['id']);
                if (!empty($teacher)) {
                    foreach ($teacher as $tv) {
                        if (!empty($tv['name']) && !in_array($tv['name'], $teacher_name)) {
                            $teacher_name[] = $tv['name'];
                        }
                    }
                }
                $val['teacher_val'] = '';
                if (!empty($teacher_name)) {
                    $val['teacher_val'] = implode('、', $teacher_name);
                }
                $val['is_complete'] = 0;
                $val['is_end'] = 0;
                $val['lessonCount'] = intval($val['class_num']);
                $val['reportCount'] = $this->lesson_model->userReportCount($uid, $val['id']);
                if (intval($val['reportCount']) >= intval($val['lessonCount'])) {
                    $val['is_complete'] = 1;
                }
                $val['is_finish'] = 0;
                if (intval($val['class_mode']) === 1) {
                    $val['class_time'] = date('m/d', strtotime($val['start_time'])) . '-' . date('m/d', strtotime($val['end_time']));
                    $val['class_date'] = date('H:i', strtotime($val['class_start_time'])) . '-' . date('H:i', strtotime($val['class_end_time']));
                    $week_day = $this->schedule_model->getScheduleWeekCycle($val['id']);
                    $val['week_val'] = '';
                    if (!empty($week_day)) {
                        foreach ($week_day as $day) {
                            $val['week_val'] .= "周$day ";
                        }
                        $val['week_val'] = rtrim($val['week_val']);
                    }
                    if ($now_time < $val['end_time']) {
                        $val['is_finish'] = 1;
                    }
                }
                $val['button_type'] = 'expired';
                $joinSchedule = $this->schedule_model->viewUserPlanStageJoinSchedule([
                    'uid' => $uid,
                    'schedule_id' => $val['id']
                ], ['plan_id', 'plan_stage_id', 'unlimit_expire', 'expire'], ['expire DESC']);
                $plan_id = $plan_stage_id = 0;
                $toActivate = FALSE;
                if (!empty($joinSchedule)) {
                    foreach ($joinSchedule as $js) {
                        if (intval($js['unlimit_expire']) === 1 || $js['expire'] > $now_time) {
                            $plan_id = $js['plan_id'];
                            $plan_stage_id = $js['plan_stage_id'];
                            break;
                        }
                        if (intval($js['unlimit_expire']) === 0 && empty($js['expire'])) {
                            $toActivate = TRUE;
                        }
                    }
                    if (intval($plan_id) && intval($plan_stage_id)) {
                        $val['button_type'] = 'learn';
                    } elseif ($toActivate === TRUE) {
                        $val['button_type'] = 'activate';
                    }
                }

                if (!empty($user_message) && intval($user_message['user_level']) > 1) {
                    if (in_array(intval($val['business_type']), [1, 2])) {
                        $val['able_del_schedule'] = TRUE;
                    }
                }
                $schedule_list[$val['id']] = $val;
            }
        }
        $result['ableCourse'] = FALSE;
        if (empty($schedule_list)) {
            if (!empty($majorCate)) {
                //其他分类下有课，当前分类下没课
                $result['ableCourse'] = TRUE;
            }
        }
        $result['schedule_list'] = $schedule_list;
        $result['current_type'] = $params['type'];
        $result['current_curricular'] = 'major';
        $result['curricular_zh'] = get_options('curricular_system_zh', $result['current_curricular']);
        return $result;
    }

    /**
     * 我的课表预约制类型课路由
     * @param $uid
     * @param $handle
     * @return array
     */
    public function myReservationRoutes($uid, $handle, array $params)
    {
        $result = [];
        $curricular = $params['curricular'];
        $type = $params['type'];
        //验证是否有购买该课程体系商品
        $curricular_system = get_options('curricular_system');
        if (!isset($curricular_system[$curricular]) || $curricular_system[$curricular] == 1) {
            return $result;
        }
        $catgroy = $curricular_system[$curricular];

        $class_type = get_options('reservationClassType');
        if ($catgroy == 2) {
            $class_type = get_options('reservationOralClassType');
        }
        if (!isset($class_type[$type])) {
            if ($catgroy == 2) $type = 'reserved';
            else $type = 'done';
        }
        $params = [
            'catgroy' => $catgroy,
            'curricular' => $curricular,
            'type' => $type
        ];
        $result['curricular_class'] = 'curricular_' . $curricular . '_type';
        $result['class_type'] = $class_type;
        $reservationGoods = $this->goods_model->getUserGoodsStatus($uid, $catgroy);
        if ($reservationGoods['hasGoods'] === TRUE) {
            $data = $this->$handle($uid, $params);
            if (!empty($data)) {
                $result = array_merge($result, $data);
            }
        }
        $result['hasGoods'] = $reservationGoods['hasGoods'];
        return $result;
    }

    /**
     * 我的课程（预约制）
     * @param $uid
     * @param $params
     * @return array
     */
    protected function my_reservation($uid, $params)
    {
        $result = [];
        $where['susl.reservation_status'] = 1;
        $where['ssl.business_type'] = 1;
        $where['ssl.curricular_system'] = $params['catgroy'];
        if ($params['type'] == 'reserved') {
            $where['ssl.end_time>='] = date('Y-m-d H:i:s', time() - 60 * 90);
        }
        if ($params['type'] == 'done') {
            $where['ssl.end_time<'] = date('Y-m-d H:i:s', time() - 60 * 90);
        }
        $my_reservation_list = $scheduleInfoArr = $teacherArr = [];
        $reservation_lesson = $this->lesson_model->getUserScheduleLessonDetial($uid, $where, [
            'susl.reservation_status',
            'susl.check_in_status',
            'susl.check_in_type',
            'susl.check_in_score',
            'ssl.name',
            'ssl.alias',
            'ssl.teacher_id',
            'ssl.is_record',
            'ssl.record_link',
            'ssl.record_download',
            'ssl.start_time',
            'ssl.end_time',
            'ssl.business_type',
            'ssl.curricular_system'
        ], ['ssl.start_time ASC']);
        if (!empty($reservation_lesson)) {
            foreach ($reservation_lesson as $val) {
                $schedule_id = $val['schedule_id'];
                $teacher_id = $val['teacher_id'];
                if ($schedule_id && !isset($scheduleInfoArr[$schedule_id])) {
                    $schedule_info = $this->schedule_model->getScheduleBaseInfo($schedule_id);
                    if (!empty($schedule_info)) {
                        $scheduleInfoArr[$schedule_id] = $schedule_info;
                    }
                }
                if ($teacher_id && !isset($teacherArr[$teacher_id])) {
                    $teacher_info = $this->schedule_model->getTeacherInfo($teacher_id);
                    if (!empty($teacher_info)) {
                        $teacherArr[$teacher_id] = $teacher_info;
                    }
                }
                $val['is_reservation'] = TRUE;
                $val['room_name'] = '';
                $val['room_pwd'] = '';
                $val['enable_zdtalk'] = 0;
                $val['title'] = !empty($val['alias']) ? $val['alias'] : $val['name'];
                if (!empty($scheduleInfoArr[$schedule_id])) {
                    if (!empty($scheduleInfoArr[$schedule_id]['name'])) {
                        $val['title'] = $scheduleInfoArr[$schedule_id]['name'] . '：' . $val['title'];
                    }
                    $val['room_name'] = $scheduleInfoArr[$schedule_id]['room_name'];
                    $val['room_pwd'] = $scheduleInfoArr[$schedule_id]['room_pwd'];
                    $val['enable_zdtalk'] = $scheduleInfoArr[$schedule_id]['enable_zdtalk'];
                }
                $val['teacher_name'] = '';
                $val['teacher_avatar'] = '';
                if (!empty($teacherArr[$teacher_id])) {
                    $val['teacher_name'] = $teacherArr[$teacher_id]['name'];
                    $val['teacher_avatar'] = $teacherArr[$teacher_id]['avatar'];
                }
                $val['zdtalk'] = $this->schedule_model->enableZDTalk($uid, [
                    'enable_zdtalk' => $val['enable_zdtalk'],
                    'schedule_id' => $val['schedule_id']
                ], $val['id']);
                $val['button'] = $this->lessonBlueButtonVal($uid, $val, TRUE);
                $lesson_res = $this->lessonPrompt($val);
                $val = array_merge($val, $lesson_res);
                $my_reservation_list[] = $val;
            }
        }
        $result['ableCourse'] = FALSE;
        if (empty($my_reservation_list)) {
            $lessonCount = parent::$main_db->from('sty_user_schedule_lesson as susl')
                ->join('sty_schedule_lesson as ssl', 'ssl.id = susl.schedule_lesson_id', 'left')
                ->where([
                    'susl.uid' => $uid,
                    'susl.reservation_status' => 1,
                    'ssl.curricular_system' => $params['catgroy'],
                    'ssl.business_type' => 1,
                    'ssl.end_time>=' => date('Y-m-d H:i:s', time() - 60 * 90),
                    'ssl.is_del' => 0,
                    'susl.is_del' => 0
                ])->count_all_results();
            if ($lessonCount > 0) {
                $result['ableCourse'] = TRUE;
            }
        }
        $result['my_reservation_list'] = $my_reservation_list;
        $result['current_type'] = $params['type'];
        $result['current_curricular'] = $params['curricular'];
        $result['curricular_zh'] = get_options('curricular_system_zh', $params['curricular']);
        return $result;
    }

    /**
     * 口语测评
     * @param $uid
     * @param $lesson_id
     * @param $display TRUE 返回口语测评数据 FALSE 验证口语测评状态
     * @return array
     */
    public function oralAssessment($uid, $lesson_id, $display = TRUE)
    {
        $result = [];
        $curricular = 'oral';
        $catgroy = get_options('curricular_system', $curricular);
        $oralGoods = $this->goods_model->getUserGoodsStatus($uid, $catgroy);
        if ($oralGoods['hasGoods'] === TRUE) {
            $result['existLesson'] = FALSE;
            $lesson_val = $this->lesson_model->getScheduleLessonDetail([
                'ssl.id' => $lesson_id,
                'ss.business_type' => 1,
                'ss.curricular_system' => $catgroy,
                'ssl.business_type' => 1,
                'ssl.curricular_system' => $catgroy,
                '_string' => "ssl.start_time IS NOT NULL AND ssl.end_time IS NOT NULL"
            ], [
                'ssl.outline_id',
                'ssl.outline_lesson_id',
                'ssl.lesson_num',
                'ssl.name',
                'ssl.alias',
                'ssl.teacher_id',
                'ssl.start_time',
                'ssl.end_time',
                'ssl.order',
                'ss.name as schedule_name'
            ], [], [1]);
            if (!empty($lesson_val)) {
                if ($display === TRUE) {
                    $current = $this->oral_assessment_current($uid, $lesson_val);
                    if (!empty($current)) {
                        $result = array_merge($result, $current);
                    }
                }
                //口语测评详细
                $detail = $this->oral_assessment_detail($uid, $lesson_val['schedule_id'], $lesson_val['outline_id'], $lesson_val['lesson_num']);
                if (!empty($detail)) {
                    $result = array_merge($result, $detail);
                }
                $result['existLesson'] = TRUE;
                $result['lesson_id'] = $lesson_id;
                $result['schedule_id'] = $lesson_val['schedule_id'];
                $result['outline_id'] = $lesson_val['outline_id'];
                $result['outline_lesson_id'] = $lesson_val['outline_lesson_id'];
                $result['lesson_num'] = $lesson_val['lesson_num'];
            }
        }
        $result['hasGoods'] = $oralGoods['hasGoods'];
        return $result;
    }

    /**
     * 口语测评相关信息
     * @param $uid
     * @param $lesson
     * @return array
     */
    protected function oral_assessment_current($uid, $lesson)
    {
        $result = [];
        if (!empty($lesson)) {
            //查询大纲下所有课件
            $schedule_id = intval($lesson['schedule_id']);
            $result['current_order'] = intval($lesson['order']);
            $result['schedule_name'] = $lesson['schedule_name'];
            $result['current_title'] = $result['schedule_name'] . '：' . (!empty($lesson['alias']) ? $lesson['alias'] : $lesson['name']);
            $result['current_class_time'] = $this->common_model->formatRecentDate($lesson['start_time'], 'm月d日');
            $result['current_class_time'] .= ' ' . date('H:i', strtotime($lesson['start_time'])) . '-' . date('H:i', strtotime($lesson['end_time']));
            $result['teacher_name'] = '';
            if ($lesson['teacher_id'] > 0) {
                $teacher_info = $this->schedule_model->getTeacherInfo($lesson['teacher_id']);
                if (!empty($teacher_info)) {
                    $result['teacher_name'] = $teacher_info['name'];
                }
            }
            //口语等级
            $result['oral_level'] = $this->oral_assessment_level($uid, $schedule_id);
        }
        return $result;
    }

    /**
     * 口语等级
     * @param $uid
     * @param $schedule_id
     * @return array
     */
    protected function oral_assessment_level($uid, $schedule_id)
    {
        $result = [];
        $this->load->model('public/member_model');
        $oral_test = $this->common_model->selectObjData('sty_test_listening', [
            'field' => ['oral_level'],
            'where' => [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'update_time>' => 0
            ],
            'order' => ['id DESC'],
            'limit' => [1]
        ]);
        if (!empty($oral_test)) {
            if (intval($oral_test['oral_level']) > 0) {
                $result['flag'] = '口语等级';
                $result['level'] = get_options('oral_level', $oral_test['oral_level']);
            }
        } else {
            $user_msg = $this->member_model->getUserMessage($uid);
            if (!empty($user_msg)) {
                if (intval($user_msg['student_power']) > 0) {
                    $result['flag'] = '口语自评等级';
                    $result['level'] = get_options('oral_level', $user_msg['student_power']);
                }
            }
        }
        return $result;
    }

    /**
     * 口语测评详细信息
     * @param $uid
     * @param $schedule_id
     * @param $outline_id
     * @param $lesson_num
     * @return array
     */
    public function oral_assessment_detail($uid, $schedule_id, $outline_id, $lesson_num)
    {
        $result = $lessson = [];
        //查询是否已通过测评
        $complete_time = 0;
        $result['apply'] = 0;//是否申请
        $result['through'] = 0;//是否通过
        $result['can_apply'] = 0;//是否可以去申请
        $listen = $this->common_model->selectObjData('sty_test_listening', [
            'field' => ['update_time'],
            'where' => [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'lesson_num' => intval($lesson_num)
            ],
            'order' => ['id DESC'],
            'limit' => [1]
        ]);
        if (!empty($listen)) {
            if (!empty($listen['update_time']) && intval($listen['update_time']) > 0) {
                $complete_time = date('Y-m-d H:i:s', $listen['update_time']);
                $result['through'] = 1;
            } else {
                $result['apply'] = 1;
            }
        }
        $result['round'] = $this->oralTestFinishRound($uid, $schedule_id);
        $lessson_list = $this->lesson_model->getScheduleLessonDetail([
            'ssl.schedule_id' => $schedule_id,
            'ssl.outline_id' => $outline_id,
            'ssl.lesson_num' => intval($lesson_num),
            '_string' => "ssl.start_time IS NOT NULL AND ssl.end_time IS NOT NULL"
        ], [
            'ssl.name',
            'ssl.alias',
            'ssl.start_time',
            'ssl.end_time',
            'ssl.outline_lesson_id',
            'ssl.order',
            'ss.name as schedule_name'
        ], ['ssl.order ASC']);
        if (!empty($lessson_list)) {
            $count_num = count($lessson_list);
            $finish_num = 0;
            foreach ($lessson_list as $key => $val) {
                $lesson[$key]['finish'] = 0;
                $lesson[$key]['lesson_id'] = $val['id'];
                $lesson[$key]['order'] = $val['order'];
                $lesson[$key]['class_title'] = $val['schedule_name'];
                $lesson[$key]['class_title'] .= !empty($val['alias']) ? '：' . $val['alias'] : '';
                $lesson[$key]['class_time'] = $this->common_model->formatRecentDate($val['start_time'], 'm月d日');
                $lesson[$key]['class_time'] .= ' ' . date('H:i', strtotime($val['start_time'])) . '-' . date('H:i', strtotime($val['end_time']));
                $exist = $this->userOutlineLessonExistReport($uid, $schedule_id, $outline_id, $val['outline_lesson_id'], $complete_time);
                if ($exist) {
                    $lesson[$key]['finish'] = 1;
                    $finish_num++;
                }
            }
            $result['lesson'] = $lesson;
            $result['count_num'] = $count_num;
            $result['finish_num'] = $finish_num;
            if (intval($count_num) > 0 && intval($finish_num) === intval($count_num)) {
                $result['can_apply'] = 1;
            }
        }
        return $result;
    }

    /**
     * 申请口语测评
     * @param $uid
     * @param array $data
     */
    public function oralAssessmentApply($uid, array $data)
    {
        $result = ['code' => 0];
        $this->load->model('public/member_model');
        if (!empty($data) && !empty($data['reg_name']) && !empty($data['mobile']) && !empty($data['hope_time']) && intval($data['teacher_uid']) > 0) {
            $hope_time = $data['hope_time'];
            $hope_time_arr = explode(' ', $hope_time);
            $assessment_data = $assessment_time = '';
            if (!empty($hope_time_arr)) {
                $assessment_data = strtotime($hope_time_arr[0] . ' 00:00:00');
                $assessment_time = $hope_time_arr[1];
                $data['hope_time'] = date('Y-m-d', $assessment_data) . ' ' . $assessment_time;
            }
            //测评时间-老师对应的时间
            $assessmentCheck = $this->common_model->selectObjData('sty_test_teacher_config', [
                'field' => ['id'],
                'where' => [
                    'teacher_uid' => $data['teacher_uid'],
                    'test_date' => intval($assessment_data),
                    'time_name' => $assessment_time
                ],
                'limit' => [1]
            ]);
            if (!empty($assessmentCheck)) {
                //测评时间段是否已被预约
                $listening = $this->common_model->selectObjData('sty_test_listening', [
                    'field' => ['id'],
                    'where' => [
                        'teacher_uid' => $data['teacher_uid'],
                        'hope_time' => $data['hope_time']
                    ],
                    'limit' => [1]
                ]);
                if (!empty($listening)) {
                    $result['code'] = 249;
                } else {
                    $last_user_rank = $this->common_model->selectObjData('sty_test_listening', [
                        'field' => ['oral_level'],
                        'where' => [
                            'uid' => $uid,
                            'update_time>' => 0
                        ],
                        'limit' => [1]
                    ]);
                    $rank = 1;
                    if (!empty($last_user_rank)) {
                        $rank = intval($last_user_rank['oral_level']);
                    } else {
                        $user_msg = $this->member_model->getUserMessage($uid);
                        if (!empty($user_msg)) {
                            $rank = $user_msg['student_power'] ? intval($user_msg['student_power']) : 1;
                        }
                    }
                    $add['uid'] = $uid;
                    $add['reg_name'] = $data['reg_name'];
                    $add['mobile'] = $data['mobile'];
                    $add['rank'] = $rank;
                    $add['hope_time'] = $data['hope_time'];
                    $add['teacher_uid'] = $data['teacher_uid'];
                    $add['reg_time'] = time();
                    $add['schedule_id'] = intval($data['schedule_id']);
                    $add['schedule_lesson_id'] = intval($data['lesson_id']);
                    $add['lesson_num'] = intval($data['lesson_num']);
                    if (parent::$main_db->insert('sty_test_listening', $add) !== FALSE) {
                        $result['code'] = 200;
                    } else {
                        $result['code'] = 285;
                    }
                }
            } else {
                $result['code'] = 281;
            }
        }
        return $result;
    }

    /**
     * 口语测评验证同一outline_lesson_id下是否存在报道
     * @param $uid
     * @param $schedule_id
     * @param $outline_id
     * @param $outline_lesson_id
     * @param $complete_time
     * @return bool
     */
    public function userOutlineLessonExistReport($uid, $schedule_id, $outline_id, $outline_lesson_id, $complete_time)
    {
        $exist = FALSE;
        $w = [
            'ssl.schedule_id' => $schedule_id,
            'ssl.outline_id' => $outline_id,
            'ssl.outline_lesson_id' => $outline_lesson_id
        ];
        $lesson = $this->lesson_model->getScheduleLessonDetail($w);
        if (!empty($lesson)) {
            foreach ($lesson as $val) {
                $report = $this->lesson_model->getUserScheduleLessonInfo($uid, $val['id']);
                if (!empty($report)) {
                    if (intval($report['reservation_status']) == 1 && intval($report['check_in_status']) == 1 && $report['check_in_time'] >= $complete_time) {
                        $exist = TRUE;
                        break;
                    }
                }
            }
        }
        return $exist;
    }

    /**
     * 口语测评完成回合数
     * @param $uid
     * @param $schedule_id
     * @return int
     */
    public function oralTestFinishRound($uid, $schedule_id)
    {
        $round = 0;
        $lesson_num_arr = [];
        //查询测评结果条数
        $listen = $this->common_model->selectObjData('sty_test_listening', [
            'field' => ['lesson_num'],
            'where' => [
                'uid' => $uid,
                'schedule_id' => $schedule_id,
                'update_time>' => 0
            ]
        ]);
        if (!empty($listen)) {
            foreach ($listen as $val) {
                if (!in_array(intval($val['lesson_num']), $lesson_num_arr)) {
                    $round++;
                    $lesson_num_arr[] = intval($val['lesson_num']);
                }
            }
        }
        return $round;
    }

    /**
     * 口语测评时间老师信息
     * @param $date
     * @return array
     */
    public function oralAssessmentDateTeacher($date)
    {
        $time = strtotime($date . ' 00:00:00');
        $teacher = $apply_data = [];
        //测评老师
        $assessment_teacher = $this->common_model->selectObjData('sty_test_teacher', [
            'field' => ['value'],
            'where' => ['type' => 0],
            'order' => ['create_time ASC']
        ]);
        if (!empty($assessment_teacher)) {
            $i = 0;
            foreach ($assessment_teacher as $tk => $tv) {
                $teacher_info = $this->schedule_model->getTeacherInfo($tv['value']);
                if (!empty($teacher_info) && !empty($teacher_info['name'])) {
                    $teacher[$i]['teacher_uid'] = $tv['value'];
                    $teacher[$i]['teacher_name'] = $teacher_info['name'];
                    $i++;
                }
            }
        }
        //测评时间
        $assessment_date = $this->common_model->selectObjData('sty_test_teacher', [
            'field' => ['value'],
            'where' => ['type' => 1],
            'order' => ['id ASC']
        ]);
        //可申请的老师-时间
        $applyTeacherDate = $this->common_model->selectObjData('sty_test_teacher_config', [
            'field' => ['test_date', 'time_name', 'teacher_uid'],
            'where' => ['test_date' => $time]
        ]);
        if (!empty($applyTeacherDate)) {
            foreach ($applyTeacherDate as $key => $val) {
                //此时间此老师是否收到测评申请 apply
                $val['apply'] = FALSE;
                $val['expired'] = FALSE;
                $time_arr = explode('-', $val['time_name']);
                if (empty($time_arr) || empty($time_arr[1])) continue;
                $hope = date('Y-m-d', $val['test_date']) . ' ' . $time_arr[1];
                $hope_time = date('Y-m-d', $val['test_date']) . ' ' . $val['time_name'];
                if ($hope > date('Y-m-d H:i')) {
                    $listening = $this->common_model->selectObjData('sty_test_listening', [
                        'field' => ['id'],
                        'where' => [
                            'teacher_uid' => $val['teacher_uid'],
                            'hope_time' => $hope_time
                        ],
                        'limit' => [1]
                    ]);
                    if (!empty($listening)) {
                        $val['apply'] = TRUE;
                    }
                } else {
                    $val['expired'] = TRUE;
                }
                $val['hope_time'] = $hope_time;
                $apply_data[$val['time_name'] . '_' . $val['teacher_uid']] = $val;
            }
        }
        return [
            'date' => $date,
            'teacher' => $teacher,
            'assessment_date' => $assessment_date,
            'apply_data' => $apply_data
        ];
    }

    /**
     * 选课提示
     * @param $uid
     * @return array
     */
    public function selectCourseTip($uid)
    {
        $result = ['code' => 0, 'goods_names' => ''];
        $goods = [];
        $goods_all = $this->goods_model->getUserGoodsDetial($uid);
        if (!empty($goods_all)) {
            //用户存在常规课去除精品课、体验课显示
            foreach ($goods_all as $item) {
                if (intval($item['business_type']) != 2 && intval($item['business_type']) != 5) {
                    $goods[] = $item;
                }
            }
            if (empty($goods)) $goods = $goods_all;
            $names = [];
            foreach ($goods as $val) {
                if (intval($val['show_tip']) === 1) {
                    $names[] = $val['name'];
                }
            }
            if (!empty($names)) {
                $result['code'] = 200;
                $result['goods_names'] = implode('，', $names);
            }
        }
        return $result;
    }

    /**
     * 关闭选课提示
     * @param $uid
     * @return array
     */
    public function selectCourseTipClose($uid)
    {
        $result = ['code' => 0];
        $goods = $this->goods_model->getUserGoodsDetial($uid, ['sug.show_tip' => 1]);
        if (!empty($goods)) {
            if (parent::$main_db->where(['uid' => $uid])->update('sty_user_goods', ['show_tip' => 0]) !== FALSE) {
                $result['code'] = 200;
            }
        }
        return $result;
    }

    /**
     * 教师评价
     * @param $uid
     * @param $id
     * @param $type
     * @return array
     */
    public function switchTeacherComment($uid, $id, $type)
    {
        $result = [];
        $type = in_array($type, ['prev', 'next']) && $id > 0 ? $type : 'next';
        $where = [
            'student_id' => $uid,
            'schedule_id >' => 0,
            'is_del' => 0
        ];
        if ($type === 'next') {
            if ($id > 0) {
                $w['id <'] = $id;
            }
            $order = 'id DESC';
        } else {
            $w['id >'] = $id;
            $order = 'id ASC';
        }
        $where['schedule_id >'] = 0;
        $query = parent::$main_db->select('id, uid, schedule_id, comment, browse, create_time')
            ->from('sty_teacher_comment')
            ->where($where);
        if (!empty($w)) {
            $query->where($w);
        }
        $comment = $query->order_by($order)->get()->row_array();
        if (!empty($comment)) {
            $result['id'] = $comment['id'];
            $result['browse'] = 0;
            $result['comment'] = $comment['comment'];
            $result['time'] = $comment['create_time'];
            $result['course'] = '';
            $result['teacher_name'] = '';
            $result['teacher_avatar'] = '';
            if ($comment['schedule_id'] > 0) {
                $schedule = $this->schedule_model->getScheduleCacheData($comment['schedule_id']);
                if (!empty($schedule)) {
                    $result['course'] = $schedule['name'];
                }
            }
            if ($comment['uid'] > 0) {
                $teacher = $this->schedule_model->getTeacherInfo($comment['uid']);
                if (!empty($teacher)) {
                    $result['teacher_name'] = $teacher['name'] . '老师';
                    $result['teacher_avatar'] = $teacher['avatar'];
                }
            }
            if (intval($comment['browse']) === 0) {
                if (parent::$main_db->update('sty_teacher_comment', ['browse' => 1], ['id' => $comment['id']]) !== FALSE) {
                    $result['browse'] = 1;
                }
            }
        }
        return $result;
    }

    /**
     * 用户是否观看新手引导
     * @param $uid
     * @return array
     */
    public function newGuideStatus($uid)
    {
        $result = ['code' => 0];
        $query = parent::$main_db
            ->select('new_guide')
            ->from('message_user')
            ->where('uid', $uid)
            ->limit(1)
            ->get()->row_array();
        if ($query['new_guide'] === '0') {
            $result['code'] = 200;
        }
        return $result;
    }

    /**
     * 用户是否观看新手引导
     * @param $uid
     * @return array
     */
    public function newGuideStatusClose($uid)
    {
        $result = ['code' => 0];
        $status = parent::$main_db->select('new_guide')
            ->from('message_user')
            ->where('uid', $uid)
            ->get()->row_array();
        if (!empty($status)) {
            if (parent::$main_db->where(['uid' => $uid])->update('message_user', ['new_guide' => 1], ['new_guide' => 0]) !== FALSE) {
                $result['code'] = 200;
            }
        }
        return $result;
    }

    /**
     * 用户是否购买过正式课
     * @param $uid
     * @return bool
     */
    public function haveFormalClass($uid)
    {
        $result = TRUE;
        $count = parent::$main_db->from('zd_netschool.sty_user_goods as sug')
            ->join('zd_netschool.sty_goods as sg', 'sug.goods_id = sg.id', 'left')
            ->where([
                'sug.uid' => $uid,
                'sug.is_del' => 0,
                'sg.is_del' => 0
            ])
            ->where_in('sg.business_type', [1, 3, 4])
            ->count_all_results();
        if ($count) { //有正式商品
            $result = $this->userIsSelectCourse($uid);
        }
        return $result;
    }

}