<div class="day_v5 calendar_week">
    <h1 class="live_v521">直播课表</h1>
    <?php foreach ($model['weekDate'] as $k => $item): ?>
    <p class="google_event day_switch_date<?php if ($k === 6): ?> mr20<?php endif; ?><?php if ($model['xdate'] == $item['date']): ?> current_v5 h39<?php endif; ?>"
    data-date="<?php echo $item['date']; ?>"
    ga-type="点击直播课表_周日历_<?php if (!empty(strtotime($item['name']))): echo '其他'; else: echo $item['name']; endif;?>" ga-title="<?php echo $item['date']; ?>">
        <?php echo $item['name']; ?><b class="fz12">(<?php echo $item['week']; ?>)</b>
        <?php if (isset($item['hasCourse']) && $item['hasCourse'] === TRUE): ?>
            <i class="calendar_tips_v5"><img src="<?php echo img_url('learning_center', 'tips_img_v5.png') ?>" alt=""></i>
        <?php endif; ?>
    </p>
    <?php endforeach; ?>
    <i class="line_number_v521"></i>
    <span class="calendar_v5 google_event" id="calendar" ga-type="点击直播课表_月日历">
        <img src="<?php echo img_url('learning_center', 'calendar.png?v=201708020810') ?>" alt="">
    </span>
    <?php echo $content; ?>
</div>
<input type="hidden" id="calendar_show" value="<?php echo img_url('learning_center', 'calendar_ov.png?v=201708020810'); ?>">
<input type="hidden" id="calendar_hide" value="<?php echo img_url('learning_center', 'calendar.png?v=201708020810'); ?>">