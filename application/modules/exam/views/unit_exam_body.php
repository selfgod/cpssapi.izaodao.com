<?php if(isset($model['over_limit'])):?>
    <input type="hidden" id="over_limit" value="1">
<?php else:?>
    <input type="hidden" id="paper_id" value="<?= $model['paper']['id']?>">
    <input type="hidden" id="default_cate" value="<?= $model['default_cate']?>">
    <input type="hidden" id="last_cate" value="<?= $model['last_cate']?>">
    <input type="hidden" id="part1_last_cate" value="<?= $model['part1_last_cate']?>">
    <input type="hidden" id="part1_num" value="<?= $model['paper']['part1_num']?>">
    <input type="hidden" id="part2_num" value="<?= $model['paper']['part2_num']?>">
    <input type="hidden" id="part1_submit" value="<?= $model['part1_submit']?>">
    <input type="hidden" id="part2_remain_time" value="<?= $model['part2_remain_time']?>">
    <input type="hidden" id="exam_status" value="<?= $model['exam_status']?>">
    <input type="hidden" id="part1_remain_time" value="<?= $model['part1_remain_time']?>">
    <div class="exam_v4_notice"><?= $model['notice']?></div>
    <div class="exam_v4_navi">
        <div class="exam_v4_navi1 prs_r">
            <?php foreach ($model['cate'] as $name => $exist):?>
                <?php if($model['default_cate'] === $name):?>
                    <div class="exam_v4_nav1_1 js_cate_tab js_main" data-cate="<?= $name?>"><?= $model['cate_title'][$name]?></div>
                <?php else:?>
                    <?php if($exist):?>
                        <div class="exam_v4_nav1 <?php if($name==='listening'):?>exam_v4_nav11<?php else:?>js_cate_tab js_main<?php endif;?>" data-cate="<?= $name?>"><?= $model['cate_title'][$name]?></div>
                    <?php else:?>
                        <div class="exam_v4_nav1 exam_v4_nav11"><?= $model['cate_title'][$name]?></div>
                    <?php endif?>
                <?php endif;?>
            <?php endforeach;?>
            <div class="exam_v4_nav2">
                <div class="exam_v4_sound fl" id="exam_v4_sound" style="vertical-align: middle; margin-top: 8px;"></div>
                <div><span id="part_title"><?= $model['part1_str']?></span> 剩余时间：<span id="time_remain">00小时00分00秒</span></div>
            </div>
        </div>
    </div>
    <div class="ykyc_v4_con">
        <div class="ykyc_v4_con_multi fl">
            <div class="ykyc_v4_ti" id="detail_content"></div>
        </div>
        <div class="ykyc_v4_con_answer_done fl pos_r">
            <div class="ykyc_v4_answer_title">
                <span class="fl">答题卡</span>
                <p class="fr"><span id="answer_num"><?= $model['answer_num']?></span> / <span id="part_total">0</span></p>
            </div>
            <div id="question_nums" class="exam_v4_answer_tab">
                <?php foreach ($model['cate'] as $name => $exist):?>
                    <?php if($model['default_cate'] === $name):?>
                        <div class="exam_v4_answer_nav1_1 js_cate_tab" data-cate="<?= $name?>" <?php if($name==='listening'):?>style="border-right:0px;"<?php endif;?>><?= $model['cate_title'][$name]?></div>
                    <?php else:?>
                        <?php if($exist):?>
                            <div class="exam_v4_answer_nav1 <?php if($name==='listening'):?>exam_v4_answer_nav_disable<?php else:?>js_cate_tab<?php endif;?>" data-cate="<?= $name?>" <?php if($name==='listening'):?>style="border-right:0px;"<?php endif;?>><?= $model['cate_title'][$name]?></div>
                        <?php else:?>
                            <div class="exam_v4_answer_nav1 exam_v4_answer_nav_disable" <?php if($name==='listening'):?>style="border-right:0px;"<?php endif;?>><?= $model['cate_title'][$name]?></div>
                        <?php endif?>
                    <?php endif;?>
                <?php endforeach;?>
            </div>
            <div id="question_num"></div>
        </div>
    </div>
<?php endif;?>
