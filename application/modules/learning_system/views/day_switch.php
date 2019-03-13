<?php for($i = 0; $i < 7; $i++): ?>
    <li class="part_v422 <?php if($model['weekday'][$i]['date']===$model['current']) echo 'haveclass_current'; ?>" data-date="<?php echo $model['weekday'][$i]['date']?>">
        <?php echo $model['weekday'][$i]['title'];?>
        <?php if (isset($model['weekday'][$i]['planned'])):?>
            <div class="mission_v422">
                <span class="mt04"><img src="<?php echo $meta['left_pic']; ?>" alt=""/></span>
                <span class="mission_v422_centen">有课</span>
                <span><img src="<?php echo $meta['right_pic']; ?>" alt=""/></span>
            </div>
        <?php endif;?>
    </li>
<?php endfor;?>
<li class="w59 part_v422 borderRight00" id="calendar_v422">
    <!--<li class="w59 part_v422 borderRight00 rili_ov" id="rili_v422">-->
    <img src="<?php echo img_url('learning_system','calendar.png'); ?>" class="mt12" alt="" />
</li>
<input type="hidden" id="calendar_show" value="<?php echo img_url('learning_system','calendar_ov.png'); ?>">
<input type="hidden" id="calendar_hide" value="<?php echo img_url('learning_system','calendar.png'); ?>">
