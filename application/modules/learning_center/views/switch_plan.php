<?php
if (!empty($model['have_plan'])):
    foreach ($model['have_plan'] as $item):?>
        <p class="google_event<?php if (intval($item['plan_id']) === intval($model['current_plan_id'])) echo ' current_teb_v5';?>" data-id="<?php echo $item['plan_id'];?>" ga-type="点击切换学习计划" ga-title="<?php echo $item['name']; ?>"><?php echo $item['name']; ?></p>
    <?php endforeach;endif; ?>