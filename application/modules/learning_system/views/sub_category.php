<div class="<?php echo $meta['class'];?> sub_category">
    <ul><?php if(isset($model['current_sub'])) $ignoreSub = TRUE;?>
        <?php foreach ($meta['components'] as $index => $li):?>
            <li class="<?php if($index === count($meta['components']) -1) echo 'borderRight00 ';
            if((isset($ignoreSub) && $model['current_sub'] == $li['name']) || (!isset($ignoreSub) && isset($li['current']))) echo 'current_v422 ';?>" data-name="<?php echo $li['name'];?>">
                <?php echo $li['title'];?></li>
        <?php endforeach;?>
    </ul>
</div>