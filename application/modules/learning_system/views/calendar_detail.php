<?php for($i=1; $i<=7 - $model['firstWeekDays']; $i++):?>
    <li></li>
<?php endfor;?>
<?php for($i=1; $i<=$model['totalDays']; $i++):?>
    <li<?php if(isset($model['selected']) && $i === $model['selected']) {echo ' class="today_v422"';}?>><?php echo $i;?>
    <?php if(isset($model['calendar'][$i])):?>
            <?php $class='';if($model['calendar'][$i] === 0):?>
                <?php $class = 'number_v422_hasClass';?>
            <?php elseif ($model['calendar'][$i] === 1):?>
                <?php $class = 'number_v422_right';?>
            <?php else:?>
                <?php if(isset($model['selected']) && $i === $model['selected']) $class = 'number_v422_status_w'; else $class = 'number_v422_status';?>
            <?php endif;?><i class="<?php echo $class;?>"></i>
    <?php endif;?></li>
<?php endfor;?>
<?php for($i=1; $i<=$model['stuffingDays']; $i++):?>
    <li></li>
<?php endfor;?>