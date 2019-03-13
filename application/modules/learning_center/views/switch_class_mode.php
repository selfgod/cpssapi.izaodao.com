<h2 class="fz16 pt05 pb05 boB_e5e5e5"><span class="current_plan"
                                 data-id="<?php echo $model['plan_id']; ?>">
        <?php echo $model['plan_name']; ?>
    </span>
    > <span class="current_plan_stage"
            data-id="<?php echo $model['plan_stage_id']; ?>">
    <?php echo $model['plan_stage_name']; ?>
    </span></h2>
<div class="cpss_techer_title taC mt20 mb20 switch_class_mode">
    <p class="cpss_live<?php if (intval($model['mode']) === 1): ?> cpss_current<?php endif; ?>"
       data-mode="live" ga-type="点击选课页_直播标签">直播</p>
    <p class="cpss_playing<?php if (intval($model['mode']) === 2): ?> cpss_current<?php endif; ?>"
       data-mode="record" ga-type="点击选课页_录播标签">录播</p>
</div>