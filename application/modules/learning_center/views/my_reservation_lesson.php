<?php if (!empty($model['my_reservation_list'])):
    foreach ($model['my_reservation_list'] as $key => $item):?>
        <ul class="clearfix class_lesson_open"
            data-lesson="<?php echo $item['id']; ?>"
            data-schedule="<?php echo $item['schedule_id']; ?>"
            data-start="<?php echo strtotime($item['start_time']); ?>"
            data-end="<?php echo strtotime($item['end_time']); ?>"
            data-room="<?php echo $item['room_name']; ?>"
            data-roompwd="<?php echo $item['room_pwd']; ?>"
            data-curricular="<?php echo $model['current_curricular']; ?>"
        >
        <div class="ul_lesson_container">
            <li class="calendar_status_v5 pt07 google_event"
                ga-type="点击<?php echo $model['curricular_zh'] . '_奖章图标'; ?>">
                <img src="<?php echo $item['report_icon']; ?>" alt="">
                <?php if ($item['report_score'] > 0): ?>
                    <p class="report_v5 <?php echo $item['score_class']; ?>">
                        +<?php echo $item['report_score']; ?>
                    </p>
                <?php endif; ?>
                <p class="status_hover_v5"><?php echo $item['report_prompt']; ?></p>
            </li>
            <li class="calendar_time_v5">
                <p class="fz18 color_666 mt03">上课</p>
                <p class="fz16 color_888 mt05"><?php echo date('Y/m/d', strtotime($item['start_time'])); ?></p>
            </li>
            <li class="calendar_chapter_v5">
                <?php if ($model['current_curricular'] === 'oral'): ?>
                    <a href="javascript:;" class="link color_3399ff google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_课件标题'; ?>" ga-title="<?php echo $item['title'];?>" data-link="oralAssessment?lesson=<?php echo $item['id']; ?>" title="<?php echo $item['title'];?>"><?php echo $item['title']; ?></a>
                <?php else: ?>
                    <p class="google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_课件标题'; ?>" ga-title="<?php echo $item['title'];?>" title="<?php echo $item['title'];?>"><?php echo $item['title']; ?></p>
                <?php endif; ?>
                <p class="chapter_time_v5">
                    <b class="ffA"><?php echo date('H:i', strtotime($item['start_time'])) . '-' . date('H:i', strtotime($item['end_time'])); ?></b>
                    <i class="tacher_img_v5 ml10 google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_老师头像'; ?>" ga-title="<?php echo $item['teacher_name']; ?>"><img src="<?php echo $item['teacher_avatar']; ?>" alt=""></i>
                    <b>主讲：<?php echo $item['teacher_name']; ?></b>
                </p>
            </li>
            <li class="calendar_button_v5 google_event" ga-label="点击<?php echo $model['curricular_zh'] . '_'; ?>">
                <?php echo $item['button']; ?>
            </li>
            </div>
        </ul>
    <?php endforeach; else: ?>
    <?php if (isset($model['noCourse'])) echo $model['noCourse']; ?>
<?php endif; ?>
