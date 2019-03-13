<div class="cpss_class_list">
    <ul>
        <li class="cpss_class_title">
            <p class="w115">上课时间</p>
            <p class="w385 taL pl20">主题</p>
            <p class="w108">已预约/限定人数</p>
            <p class="w230 taR pr40">操作</p>
        </li>
        <div class="reservation_list_detial">
            <?php if (!empty($model['reservation_list'])):
                foreach ($model['reservation_list'] as $item):
                    ?>
                    <li class="cpss_list_hover class_lesson_open"
                        data-lesson="<?php echo $item['id']; ?>"
                        data-schedule="<?php echo $item['schedule_id']; ?>"
                        data-start="<?php echo strtotime($item['start_time']); ?>"
                        data-end="<?php echo strtotime($item['end_time']); ?>"
                        data-room="<?php echo $item['room_name']; ?>"
                        data-roompwd="<?php echo $item['room_pwd']; ?>"
                        data-curricular="<?php echo $model['curricular']; ?>"
                    >
                        <div class="cpss_time">
                            <p class="fz14 color_444"><?php echo $item['class_time']; ?></p>
                            <p class="fz12 color_999 mt03"><?php echo $item['class_date']; ?></p>
                            <p class="fz12 color_999 mt03"><?php echo $item['class_week']; ?></p>
                        </div>
                        <div class="cpss_theme">
                            <?php if ($model['curricular'] == 'oral'): ?>

                                <p class="fz16 mb05">
                                    <a href="javascript:;" class="link color_3399ff google_event" title="<?php echo $item['title'];?>"
                                       data-link="oralAssessment?lesson=<?php echo $item['id']; ?>" ga-type="点击<?php echo $model['curricular_zh'] . '_课程标题'; ?>" ga-title="<?php echo $item['title']; ?>"><?php echo $item['title']; ?></a>
                                </p>

                            <?php else: ?>
                                <p class="color_555 fz16 mb05 google_event" title="<?php echo $item['title'];?>" ga-type="点击<?php echo $model['curricular_zh'] . '_课程标题'; ?>" ga-title="<?php echo $item['title']; ?>"><?php echo $item['title']; ?></p>
                            <?php endif; ?>
                            <p class="color_bbb fz14">适合基础：<?php echo $item['basic_val']; ?></p>
                            <span class="cpss_teacher_avatar pt05 google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_老师头像'; ?>" ga-title="<?php echo $item['teacher_name']; ?>">
                                <img src="<?php echo $item['teacher_avatar']; ?>" alt="">
                                <p><?php echo $item['teacher_name']; ?></p>
                            </span>
                        </div>
                        <div class="cpss_people"><?php echo $item['lesson_reservation_count']; ?>/<?php echo $item['max_limit']; ?></div>
                        <div class="cpss_operation w250 taR mr20 google_event" ga-type="<?php echo $model['curricular_zh']; ?>">
                            <?php echo $item['button']; ?>
                        </div>
                    </li>
                <?php endforeach;
            else: ?>
                <?php if (isset($model['noCourse'])) echo $model['noCourse']; ?>
            <?php endif; ?>
        </div>
    </ul>
</div>
