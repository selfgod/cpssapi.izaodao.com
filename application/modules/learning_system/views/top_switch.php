<div class="title_v422">
    <ul><?php if(isset($model['current_top'])) $ignoreTop = TRUE;?>
        <?php foreach ($meta['components'] as $name => $li):?>
            <li<?php if((isset($ignoreTop) && $model['current_top'] == $name) || (!isset($ignoreTop) && isset($li['current'])))
                echo ' class="title_current"';?> data-name="<?php echo $name;?>" ga-type="点击<?php echo $li['title'];?>" ga-title="<?php echo $li['title'];?>"><?php echo $li['title'];?></li>
        <?php endforeach;?>
    </ul>
    <?php if($meta['name'] == 'exercise'):?>
    <a href="<?php echo $model['error_record_link'];?>" class="error_v422" target="_blank"><i class="flL"><img src="<?php echo $meta['error_record_img'];?>" alt=""/></i><p class="flR">错题本</p></a><?php endif;?>
</div>