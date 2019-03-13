<div id="header">
    <div class="header_main">
        <h1 class="title_left">日语等级测试</h1>
    </div>
</div>
<div id="main" class="pt30 pb30">
    <div class="result_ago">
        <p class="result_title">历史测评结果</p>
        <?php if($model['data']):?>
        <div class="result_ago_item clearfix">
            <div class="result_ago_item_title clearfix">
                <span>交卷时间</span>
                <span>正确率</span>
                <span>等级</span>
                <span>合格状态</span>
                <span>操作</span>
            </div>

            <ul class="result_ago_item_row">
                <?php foreach($model['data'] as $key => $val):?>
                <li class="clearfix">
                    <p class="color_666"><?= $val['submit_time']?></p>
                    <p class="color_666"><?= $val['correct_rate']/100?>%</p>
                    <p class="color_666"><?= $val['grade_name']?></p>
                    <p class="color_666"><?php if($val['is_pass']):?>合格<?php else:?>未合格<?php endif;?></p>
                    <a href="/grade/review?result_id=<?= $val['id']?>" class="review_btn">回顾</a>
                </li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php else:?>
        <p class="mt160">你还没有完成任何一套等级测试哦</p>
        <?php endif;?>
    </div>
</div>
<div class="footer">

</div>