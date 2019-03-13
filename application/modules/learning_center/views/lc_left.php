<div class="nav_left_v5 ga" ga-location="学习中心_左侧导航栏">
    <?php
    if (!empty($meta['new_guide_list'])):
        foreach ($meta['new_guide_list'] as $key => $value):
        ?>
        <div class="<?php echo $value['class']; ?> google_event" ga-type="点击操作帮助_查看" style="display: none">
            <i class="<?php echo $value['i_class']; ?>"></i>
            <span class="clearfix">
                <i class="flL mr10"><img src="<?php echo $value['image']; ?>" alt=""></i>
                <p class="fz16 color_333 flL mt03"><?php echo $value['name']; ?></p>
            </span>
            <p class="mt05"><?php echo $value['content_text']; ?></p>
            <span class="clearfix">
                <p class="fz12 color_ccc flL mt25 skip" style="cursor: pointer" ga-title="<?php echo $value['name']; ?>_跳过">跳过</p>
                <a href="javascript:void(0);" class="bg_3399ff shade_btn" data-step="step<?php echo $value['step']; ?>" ga-title="<?php echo $value['name']; ?>_继续">继续</a>
            </span>
        </div>
    <?php endforeach; endif; ?>

    <div class="shade_text top240 select_class" style="display: none">
        <i class="jiantou"></i>
        <p class="fz14 color_999">先选课，再上课！</p>
    </div>

    <div class="<?php echo $meta['div_class']; ?>">
        <div class="shade_v5" style="display: none"></div>
        <ul>
            <?php
            if (!empty($meta['column_list'])):
                foreach ($meta['column_list'] as $item):
                    ?>
                    <li class="<?php echo $item['class']; ?> google_event"<?php if (!empty($item['data-link'])): ?> data-link="<?php echo $item['data-link']; ?>"<?php endif; ?> ga-type="点击<?php echo $item['name']; ?>" data-ga_name="<?php echo $item['name']; ?>（日语）">
                        <?php if ($item['data-link'] === 'selectCourse/major'): ?>
                            <span class="mouse_icon" style="display: none">
                                <i class="circle_afd7ff"></i>
                                <i class="circle_e0efff"></i>
                                <i class="circle_f5faff"></i>
                                <i class="hand_icon"><img src="<?php echo img_url('learning_center', 'mouse.png') ?>" alt=""></i>
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo $item['href']; ?>"<?php if (!empty($item['target'])): ?> target="<?php echo $item['target']; ?>"<?php endif; ?>><i><img
                                        src="<?php echo $item['img']; ?>" alt=""></i><?php echo $item['name']; ?></a>
                    </li>
                <?php endforeach; endif; ?>
        </ul>
    </div>
    <div class="newbie_help" style="display: none">
        <a href="javascript:void(0);" class="newbie_help_close">&times;</a>
        <p>不知道如何操作？</p>
        <a href="javascript:void(0);" class="newbie_help_btn google_event" data-step="step1" ga-type="点击操作帮助_查看">查 看</a>
    </div>
</div>

