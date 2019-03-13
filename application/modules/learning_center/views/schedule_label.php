<?php if (intval($model['mode']) === 1): ?>
    <?php if (!empty($model['class_time'])): ?>
        <div class="screen_v5 pt15 class_time">
            <b>上课时间</b>
            <span class="ml20 google_event" ga-type="点击选课页_上课时间">
                <p data-id="0" class="current_screen_v5" ga-title="全部">全部</p>
                <?php foreach ($model['class_time'] as $time_key => $time_val): ?>
                    <p data-id="<?php echo $time_key; ?>" ga-title="<?php echo $time_val; ?>"><?php echo $time_val; ?></p>
                <?php endforeach; ?>
            </span>
        </div>
    <?php endif; ?>
    <?php if (!empty($model['class_status'])): ?>
        <div class="screen_v5 pb15 class_status">
            <b>开课状态</b>
            <span class="ml20 google_event" ga-type="点击选课页_开课状态">
                <p data-id="0" class="current_screen_v5" ga-title="全部">全部</p>
            <?php foreach ($model['class_status'] as $status_key => $status_val): ?>
                <p data-id="<?php echo $status_key; ?>" ga-title="<?php echo $status_val; ?>"><?php echo $status_val; ?></p>
            <?php endforeach; ?>
            </span>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php echo $content; ?>
