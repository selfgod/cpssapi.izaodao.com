<div class="cpss_menu green_bg h320">
    <div class="cpss_sortList clearfix">
        <ul>
            <?php foreach ($meta['category'] as $index => $item):?>
            <li class="mt45<?php if($index === count($meta['category'])-1) echo ' mr00';?>">
                <span class="cpss_sortbg">
                    <i><img src="<?php echo $item['img'];?>" alt=""></i>
                    <p class="color_bae5c3 fz14 pt20"><?php if(isset($model[$item['type']]['unlimit'])):?>不限次数</p>
                    <p class="fz16 tdU curP history" data-type="<?php echo $item['name'];?>">使用记录</p>
                    <?php else:?>可用次数/总次数</p><p class="fz16 tdU curP history" data-type="<?php echo $item['name'];?>"><?php if(isset($model[$item['type']])):?>
                        <?php echo $model[$item['type']]['remain'];?>/<?php echo $model[$item['type']]['total'];?>
                        <?php else:?>0/0<?php endif;?></p>
                    <?php endif;?>
                </span>
                <p class="mt10"><?php echo $item['title'];?></p>
            </li>
            <?php endforeach;?>
        </ul>
    </div>
</div>