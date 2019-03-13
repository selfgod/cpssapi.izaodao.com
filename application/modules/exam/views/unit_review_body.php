<input type="hidden" id="paper_id" value="<?= $model['paper']['id']?>">
<input type="hidden" id="default_cate" value="<?= $model['default_cate']?>">
<div class="exam_v4_notice"><?= $model['notice']?></div>
<div class="exam_v4_navi">
    <div class="exam_v4_navi1 prs_r">
        <?php foreach ($model['cate'] as $name => $exist):?>
            <?php if($model['default_cate'] === $name):?>
                <div class="exam_v4_nav1_1 js_cate_tab js_main" data-cate="<?= $name?>"><?= $model['cate_title'][$name]?></div>
            <?php else:?>
                <?php if($exist):?>
                    <div class="exam_v4_nav1 js_cate_tab js_main" data-cate="<?= $name?>"><?= $model['cate_title'][$name]?></div>
                <?php else:?>
                    <div class="exam_v4_nav1 exam_v4_nav11"><?= $model['cate_title'][$name]?></div>
                <?php endif?>
            <?php endif;?>
        <?php endforeach;?>
        <div class="exam_v4_nav2">
            <div class="exam_v4_sound fl" id="exam_v4_sound" style="vertical-align: middle; margin-top: 8px; display:none"></div>
            <a href="/exam/unitTest/result?paper_id=<?= $model['paper']['id']?>"></a>
        </div>
    </div>
</div>
<div class="ykyc_v4_con">
    <div class="ykyc_v4_con_multi fl">
        <div class="ykyc_v4_ti" id="detail_content"></div>
    </div>
    <div class="ykyc_v4_con_answer_done fl pos_r">
        <div class="ykyc_v4_answer_title" style="margin-bottom:0px;">
            <span class="fl">答题卡</span>
            <div class="exam_v4_answer_r fr">
                <p>得分：<b><?= $model['score']?></b> /<?= $model['total_score']?></p>
<!--                <div class="exam_v4_answer_r1"></div>-->
            </div>
        </div>
        <div id="question_nums" class="exam_v4_answer_tab">
            <?php foreach ($model['cate'] as $name => $exist):?>
                <?php if($model['default_cate'] === $name):?>
                    <div class="exam_v4_answer_nav1_1 js_cate_tab" data-cate="<?= $name?>" <?php if($name==='listening'):?>style="border-right:0px;"<?php endif;?>><?= $model['cate_title'][$name]?></div>
                <?php else:?>
                    <?php if($exist):?>
                        <div class="exam_v4_answer_nav1 js_cate_tab" data-cate="<?= $name?>" <?php if($name==='listening'):?>style="border-right:0px;"<?php endif;?>><?= $model['cate_title'][$name]?></div>
                    <?php else:?>
                        <div class="exam_v4_answer_nav1" style="color:#B7B7B7;<?php if($name==='listening'):?> border-right:0px;<?php endif;?>"><?= $model['cate_title'][$name]?></div>
                    <?php endif?>
                <?php endif;?>
            <?php endforeach;?>
        </div>
        <div id="question_num"></div>
    </div>
</div>