<?php
if (!empty($model['schedule_list'])):
    foreach ($model['schedule_list'] as $val):
        if($model['mode'] == 1):
            if($val['remain_num'] <= 0):
                continue;
            endif;
        endif;
        ?>
        <div class="cpss_techer_list">
            <h2 class="pl20 color_333 boB_e5e5e5 clearfix"><p class="flL mr10"><?php echo $val['name']; ?></p>
                <?php if($model['mode'] == 1 && $val['curricular_system'] == 1):?>
                    <i class="quick_icon" <?php if(!in_array(1,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][1];?></i>
                    <i class="quick_icon"  <?php if(!in_array(2,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][2];?></i>
                    <i class="quick_icon"  <?php if(!in_array(3,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][3];?></i>
                    <i class="evening_icon"  <?php if(!in_array(4,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][4];?></i>
                    <i class="evening_icon"  <?php if(!in_array(5,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][5];?></i>
                    <i class="evening_icon"  <?php if(!in_array(6,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][6];?></i>
                    <i class="blue_icon"  <?php if(!in_array(7,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][7];?></i>
                    <i class="blue_icon"  <?php if(!in_array(8,$val['label'])): echo 'hidden'; endif;?> ><?php echo $model['label_all'][8];?></i>
                 <?php endif;?>
            </h2>
            <!--- start 老师头像切换 ------------------>
            <?php if (!empty($val['teachers'])): ?>
                <div class="switch_teacher cpss_techer_box" data-length="<?php echo count($val['teachers']);?>">
                    <?php
                    foreach ($val['teachers'] as $k => $v):
                        $v['tag'] = implode(' ',explode(',',$v['tag']));
                        ?>
                        <div class="slide">
                            <div class="cpss_techer_box">
                                <?php if(!empty($v['audition_link'])):?>
                                    <a target="_blank" href="<?php echo $v['audition_link'];?>">
                                        <i class="listening_img play_video google_event" class="video_img" data-links="<?php echo $v['audition_link'];?>" ga-type="点击选课页_试听按钮">
                                            <img src="<?php echo img_url('learning_center','listening_img.png'); ?>" alt="">
                                        </i>
                                    </a>

                                <?php endif;?>
                                <span class="cpss_techer_box_avatar taC google_event" ga-type="点击选课页_点击老师头像" ga-title="<?php echo $v['name']; ?>">
                            <a href="<?php echo $v['detail_link']; ?>" target="_blank"><img src="<?php echo $v['avatar']; ?>" alt=""></a>
                        </span>
                                <p class="mt20 fz16 color_333 taC"><?php echo $v['name']; ?></p>
                                <p class="cpss_text_ticp"><?php echo $v['tag']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!--- end 老师头像切换 -------------------->

            <!--- start cpss_techer_text ------------------>
            <div class="cpss_techer_right">
                <?php if (intval($model['mode']) === 1): ?>
                <span class="cpss_techer_text color_3399ff clearfix">
                    <b class="dat flL">上课日期：</b>
                    <p class="flL"><?php echo $val['class_date']; ?></p>
                </span>
                <span class="cpss_techer_text color_3399ff clearfix">
                    <b class="time flL">上课时间：</b>
                    <p class="flL"><?php echo $val['class_time']; ?></p>
                </span>
                <?php else:?>
                <span class="cpss_techer_text color_3399ff clearfix">
                    <b class="dat flL">课次总数：</b>
                    <p class="flL"><?php echo $val['class_num']; ?></p>
                </span>
                <?php endif;?>
                <span class="cpss_techer_text clearfix">
                    <b class="book flL">使用教材：</b>
                    <p class="flL"><?php echo $val['use_books']; ?></p>
                </span>
                <?php if (intval($model['mode']) === 1): ?>
                <span class="cpss_techer_text clearfix">
                    <b class="lessons flL">上课进度：</b>
                    <p class="flL"><?php echo $val['progress']; ?></p>
                </span>
                <?php endif;?>

                <span class="cpss_techer_text clearfix">
                    <b class="plan flL">授课安排：</b>
                    <p class="flL"><?php echo $val['target']; ?></p>
                </span>
                <div class="cpss_techer_button taR clearfix">
                    <a href="javascript:;" class="<?php echo $val['prompt_class']; ?>"
                       data-id="<?php echo $val['id']; ?>"
                       data-zdtalk="<?php echo $val['enable_zdtalk']; ?>"
                       data-qq="<?php echo $val['qq_group']; ?>"
                       ga-type="点击选课页_<?php echo $val['prompt_button']; ?>"
                       ga-title="<?php echo $val['name']; ?>"><?php echo $val['prompt_button']; ?></a>
                    <p class="mr20 flR color_ff4a4a"><?php echo $val['prompt_info']; ?></p>
                </div>
            </div>
            <!--- end cpss_techer_text -------------------->
        </div>
    <?php  endforeach;
else: ?>
    <div class="no_course_v400">
        <p>暂时没有阶段课程信息哦！~</p>
        <span><img src="<?php echo img_url('learning_center', 'ff_01.png'); ?>" alt=""></span>
    </div>
<?php endif; ?>