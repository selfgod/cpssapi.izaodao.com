<div class="container_v422_tips taC fz18"><?php if($model['allDone']):?>今天任务全部完成了，休息一下吧~<?php else:?>今天共有 <b class="color_4bb866"><?php echo $model['total'];?></b> 个任务，抓紧完成哦~</div><?php endif;?>

<div class="container_v422_list google_event" ga-type="点击直播课表">
    <?php foreach ($model['lessons'] as $index => $lesson):?>
        <ul class="borderLeft00 borderRight00 <?php if($index !== 0) echo 'borderTop00';?>" data-starttime="<?php echo $lesson['start_timestamp'];?>" data-endtime="<?php echo $lesson['end_timestamp'];?>" data-id="<?php echo $lesson['id'];?>">
            <li class="w155 mt10">
                <div class="task_v422">
                    <?php if($lesson['check_in_status'] === '1' && $lesson['check_in_score'] > 0):?>
                        <p class="color_4bb866 flL">+<?php echo $lesson['check_in_score'];?></p>
                    <?php elseif ($lesson['check_in_status'] !== '1' && isset($lesson['ontime'])):?>
                        <p class="color_ccc">+5</p>
                    <?php endif;?>
                    <?php if($lesson['check_in_status'] === '1'):?>
                        <i class="task_right_v422">
                            <img src="<?php echo img_url('learning_system', 'task_right_v422.png');?>" alt=""/>
                        </i>
                    <?php endif;?>
                </div>
                <p class="color_555 w100 taC <?php  if($lesson['check_in_status'] !== '1' && !isset($lesson['ontime'])) echo 'mt20';?> clearfix">上课</p>
            </li>
            <li class="w650 mt10">
                <p class="lessonTitle_v422"><?php echo $lesson['name'];?></p>
                <div class="teacher_v422"><?php echo $lesson['beauty_start_time'].'-'.$lesson['beauty_end_time'];?><img src="<?php echo $lesson['smallPic'];?>" class="ml20">主讲：<?php echo $lesson['teacher_name'];?></div>
            </li>
            <li class="w100">
                <?php if($lesson['check_in_status'] !== '1' && $model['current'] < strtotime($lesson['end_time'])+(90*60)):?>
                    <?php if($lesson['enable_zdtalk']['enable']==TRUE):?>
                        <a href="javascript:void(0);" data-zdtalk="<?php echo $lesson['enable_zdtalk']['href']?>" class="button_v422_01 button_green zdtalk" data-download="<?php echo $model['zd_download'];?>" ga-title="进入教室">进入教室</a>
                    <?php else:?>
                        <a href="javascript:void(0);" class="button_v422_01 button_green room">教室信息</a>
                    <?php endif;?>
                    <a href="javascript:void(0);" class="button_v422_01 button_lineGreen mt08 checkin" ga-title="报到">报 到</a>
                <?php elseif($lesson['check_in_status'] === '1' && $model['current'] < strtotime($lesson['end_time'])+(90*60)):?>
                    <?php if($lesson['enable_zdtalk']['enable']==TRUE):?>
                        <a href="javascript:void(0);" data-zdtalk="<?php echo $lesson['enable_zdtalk']['href']?>" class="button_v422_01 button_green zdtalk" data-download="<?php echo $model['zd_download'];?>" ga-title="进入教室">进入教室</a>
                    <?php else:?>
                        <a href="javascript:void(0);" class="button_v422_01 button_green room">教室信息</a>
                    <?php endif;?>
                    <a class="button_v422_01 mt08 button_Hui checkin">已报到</a>
                <?php else:?>
                    <?php if (intval($lesson['is_record']) === 1):
                        $mtClass = ' mt25';
                    ?>
                        <?php
                            if(intval($lesson['record_download']) === 1):
                                $mtClass = '';
                            ?>
                            <a href="javascript:void(0);" class="button_v422_01 button_green <?php if(!empty($lesson['record_link'])) echo 'download'; else echo 'none_record';?>" ga-title="下载">下 载</a>
                        <?php endif;?>
                        <a href="javascript:void(0);" class="button_v422_01 button_lineGreen mt08 <?php if(!empty($lesson['record_link'])) echo 'review'; else echo 'none_record';?><?php echo $mtClass;?>" ga-title="回顾">回 顾</a>
                    <?php else:?>
                        <p class="lh80">无录播</p>
                    <?php endif;?>
                <?php endif;?>
            </li>
        </ul>
        <?php if(!empty($lesson['test_id'])):?>
            <ul class="borderLeft00 borderRight00 borderTop00">
                <li class="w155 mt10">
                    <div class="task_v422">
                        <?php if(isset($lesson['test_complete'])):?>
                        <?php if(isset($lesson['test_current_finish']) && $lesson['test_score']>0):?><p class="color_4bb866 flL">+3</p><?php endif;?>
                            <i class="task_right_v422">
                                <img src="<?php echo img_url('learning_system', 'task_right_v422.png');?>" alt=""/>
                            </i>
                        <?php else:?>
                        <p class="color_ccc">+3</p>
                        <?php endif;?>
                    </div>
                    <p class="color_555 w100 taC clearfix">一课一练</p>
                </li>
                <li class="w650 mt10">
                    <p class="lessonTitle_v422"><?php echo $lesson['name'];?></p>
                    <div class="teacher_v422">共<?php echo $lesson['test_item_count'];?>题 预计<?php echo $lesson['test_total_time'];?>分钟</div>
                </li>
                <li class="w100">
                    <?php if(isset($lesson['test_last_time'])):?>
                    <a href="javascript:void(0);" data-url="<?php echo $lesson['test_retest_url'];?>" class="button_v422_01 button_green exec" ga-title="重做">重 做</a>
                    <a target="_blank" href="<?php echo $lesson['test_begin_url'];?>" class="button_v422_01 button_lineGreen mt08" ga-title="回顾">回 顾</a>
                    <?php else:?>
                    <a href="javascript:void(0);" data-url="<?php echo $lesson['test_begin_url'];?>" class="button_v422_01 button_green mt20 exec" ga-title="开始练习">开始练习</a>
                    <?php endif;?>
                </li>
            </ul>
        <?php endif;?>
        <?php if(!empty($lesson['unit_id'])):?>
            <ul class="borderLeft00 borderRight00 borderTop00" data-starttime="<?php echo $lesson['start_timestamp'];?>">
                <li class="w155">
                    <div class="task_v422">
                        <?php if(isset($lesson['unit_complete'])):?>
                        <?php if(isset($lesson['unit_current_finish']) && $lesson['unit_score']>0):?><p class="color_4bb866 flL">+<?php echo $lesson['unit_score'];?></p><?php endif;?>
                        <i class="task_right_v422">
                            <img src="<?php echo img_url('learning_system', 'task_right_v422.png');?>" alt=""/>
                        </i>
                        <?php else:?>
                            <p class="color_ccc">
                            <?php if(!$lesson['ontime_unit']):?>
                                <?php if(isset($lesson['unit_last_time'])) echo '+3'; else echo '+5';?>
                            <?php else:?>
                                <?php if($model['current'] > strtotime($lesson['start_time']) + 3600) echo '+3'; else echo '+5';?>
                            <?php endif;?></p>
                        <?php endif;?>
                    </div>
                    <p class="color_555 w100 taC clearfix">单元测试</p>
                </li>
                <li class="w650 mt10">
                    <p class="lessonTitle_v422"><?php echo $lesson['name'];?></p>
                    <div class="teacher_v422">共<?php echo $lesson['unit_item_count'];?>题<b class="ml20"><?php echo $lesson['unit_total_time'];?>分钟</b></div>
                </li>
                <li class="w100">
                    <?php if(isset($lesson['unit_last_time'])):?>
                        <a href="javascript:void(0);" data-url="<?php echo $lesson['unit_retest_url'];?>" class="button_v422_01 button_green retest exec" ga-title="重考">重 考</a>
                        <a target="_blank" href="<?php echo $lesson['unit_review_url'];?>" class="button_v422_01 button_lineGreen mt08" ga-title="回顾">回 顾</a>
                    <?php else:?>
                        <?php
                        $but = '补考';
                        if (!$lesson['ontime_unit'] || ($model['current'] < strtotime($lesson['start_time']) + 3600)) $but = '开始测试';
                        ?>
                        <a href="javascript:void(0);" data-url="<?php echo $lesson['unit_begin_url'];?>" class="button_v422_01 button_green mt20 exec <?php if(isset($lesson['ontime_unit_notstart'])) echo 'not_start'; elseif(isset($lesson['over_30min'])) echo ' over_30min';?>" ga-title="<?php echo $but;?>"><?php echo $but;?></a>
                    <?php endif;?>
                </li>
            </ul>
        <?php endif;?>
    <?php endforeach;?>
</div>