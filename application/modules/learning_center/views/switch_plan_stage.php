<?php
$current_plan_info = !empty($model['current_plan_info']) ? $model['current_plan_info'] : [];
$plan_stage = !empty($model['plan_stage']) ? $model['plan_stage'] : [];
$stage_data = !empty($model['stage_data']) ? $model['stage_data'] : [];
$max_plan_stage_id = !empty($model['max_plan_stage_id']) ? $model['max_plan_stage_id'] : 0;
?>
<?php if (!empty($current_plan_info)): ?>
    <p class="taC color_999 fz13">
        <?php if(intval($model['remain_count']) >= 0): ?>
        <b class="mr15">剩余选班次数: <?php echo $model['remain_count'];?></b>
        <?php endif;?>
        <?php if (intval($current_plan_info['unlimit_expire']) !== 1 && !intval($current_plan_info['expire'])): ?>
            商品需激活，方可开始学习　　
            <a href="/purchased" target="_blank" class="color_4bb866 tdN google_event" ga-type="点击主修课_去激活">去激活 ></a>
        <?php elseif (intval($current_plan_info['unlimit_expire']) !== 1 && date('Y-m-d H:i:s') > $current_plan_info['expire']): ?>
            已于<?php echo date('Y年m月d日', strtotime($current_plan_info['expire'])); ?>过期　　
            <a href="<?php echo MASTER_DOMAIN; ?>" target="_blank" class="color_4bb866 tdN">去购买 ></a>
        <?php elseif (intval($current_plan_info['unlimit_expire']) !== 1 && $current_plan_info['expire'] >= date('Y-m-d H:i:s')): ?>
            有效期截止：<?php echo date('Y年m月d日', strtotime($current_plan_info['expire'])); ?>　　
            <a target="_blank" href="/purchased/curricular/major" class="color_4bb866 tdN google_event" ga-type="点击主修课_有效期截止次数使用规则">次数使用规则 ></a>
        <?php else: ?>
            有效期：终身
        <?php endif; ?>
    </p>
<?php endif; ?>
<div class="cpss_info_item current_plan_id" data-id="<?php echo intval($current_plan_info['plan_id']); ?>">
    <?php if (!empty($plan_stage)): ?>
        <?php
        foreach ($plan_stage as $k => $v):
            $ul_class = 'borderTop00 borderBottom00 borderLeft00 borderRight00';
            $span_class = '';
            ?>
            <?php
            $group = ceil(($k + 1) * 0.5);
            if ((($k % 2) == 0 && ($group % 2) == 0) || (($k % 2) != 0 && ($group % 2) != 0)) {
                $li_class = 'cpss_info_item_after';
            } else {
                $li_class = 'cpss_info_item_before';
            }
            if ($k == 0 || ($k % 2) == 0) {
                if (isset($plan_stage[$k + 1])) {
                    if (isset($plan_stage[$k + 2])) {
                        if ($k == 0) {
                            $span_class = 'line_top right00';
                            $ul_class = 'borderTop00 borderBottom00 borderLeft00';
                        } else if (($group % 2) == 0) {
                            $ul_class = 'borderBottom00 borderRight00';
                        } else {
                            $ul_class = 'borderBottom00 borderLeft00';
                        }
                    } else {
                        if ($k == 0) {
                            $span_class = 'line_center';
                        } else if (($group % 2) == 0) {
                            $span_class = 'line_top right00';
                        } else {
                            $span_class = 'line_top left00';
                        }
                    }
                } else {
                    if ($k == 0) {
                        $span_class = '';
                    } else if (($group % 2) == 0) {
                        $span_class = 'line_small right00';
                    } else {
                        $span_class = 'line_small left00';
                    }
                }
                if ($k == 0) $ul_class .= ' mt70';
                ?>
                <ul class="<?php echo $ul_class; ?>">
                <?php if ($span_class != ''): ?>
                    <span class="<?php echo $span_class; ?>"></span>
                <?php endif; ?>
            <?php } ?>
            <?php
                $plan_stage_name = isset($stage_data[$v['plan_stage_id']]) ? $stage_data[$v['plan_stage_id']]['name'] : '';
                $plan_stage_level = isset($stage_data[$v['plan_stage_id']]) ? $stage_data[$v['plan_stage_id']]['level'] : '';
                $plan_stage_icon_off = isset($stage_data[$v['plan_stage_id']]) ? $stage_data[$v['plan_stage_id']]['icon_off'] : '';
                $plan_stage_icon = isset($stage_data[$v['plan_stage_id']]) ? $stage_data[$v['plan_stage_id']]['icon'] : '';
            ?>
            <li class="<?php echo $li_class; ?> google_event" data-id="<?php echo $v['plan_stage_id']; ?>"
                ga-type="点击计划阶段图标"
                ga-title="<?php echo $plan_stage_name; ?>">

                <?php if ($v['unlock_able'] == 0): ?>
                    <span class="cpss_info_roundImg plan_stage_lock_able"><img src="<?php echo $plan_stage_icon_off; ?>"></span>
                    <span class="cpss_lock forbid_lock" ga-type="点击锁头图标_不可解锁" ga-title="<?php echo $plan_stage_name; ?>"></span>
                    <div class="cpss_list_mask">
                        <p>未购买本阶段，无法解锁哦~</p>
                    </div>
                <?php else: ?>
                    <?php if ($v['is_unlocked'] == 1): ?>
                        <div class="cpss_list_mask">
                            <p>点这里，进行这个阶段的选课哦~</p>
                        </div>
                        <span class="cpss_info_roundImg plan_stage_unlocked"><img src="<?php echo $plan_stage_icon; ?>"></span>
                        <?php if ($v['plan_stage_id'] == $max_plan_stage_id): ?>
                            <span class="cpss_lock avatar_lock"><img src="<?php echo $model['headimgurl']; ?>" alt=""></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="cpss_lock_open curP">
                            <div class="cpss_list_mask">
                                <p>解锁本阶段，需要完成前一阶段的课程哦~</p>
                            </div>
                            <span class="cpss_info_roundImg plan_stage_unlock_able"><img src="<?php echo $plan_stage_icon_off; ?>"></span>
                            <span class="cpss_lock disabled_lock" ga-type="点击锁头图标_未解锁" ga-title="<?php echo $plan_stage_name; ?>"></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="cpss_cup_bg">
                    <?php if ($v['is_unlocked'] == 1): ?>
                        <?php if ($v['is_finish'] == 1): ?>
                            <span class="cpss_cup_open"></span>
                        <?php else: ?>
                            <span class="cpss_cup_disabled"></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="cpss_cup_disabled"></span>
                    <?php endif; ?>
                </div>
                <p class="cpss_cup_text">
                    <b><?php echo $plan_stage_level; ?></b>
                    <?php echo $plan_stage_name; ?>
                </p>
            </li>
            <?php if (($k % 2) == 1 || !isset($plan_stage[$k + 1])): ?>
            </ul>
        <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
