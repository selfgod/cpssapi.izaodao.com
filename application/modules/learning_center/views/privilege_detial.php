<div class="cpss_module mt30 clearfix">
    <h2 class="cpss_icon_02">我的特权</h2>
    <?php if(!empty($model['privilege'])):
        foreach($model['privilege'] as $k=>$v):
            ?>
            <div id="point<?php echo $k;?>" class="cpss_sortItem mt20 mb30 clearfix">
        <span class="cpss_sortItem_img flL">
            <?php if(!empty($model['user_privilege_ids']) && in_array($v['id'],$model['user_privilege_ids'])):?>
                <img src="<?php echo $v['big_img'];?>" alt="">
            <?php else:?>
                <img src="<?php echo $v['big_off_img'];?>" alt="">
            <?php endif;?>
        </span>
                <span class="cpss_sortItem_text flL">
            <h2 class="color_333 boB_d2e8cb mb25"><?php echo $v['name'];?><b class="fz14 ml20 color_666"><?php echo $v['description'];?></b></h2>
            <p><?php echo $v['summary'];?></p>
        </span>
            </div>
        <?php endforeach;endif;?>
</div>