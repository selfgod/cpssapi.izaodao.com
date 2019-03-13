<div class="container_v422_list mt20 w948 google_event" ga-type="点击录播回顾">
    <?php foreach ($model['data'] as $lesson):?>
    <ul data-id="<?php echo $lesson['id'];?>">
        <li class="w135 mt10">
            <?php if($lesson['check_in_status'] === '1'):?>
            <div class="task_v422">
                <?php if($lesson['check_in_score'] > 0):?>
                <p class="color_4bb866 flL ml10">+<?php echo $lesson['check_in_score'];?></p>
                <i class="task_right_v422 ml05"><?php else:?>
                <i class="task_right_v422 ml35">
                <?php endif;?>
                    <img src="<?php echo img_url('learning_system', 'task_right_v422.png');?>" alt=""/>
                </i>
            </div><p class="doing_green_v422 clearfix"><?php echo $lesson['beauty_checkin_date'];?><br/>完成</p>
            <?php endif;?>
        </li>
        <li class="w110 mt10 mr30">
            <p class="lessonTitle_v422 color_666 <?php if(!$model['is_record']) echo 'mt04'; else echo 'mt20';?> mb05 taC">上课</p>
            <?php if(!$model['is_record']):?>
            <div class="teacher_v422 taC lh20"><?php echo $lesson['beauty_start_date'];?></div>
            <?php endif;?>
        </li>
        <li class="w530 mt10">
            <p class="lessonTitle_v422 color_666 mt04 mb05"><?php echo $lesson['name'];?></p>
            <div class="teacher_v422"><img src="<?php echo $lesson['smallPic'];?>">主讲 <?php echo $lesson['teacher_name'];?></div>
        </li>
        <li class="w100">
            <?php
                $mtClass = ' mt25';
                if(intval($lesson['record_download']) === 1):
                    $mtClass = '';
            ?>
            <a href="javascript:void(0);" class="button_v422_01 button_green <?php if(!empty($lesson['record_link'])) echo 'download'; else echo 'none_record';?>" ga-title="下载">下 载</a>
            <?php endif;?>
            <a href="javascript:void(0);" class="button_v422_01 button_lineGreen mt08 <?php if(!empty($lesson['record_link'])) echo 'review'; else echo 'none_record';?><?php echo $mtClass;?>" ga-title="回顾">回 顾</a>
        </li>
    </ul>
    <?php endforeach;?>
</div>