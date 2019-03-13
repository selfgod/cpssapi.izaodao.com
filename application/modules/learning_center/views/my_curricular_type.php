<div<?php if (!empty($model['curricular_class'])): ?> class="<?php echo $model['curricular_class']; ?>"<?php endif; ?>>
    <?php if (!empty($model['class_type'])): ?>
        <div class="teb_title_v5 my_curricular_type">
            <?php foreach ($model['class_type'] as $key => $val): ?>
                <p class="google_event<?php if ($key == $model['current_type']) echo ' current_v5'; ?>"
                   data-type="<?php echo $key; ?>"
                   ga-type="点击<?php echo $model['curricular_zh'] . '_' . $val; ?>"><?php echo $val; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>