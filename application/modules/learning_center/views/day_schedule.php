<div class="class_list_v5">
    <?php if ($model['hasExpire'] == FALSE): ?>
        <!--- 已过期 --->
        <div class="no_course_v5">
            <img src="<?php echo img_url('learning_center', 'ff_04.png') ?>" alt="">
            <p class="taC fz16 color_333 mt15">你所报名的课程商品已过期，无法学习啦…</p>
            <span class="w230 marAuto block mt05">
            <a href="#" class="button_w106 flL btn_line_3399ff mt15">查看已购商品</a>
            <a href="#" class="button_w108 flL btn_3399ff mt15 ml10">去报名新商品</a>
        </span>
        </div>
        <!--- 已过期 ---->
    <?php elseif ($model['selectCourse'] == FALSE): ?>
        <!--- 未选课 --->
        <div class="no_course_v5">
            <img src="<?php echo img_url('learning_center', 'noClass_v5.png') ?>" alt="">
            <p class="taC fz14 color_666 mt25">选课后，在这里查看课表、进入教室上直播课哦</p>
        </div>
        <!--- 未选课 --->
    <?php else: ?>
        <?php $i=0;
        if (!empty($model['asoftData']) && !empty($model['lessonData'])):
            foreach ($model['asoftData'] as $lessonId => $item):
                if (isset($model['lessonData'][$lessonId]) && !empty($model['lessonData'][$lessonId])):
                    $lessonVal = $model['lessonData'][$lessonId];
                    $i++;
                    if ($lessonVal['report_score'] > 0) {
                        $liClass = 'pt08';
                    } else {
                        $liClass = 'pt08';
                    }
                    ?>
                    <ul class="clearfix class_lesson_open"
                        data-lesson="<?php echo $lessonVal['lesson_id']; ?>"
                        data-schedule="<?php echo $lessonVal['schedule_id']; ?>"
                        data-start="<?php echo strtotime($lessonVal['start_time']); ?>"
                        data-end="<?php echo strtotime($lessonVal['end_time']); ?>"
                        data-room="<?php echo $lessonVal['room_name']; ?>"
                        data-roompwd="<?php echo $lessonVal['room_pwd']; ?>"
                        data-plan="<?php echo $lessonVal['plan_id']; ?>"
                        data-stage="<?php echo $lessonVal['plan_stage_id']; ?>"
                        data-curricular="<?php echo $lessonVal['curricular']; ?>"
                    >
                        <div class="ul_container <?php if($i > 1 && $i===count($model['asoftData'])) echo 'border00';?>">
                            <li class="calendar_status_v5 google_event <?php echo $liClass; ?>" ga-type="点击直播课表_奖章图标">
                                <img src="<?php echo $lessonVal['report_icon']; ?>" alt="">
                                <?php if ($lessonVal['report_score'] > 0): ?>
                                    <p class="report_v5 <?php echo $lessonVal['score_class']; ?>">
                                        +<?php echo $lessonVal['report_score']; ?>
                                    </p>
                                <?php endif; ?>
                                <p class="status_hover_v5"><?php echo $lessonVal['report_prompt']; ?></p>
                            </li>
                            <li class="calendar_chapter_v5">
                                <?php if ($lessonVal['is_reservation'] === TRUE): ?>
                                    <p class="google_event" ga-type="点击直播课表_课件标题" ga-title="<?php echo $lessonVal['title']; ?>" title="<?php echo $lessonVal['title']; ?>"><?php echo $lessonVal['title']; ?></p>
                                <?php else:?>
                                    <p class="google_event hover_blue curP link" data-link="myCourse?id=<?php echo $lessonVal['schedule_id'];?>" ga-type="点击直播课表_课件标题" ga-title="<?php echo $lessonVal['title']; ?>" title="<?php echo $lessonVal['title']; ?>"><?php echo $lessonVal['title']; ?></p>
                                <?php endif;?>
                                <p class="chapter_time_v5">
                                    <b>主讲老师：<?php echo $lessonVal['teacher_name']; ?></b>
                                </p>
                            </li>
                            <li class="calendar_button_v5 google_event" ga-label="点击直播课表_">
                                <?php echo $lessonVal['button']; ?>
                            </li>
                        </div>
                    </ul>
                <?php endif; endforeach; ?>
        <?php else: ?>
            <div class="cpss_search_none">
                <img src="<?php echo img_url('learning_center', 'cpss_cup.png'); ?>">
                <h3>今天没有直播课哦</h3>
                <?php if (!empty($model['afterLesson'])): ?>
                    <h4 class="blue">下次直播：<?php echo date('Y/m/d H:i', strtotime($model['afterLesson']['start_time'])); ?></h4>
                    <a href="javascript:;" class="today_after_live google_event" data-date="<?php echo date('Y-m-d', strtotime($model['afterLesson']['start_time'])); ?>" ga-type="点击直播课表_周日历" ga-title="查看（当日无课状态）">查 看</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

