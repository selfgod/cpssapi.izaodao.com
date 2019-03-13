<i class="icon_tips flL left45 top30"><?php echo $model['current_order']; ?></i>
<div class="evaluation_top_left_v5">
    <h3 class="color_3bac57"><?php echo $model['current_title']; ?></h3>
    <p class="fz14 color_888 disIb">上课时间：<?php echo $model['current_class_time']; ?></p>
    <?php if (!empty($model['teacher_name'])): ?>
        <p class="disIb fz14 color_888 ml20">上课教师：<?php echo $model['teacher_name']; ?></p>
    <?php endif; ?>
</div>
<?php if (!empty($model['oral_level']) && !empty($model['oral_level']['level'])): ?>
    <div class="evaluation_top_right_v5">
            <span class="level_v5 disIb"><?php echo $model['oral_level']['level'][0]; ?><?php if (isset($model['oral_level']['level'][2])): ?>
                    <b><?php echo $model['oral_level']['level'][2]; ?></b><?php endif; ?></span>
        <span class="disIb fz14 color_999 ml05">
            <p><?php echo $model['oral_level']['flag']; ?></p>
            <p class="mt15"><?php echo $model['oral_level']['level'][1]; ?></p>
        </span>
    </div>
<?php endif; ?>