<?php if(intval($model['un_suspend'])!=1):?>
    <?php if(!empty($model['suspend_info'])):?>
        <div class="tab2 mb30">
            <p class="pt40">
                <span>开始时间：</span>
                <?php echo date('Y-m-d',strtotime($model['suspend_info']['start_time']));?>
            </p>
            <p class="pt20">
                <span>结束时间：</span>
                <?php echo date('Y-m-d',strtotime($model['suspend_info']['end_time']));?>
            </p>
            <p class="pt20 clearfix">
                <span class="flL mt10 mr05">休学理由：</span>
                <span class="flR w761 mt10"><?php echo $model['suspend_info']['reason'];?></span>
            </p>
            <p class="pt40">
                <span>&nbsp;</span>
                <?php if ($model['suspend_info']['status'] == 2 || $model['suspend_info']['status'] == 3): ?>
                    <button class="sub_btn">已结束</button>
                <?php else: ?>
                    <button class="sub_btn change-blue stop_suspend w110">停止休学</button>
                <?php endif; ?>
            </p>
        </div>
    <?php else:?>
        <div class="tab2 mb30 holiday">
            <p class="pt20">
                <span>开始时间：</span>
                <input type="text" id="choose_start_time" data-last="<?php echo date('Y-m-d', strtotime($model['last_expire'])); ?>" name="start_time">
            </p>
            <p class="pt20">
                <span>结束时间：</span>
                <input type="text" data-min="15" data-max="180" name="end_time" id="choose_end_time" class="mr10" disabled>
            </p>
            <p class="pt20 clearfix">
                <span class="flL mt10">休学理由：</span>
                <textarea id="holiday_reason" placeholder="请填写你的休学理由"></textarea>
                <b class="tab2_tips flR" style="height: 150px;">
                    1、针对学员账户，只有 <em>1次</em> 休学机会；<br>
                    2、每次休学时间不得少于 <em>15天</em>，不得大于 <em>180天</em>；<br>
                    3、休学期间您将无法进入学习中心，并且不支持直播课学习及录播课件下载；<br>
                    4、如果提前上课，请务必进行销假操作，否则无法学习已休学的课程；<br>
                    5、休学结束后，系统将自动为课程延期实际休学天数；<br>
                    6、请在课程有效期内申请休学，过期后，无法申请休学；
                </b>
            </p>
            <p class="pt20">
                <span> </span>
                <button class="sub_btn submit change-blue" data-type="suspend">提 交</button>
                <i class="color_ff0000 submit_prompt hide"></i>
            </p>
        </div>
    <?php endif;?>

<?php else:?>
    <div class="tab0 mb30">
        <div class="tab0_area">
            <img src="<?php echo img_url('public','ff_jingya.png');?>">
            <p><?php echo $model['un_suspend_prompt'];?></p>
        </div>
    </div>
<?php endif;?>
