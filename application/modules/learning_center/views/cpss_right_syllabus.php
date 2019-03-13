<?php if(empty($model['dataList'])):?>
    <div class="myTime_listR_null" style="display:block;">
        <p class="mb15">没课，休息休息吧~~</p>
        <img src="<?php echo img_url('learning_center', 'myTime_listR_nullImg.png'); ?>">
    </div>
<?php else:?>
    <?php foreach ($model['dataList'] as $v):?>
        <div class="myTime_listR_item" style="display:block;">
            <h3><?php echo $v['schedule_name']; ?></h3>
            <div class="myTime_avatar clearfix">
                <span class="myTime_avatarImg mt23 ml20"><img src="<?php echo $v['small_pic']; ?>" alt=""/></span>
                <span class="myTime_avatarTitle mt20 ml20">
			        <p><?php echo $v['lesson_name']; ?></p>
			        <p><?php echo $v['start_time'].'-'.$v['end_time']; ?></p>
			        <p>主讲：<?php echo $v['teacher_name']; ?></p>
		        </span>
                <?php if($v['content_cat'] == '67'):?>
                    <a target="_blank" href="/curriculum/oral?type=reservation" class="myTime_avatarButtom">查看</a>
                <?php elseif($v['content_cat'] == '68'):?>
                    <a target="_blank" href="/curriculum/elective?type=reservation" class="myTime_avatarButtom">查看</a>
                <?php elseif($v['content_cat'] == '69'):?>
                    <a target="_blank" href="/curriculum/special?type=reservation" class="myTime_avatarButtom">查看</a>
                <?php else:?>
                    <a target="_blank" href="/learningsystem/schedule/<?=$v['schedule_id']?>?plan=<?=$v['plan_id']?>&stage=<?=$v['plan_stage_id']?>&date=<?=$v['date']?>&nav=book" class="myTime_avatarButtom">查看</a>
                <?php endif;?>
            </div>
        </div>
    <?php endforeach;?>
<?php endif;?>
