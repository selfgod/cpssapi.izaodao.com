<div class="right_w300_v422">
    <div class="widgetB_v422">
        <?php if($model['action'] != 'learn'): ?>
        <h2 class="cpss_h2 taL borderBottom00">学分排行</h2>
        <?php endif; ?>
        <h1 class="tool_title_v5" id="category_switch">
            <?php $i = 0;
            foreach ($meta['category'] as $name => $item): ?>
                <b class="google_event pb20<?php if (isset($item['current'])) echo ' current_v5'; ?>" data-name="<?php echo $name; ?>" ga-type="点击<?php echo $item['title']; ?>"><?php echo $item['title']; ?></b>
                <?php if($i === 0): ?>
                    <i class="line_v521"></i>
                <?php endif;?>
                <?php $i++;endforeach;?>
        </h1>
        <div id="rank_panel_body" class="tabList_v422 clearfix">
            <div class="cpss_item_con1" id="group_3_box">
                <div id="slide2" class="slideBox">
                    <?php echo $content;?>
                </div>
            </div>
        </div>
    </div>
</div>
