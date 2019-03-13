<?php $planClass = 'cpss_plan_list select_plan google_event'; ?>
<div class="cpss_plan">
    <h1 class="cpss_plan_title">我的学习目的是...</h1>
    <?php if (in_array(2, $model['planIdMap'])): ?>
        <a href="javascript:;" data-id="2" class="<?php echo $planClass; ?>" ga-type="点击选择学习目的" ga-title="兴趣/工作需要">
            <i class="cpss_plan_interest"></i>
            <p class="mt30">兴趣/工作需要</p>
        </a>
    <?php endif; ?>
    <?php
    if (in_array(1, $model['planIdMap']) && in_array(2, $model['planIdMap'])) {
        $planClass .= ' ml90';
    }
    ?>
    <?php if (in_array(1, $model['planIdMap'])): ?>
        <a href="javascript:;" data-id="1" class="<?php echo $planClass; ?>" ga-type="点击选择学习计划" ga-title="考试/升学需要">
            <i class="cpss_plan_exam"></i>
            <p class="mt30">考试/升学需要</p>
        </a>
    <?php endif; ?>
</div>
