<div class="noClass_v422">
    <h1 class="taC">今天没有新任务哦</h1>
    <p class="fz18 mt05 taC"><?php if($model['hasCompletedOne']) echo '休息一下或者回顾已完成的任务吧'; else echo '去完成已落下的任务吧';?></p>
    <div class="taskBtn_v422 clearfix">
        <a href="<?php echo $model['go_study_link'];?>" target="_blank" class="flL btnPrimary_v422 button_green">去上课</a>
        <a href="<?php echo $model['go_exercise_link'];?>" target="_blank" class="flL btnPrimary_v422 button_lineGreen ml10">去做题</a>
    </div>
</div>
<?php if(isset($model['nextLesson']) && !empty($model['nextLesson'])):?>
<div class="container_v422_tips taC fz16 color_444 mt45" data-timestamp="<?php echo $model['nextLesson']['start_timestamp'];?>">
    <b class="color_888">下次课提醒：</b><?php echo $model['nextLesson']['start_time'];?>  <?php echo $model['nextLesson']['name'];?>[主讲：<?php echo $model['nextLesson']['t_name'];?>]
    <?php if($model['nextLesson']['enable_zdtalk']['enable']==TRUE):?>
        <a href="javascript:void(0);" data-download="<?php echo $model['zd_download'];?>" data-zdtalk="<?php echo $model['nextLesson']['enable_zdtalk']['href']?>" class="classroom_v422 button_green ml20 curP w100 zdtalk">进入教室</a>
    <?php else:?>
        <a href="javascript:void(0);" class="classroom_v422 button_green ml20 curP room">教室</a>
    <?php endif;?>
</div>
<?php endif;?>