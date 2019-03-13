<?php
$unActivate = $unexpire = $unnum = 0;
if ($model['used']['is_activate'] != 1) {
    $unActivate = 1;
}
if ($model['used']['is_activate'] == 1 && $model['used']['is_unexpire'] == 1) {
    $unexpire = 1;
}
if ($model['used']['is_activate'] == 1 &&
    $model['used']['is_unexpire'] != 1 &&
    $model['used']['unlimit_count'] != 1 &&
    intval($model['used']['remain_count']) == 0
) {
    $unnum = 1;
}
?>
    <p class="cpss_tips_text taC" data-unactivate="<?php echo $unActivate; ?>"
       data-unexpire="<?php echo $unexpire; ?>" data-unnum="<?php echo $unnum; ?>">
        <?php if ($model['used']['is_activate'] == 1 && $model['used']['is_unexpire'] == 1): ?>
            已于<?php echo date('Y年m月d日', strtotime($model['used']['expire'])); ?>过期 <a target="_blank"
                                                                                      href="<?php echo MASTER_DOMAIN; ?>"
                                                                                      class="color_4bb866">去购买
                ></a>
        <?php else: ?>
            <?php if ($model['used']['is_activate'] == 1) : ?>
                <?php if ($model['used']['unlimit_count'] == 1): ?>
                    不限次数
                <?php else: ?>
                    剩余预约次数：<b class="color_4bb866 rest_reservation_num"><?php echo $model['used']['remain_count']; ?></b>次
                <?php endif; ?>
                <?php if ($model['used']['unlimit_expire'] == 1): ?>
                    有效期：终身
                <?php else: ?>
                    有效期截止：<?php echo date('Y年m月d日', strtotime($model['used']['expire'])); ?>
                <?php endif; ?>
                <a target="_blank" href="<?php echo $model['used']['detail_href']; ?>" class="color_4bb866" ga-type="点击<?php echo $model['curricular_zh']; ?>_有效期截止次数使用规则">次数使用规则 ></a>
            <?php else: ?>
                商品需要激活，方可开始学习 <a href="/purchased" target="_blank" class="color_ff0000" ga-type="点击<?php echo $model['curricular_zh']; ?>_去激活">去激活 ></a>
            <?php endif; ?>
        <?php endif; ?>
    </p>