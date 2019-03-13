<div class="detail">
    <?php if(!empty($model['leave_list'])):?>
    <p class="fz18 boB_e5e5e5 pb20">我的假条</p>
    <div class="detail_th clearfix">
        <p class="w182 flL ">次数</p>
        <p class="w182 flL ">请假时间</p>
        <p class="w454 pl40 flL ">请假理由</p>
        <p class="pl40 flL ">请假状态</p>
    </div>
        <?php foreach($model['leave_list'] as $key=>$val):?>
        <div class="detail_td">
            <div class="detail_border clearfix">
                <p class="detail_td1">第<?php echo intval(count($model['leave_list'])-$key);?>次</p>
                <div class="detail_td2"><img src="<?php echo img_url('teaching_center' , 'timeline.png');?>" class="timeline">
                    <p><?php echo date('Y/m/d',strtotime($val['start_time']));?></p>
                    <p><?php echo date('Y/m/d',strtotime($val['end_time']));?></p>
                </div>
                <p class="detail_td3"><?php echo $val['reason'];?></p>
                <p class="detail_td4">
                <?php if($val['status']==1):?>
                    <a href="javascript:;" class="cancel_leave" data-leave-id="<?php echo $val['id'];?>">销假</a>
                <?php elseif($val['status']==2):?>
                    已结束
                <?php elseif($val['status']==3):?>
                    已销假
                <?php endif;?>
                </p>
                <img src="<?php echo img_url('teaching_center' , 'corner.jpg');?>" class="bg_corner">
            </div>
        </div>
        <?php endforeach;?>
    <?php else:?>
    <p class="detail0">还没有请过假，继续保持哦~</p>
    <?php endif;?>
    <img src="<?php echo img_url('teaching_center' , 'detail_bg.png');?>" class="detail_bg">
</div>