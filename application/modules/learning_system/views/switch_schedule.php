<div class="new_title">
    <h1><a href="/#selectCourse/major"><?php echo $model['plan_name']; ?></a> &gt; <?php echo $model['stage_name']; ?></h1>
</div>
<div class="new_liveClass ga" ga-location="我的课程（日语）_主修课_学习页">
    <div class="new_delete_contant">
        <div id="switch_schedule" class="slider3" style="display: none">
            <?php foreach ($model['scheduleList'] as $schedule): ?>
                <div data-mode="<?php echo $schedule['class_mode']; ?>"
                     data-starttime="<?php echo $schedule['start_time']; ?>"
                     data-roomid="<?php echo $schedule['room_id']; ?>"
                     data-roomname="<?php echo $schedule['room_name']; ?>"
                     data-roompwd="<?php echo $schedule['room_pwd']; ?>" data-id="<?php echo $schedule['id']; ?>"
                     class="slide imglist<?php if ($model['current_id'] === $schedule['id']):; ?> new_liveClass_current">
                    <i class="new_liveClass_currentIcon"><img
                                src="<?php echo img_url('learning_system', 'new_liveClass_current.png'); ?>"
                                alt=""></i><?php else: ?>">
                    <?php endif; ?>
                    <p class="<?php if ($model['current_id'] === $schedule['id']):; ?>imglist_title<?php else: ?>imglist_title_w240 color_666<?php endif; ?>"><?php echo $schedule['name']; ?></p>
                    <p class="mt15">主讲：<?php echo implode('、', $schedule['teachers']); ?></p>
                    <?php if ($schedule['class_mode'] != '2'): ?>
                        <p>
                            上课：<?php echo implode('', $schedule['week_circle']); ?> <?php echo $schedule['class_start_time']; ?>
                            -<?php echo $schedule['class_end_time']; ?></p>
                        <?php if (!empty($schedule['qq_group'])): ?>
                            <p class="mt10"><b style="font-family: Arial">QQ</b>群：<?php echo $schedule['qq_group']; ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($model['able_del_schedule'] === TRUE): ?>
                        <div class="new_delete">
                            <span class="new_delete_icon_off curP ga" ga-location="我的课程（日语）_主修课_学习页" ga-type="点击删除图标"></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="slide imglist new_liveClass_none" ga-type="点击添加新课程">添加新课程</div>
        </div>
    </div>
</div>
<input type="hidden" id="pre_date" value="<?php if (isset($model['pre_date'])) echo $model['pre_date']; ?>">
<input type="hidden" id="plan_id" value="<?php if (isset($model['plan_id'])) echo $model['plan_id']; ?>">
<input type="hidden" id="plan_stage_id"
       value="<?php if (isset($model['plan_stage_id'])) echo $model['plan_stage_id']; ?>">
<input type="hidden" id="hide_nav" value="<?php if (isset($model['pre_nav'])) echo $model['pre_nav']; ?>">
<input type="hidden" id="hide_type" value="<?php if (isset($model['pre_type'])) echo $model['pre_type']; ?>">
<input type="hidden" id="hide_category"
       value="<?php if (isset($model['pre_category'])) echo $model['pre_category']; ?>">
