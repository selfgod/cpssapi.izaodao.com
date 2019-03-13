<div class="evaluation_main_v5 clearfix">
    <div class="evaluation_main_top_v5">
        <h4 class="pl05" id="schedule_name_val"><?php echo $model['schedule_name']; ?></h4>
        <p class="flL fz18 color_ff6e1f mt10 pl05">
            完成度: <?php echo $model['finish_num'] . '/' . $model['count_num']; ?></p>
        <?php if (!empty($model['lesson'])): ?>
            <span class="evaluation_main_star_v5">
            <?php foreach ($model['lesson'] as $v):
                $aClass = 'evaluation_main_star_finish_v5 link';
                if (intval($v['finish']) === 1) {
                    $aClass = 'evaluation_main_star_unfinish_v5 link';
                }
                ?>
                <a href="javascript:;" class="<?php echo $aClass; ?>"
                   data-link="oralAssessment?lesson=<?php echo $v['lesson_id']; ?>"></a>
            <?php endforeach; ?>
        </span>
        <?php endif; ?>
        <p class="fz14 color_888 pt05 pl05 clearfix">
            <?php if (intval($model['finish_num']) >= intval($model['count_num'])): ?>
                已完成全部学习，请申请专业口语测评！
            <?php else: ?>
                完成全部课程学习，可以申请专业口语测评哦！
            <?php endif; ?>
        </p>
        <div class="myclassv222_row_right">
            <p class="myclassv222_textR"><?php if ($model['round']) echo '课程全部完成 ' . $model['round'] . ' 次'; ?></p>
            <input type="hidden" value="<?php echo $model['round']; ?>" id="listening_count"/>
            <span class="myclassv222_buttonR mt00"></span>
        </div>
        <a href="javascript:;" class="apply_oral_assessment evaluation_button_v5"><img
                    src="<?php echo img_url('learning_center', 'myclassv222_button.png') ?>" alt=""></a>
    </div>
    <?php if (!empty($model['lesson'])): ?>
        <div class="evaluation_main_list_v5">
            <ul class="clearfix">
                <li class="w515 color_888 fz18 pl30">课程名称</li>
                <li class="w170 color_888 taC fz18">上课时间</li>
                <li class="w75  color_888 taC fz18">状态</li>
            </ul>
            <?php foreach ($model['lesson'] as $lv): ?>
                <ul class="clearfix link" data-link="oralAssessment?lesson=<?php echo $lv['lesson_id']; ?>">
                    <li class="w515 color_3bac57 fz18 pl30"><i
                                class="icon_tips flL mr15"><?php echo $lv['order']; ?></i><?php echo $lv['class_title']; ?>
                    </li>
                    <li class="w170 color_aaa taC"><?php echo $lv['class_time']; ?></li>
                    <?php if (intval($lv['finish']) === 1): ?>
                        <li class="w75 color_ff6e1f taC"><i class="myclassv222_left_listIcon_starOff"></i> 已学</li>
                    <?php else: ?>
                        <li class="w75 color_888 taC"><i class="myclassv222_left_listIcon_starOv"></i> 未学</li>
                    <?php endif; ?>
                </ul>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>