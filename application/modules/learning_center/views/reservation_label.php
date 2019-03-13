<?php
$basic_class = 'screen_v5 pt15 clearfix';
if ($model['curricular'] === 'custom'):
    $basic_class = 'screen_v5 pt15 clearfix pb30';
?>
    <div class="svip_img_v5 pl100">
        <img src="<?php echo img_url('learning_center', 'ff_2.png'); ?>" class="flL" alt="">
        <span class="flL pt40 pl10">
        <p class="fz12 color_999">加入SVIP社群，可在群中提出定制课上课需求，请联系班主任获取SVIP社群号</p>
            <a href="http://wpa.qq.com/msgrd?v=3&uin=387719168&site=qq&menu=yes" target="_blank"><p class="teacher_qq" ga-type="点击<?php echo $model['curricular_zh']; ?>_班主任QQ">班主任QQ号：387719168</p></a>
    </span>
    </div>
<?php endif; ?>
<?php
if (!empty($model['fit_basic'])): ?>
    <div class="<?php echo $basic_class; ?> fit_basic">
        <b class="flL mt05">适合基础</b>
        <span class="flR google_event" ga-type="点击<?php echo $model['curricular_zh']; ?>_适合基础">
            <p data-id="0" class="w140 mr00 current_screen_v5" ga-title="全部">全部</p>
            <?php foreach ($model['fit_basic'] as $fk => $fb): ?>
                <p data-id="<?php echo $fk; ?>"
                   class="w140 mr00" ga-title="<?php echo $fb; ?>"><?php echo $fb; ?></p>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>
<?php if ($model['curricular'] !== 'custom'):
    if (!empty($model['curricular_category'])): ?>
        <div class="screen_v5 pb25 clearfix content_cat">
            <b class="flL mt05">内容分类</b>
            <span class="flR google_event" ga-type="点击<?php echo $model['curricular_zh']; ?>_内容分类">
                <p data-id="0" class="w140 mr00 current_screen_v5" ga-title="全部">全部</p>
                <?php foreach ($model['curricular_category'] as $ck => $cc): ?>
                    <p data-id="<?php echo $ck; ?>"
                       class="w140 mr00" ga-title="<?php echo $cc; ?>"><?php echo $cc; ?></p>
                <?php endforeach; ?>
        </span>
        </div>
    <?php endif; endif; ?>