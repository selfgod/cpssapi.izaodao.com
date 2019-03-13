<div class="null_v422" >
    <?php $curMeta = $meta['category'][$model['category']][$model['type']];?>
    <div class="null_v422_content mt100 mb215 clearfix <?php if(isset($curMeta['wclass'])) echo $curMeta['wclass'];?>">
        <span class="flL"><img src="<?php echo $meta['image']; ?>" alt=""/></span>
        <p class="flR null_v422_text">
            <?php echo $curMeta['title'];?>
            <?php if (isset($curMeta['button'])):?>
            <a href="<?php echo $curMeta['button']['href'];?>" class="button_green btnPrimary_v422" target="_blank"><?php echo $curMeta['button']['title'];?></a>
            <?php endif;?>
        </p>
    </div>
</div>