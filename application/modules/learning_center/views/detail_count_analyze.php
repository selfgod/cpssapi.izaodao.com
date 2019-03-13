<ul class="clearfix">
    <li class="w140 pl20">操作</li>
    <li class="w530">课程</li>
    <li class="w100 taC">次数变化</li>
    <li class="w300 pl20">变化原因</li>
</ul>
<?php if (empty($model['data'])): ?>
    <p class="cpss_norecord">还没有记录</p>
<?php else: ?>
    <?php foreach ($model['data'] as $audit): ?>
        <ul class="clearfix">
            <li class="w140 pl20 lh23">
                <p class="mt10"><?php echo $audit['action_name']; ?></p>
                <b class="fz12 color_999"><?php echo date('Y/m/d H:i', $audit['create_time']); ?></b>
            </li>
            <li class="w530 lh23">
                <?php if (!isset($audit['name'])): ?>
                    <p class="mt10">--</p>
                <?php else: ?>
                    <p class="mt10"><?php echo $audit['name']; ?></p>
                    <b class="fz12 color_999">主讲：<?php if (!empty($audit['teachers'])) echo implode('、', $audit['teachers']); ?></b>
                <?php endif; ?>
            </li>
            <li class="w100 taC right_line_e5e5e5 <?php if ($audit['num'] <= 0): ?>color_cc0000">
                <?php echo $audit['num']; ?>
                <?php else: ?>color_4bb866">+<?php echo $audit['num']; ?><?php endif; ?></li>
            <li class="w300 pl20 color_999 textOverflow"><?php echo $audit['desc']; ?></li>
        </ul>
    <?php endforeach; ?>
<?php endif; ?>

