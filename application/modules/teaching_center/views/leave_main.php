<div class="tab1 mb30 holiday">
    <p class="pt40">
        <span>开始时间：</span>
        <input type="text" id="choose_start_time" name="start_time">
    </p>
    <p class="pt20">
        <span>结束时间：</span>
        <input type="text" data-max="15" name="end_time" id="choose_end_time" class="mr10" disabled>
        <i>提示：每次请假时间不能超过15天哦~</i>
    </p>
    <p class="pt20 clearfix">
        <span class="flL mt10">请假理由：</span>
        <textarea id="holiday_reason" placeholder="请填写你的请假理由"></textarea>
    </p>
    <p class="pt20">
        <span></span>
        <?php if($model['doing']=='submit'):?>
            <button class="sub_btn submit change-blue" data-type="leave">提 交</button>
        <?php else:?>
            <button class="sub_btn unsubmit" data-do="<?php echo $model['doing'];?>">提 交</button>
        <?php endif;?>
        <!-- error area start-->
        <i class="color_ff0000 submit_prompt hide"></i>
        <!-- error area end -->
    </p>
</div>