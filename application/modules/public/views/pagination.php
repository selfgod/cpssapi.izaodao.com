<div id="pagination" class="<?php echo $meta['class'];?>">
    <?php if($model['current_page'] - intval($meta['max']/2)>0) $first_page = $model['current_page'] - intval($meta['max']/2); else $first_page = 1;?>
    <?php if($model['total_page']>$first_page+$meta['max']-1) $last_page = $first_page+$meta['max']-1; else $last_page=$model['total_page'];?>
    <?php if($last_page-$first_page+1<$meta['max'] && $model['total_page']==$last_page) $first_page=$last_page-$meta['max']+1; if($first_page<1) $first_page=1;?>
    <?php if($first_page !== 1):?>
    <p data-name="first"><<</p>
    <p data-name="previous"><</p>
    <?php endif;?>
    <?php if($model['total_page'] !== 1):?>
    <?php for($i=$first_page; $i<=$last_page;$i++):?>
        <p<?php if($model['current_page'] === $i) echo ' class="'.$meta['currentClass'].'"';?>><?php echo $i;?></p>
    <?php endfor;?>
    <?php endif;?>
    <?php if($last_page !== $model['total_page']):?>
        <p class="mr00" data-name="next">></p>
        <p data-name="last" data-total="<?php echo $model['total_page'];?>">>></p>
    <?php endif;?>
</div>