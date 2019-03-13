<h2 class="cpss_icon_02">总次数统计</h2>
<div class="cpss_number_list mb10">
    <ul class="clearfix cpss_number_list_title">
        <li class="w340 pl20">商品名称</li>
        <li class="w430 pl20">到期时间</li>
        <li class="w110 taC"><?php echo $model['title'];?>次数</li>
        <li class="w110 taC">已用次数</li>
        <li class="w108 taC">剩余次数</li>
    </ul>
    <?php if(empty($model['list'])):?>
        <p class="cpss_notsign">你还没有报名哦，赶快去报名吧！</p>
        <a href="<?php echo $model['emptyLink'];?>" target="_blank" class="button_green cpss_gosign">去报名</a>
    <?php else:?>
        <?php foreach ($model['list'] as $goods):?>
            <ul class="clearfix cpss_number_list_main">
                <li class="w300 pl20 lh23 right_line pr40">
                    <p class="cpss_title_list mt10"><?php echo $goods['name'];?></p>
                    <b class="fz12 color_999">报名时间：<?php echo date('Y/m/d', $goods['create_time']);?></b>
                </li>
                <li class="w430 pl20 right_line"><?php if($goods['is_activate']=='0'):?>未激活
                    <?php elseif(!empty($goods['unlimit_expire'])):?>不限
                    <?php else:?><?php echo date('Y/m/d', $goods['expire']);?><?php endif;?></li>
                <li class="w110 taC right_line"><?php if(!empty($goods['unlimit_count'])) echo '不限'; else echo $goods['count'];?></li>
                <li class="w110 taC right_line"><?php if(!empty($goods['unlimit_count'])) echo '--'; else echo $goods['used'];?></li>
                <li class="w108 taC color_333"><?php if(!empty($goods['unlimit_count'])) echo '不限'; else echo $goods['remain_count'];?></li>
            </ul>
        <?php endforeach;?>
        <ul class="clearfix cpss_number_list_main borderBottom00">
            <li class="w770 pl20 right_line taR pr20 color_666">汇总</li>
            <li class="w110 taC right_line"><?php if($model['hasUnlimitCount']) echo '不限'; else echo $model['summary']['total'];?></li>
            <li class="w110 taC right_line"><?php if($model['hasUnlimitCount']) echo '--'; else echo $model['summary']['used'];?></li>
            <li class="w108 taC color_333"><?php if($model['hasUnlimitCount']) echo '不限'; else echo $model['summary']['remain'];?></li>
        </ul>
    <?php endif;?>
</div>