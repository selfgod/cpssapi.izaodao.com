<input type="hidden" id="submitted" value="<?= $model['xuefen']?>">
<input type="hidden" id="schedule_id" value="<?= $model['schedule_id']?>">
<input type="hidden" id="lesson_id" value="<?= $model['lesson_id']?>">
<input type="hidden" id="plan_id" value="<?= $model['plan_id']?>">
<input type="hidden" id="plan_stage_id" value="<?= $model['plan_stage_id']?>">
<input type="hidden" id="paper_id" value="<?= $model['paper']['id']?>">
<input type="hidden" id="right_per" value="<?= $model['right_per']?>">
<div class="ykyc_v4_con pos_r">
    <div class="exam_v4_result">
        <div class="exam_v4_result_f">
            <b>试题报告</b>
        </div>
        <div class="exam_v4_s_con">
            <div class="exam_v4_s fl">
                <div class="exam_v4_sr fl">
                    <div class="exam_v4_s_self"><span><?= $model['score']?></span>分</div>
                    <div class="exam_v4_s_all">满分<?= $model['total_score']?>分</div>
                </div>
                <div class="exam_v4_ss fl">
                    <ul>
                        <li><span>答对题数：</span><?= $model['right_num']?>/<?= $model['paper']['total_num']?>道</li>
                        <li><span>平均分：</span><?= $model['avg_score']?>分</li>
                        <li><span>击败考生：</span><?= $model['win_per']?>%</li>
                        <li><span>用时：</span><?= $model['use_time']?></li>
                    </ul>
                </div>
            </div>
            <div class="exam_v4_e fr">
                <div class="exam_v4_e_title">当前各分数段考生分布</div>
                <div class="exam_v4_e_con"><img src="<?= $model['scoreChart']?>"></div>
            </div>
        </div>
        <div class="exam_v4_result_f1">
            <b>试题回顾</b>
            <img src="<?= img_url('exam', 'bt22.jpg'); ?>">
            <a href="/exam/unitTest/review?paper_id=<?= $model['paper']['id']?>">查看回顾详情</a>
        </div>
    </div>
    <div class="exam_v4_result2">
        <?php foreach ($model['questions'] as $title => $item):?>
            <div class="db">
                <?php if(empty($item)):?>
                    <div class="no">本次考试没有<?= $title?>部分</div>
                <?php else:?>
                    <div class="exam_v4_result2_bo fl">
                        <div class="exam_v4_result2_boT">
                            <span class="w90"><?= $title?></span>
                            <p>正确率：<b><?= $item['right_per']?>%</b></p>
                        </div>
                        <div class="exam_v4_result2_boL">
                            <ul>
                                <li>答对<br><b class="b_gr"><?= $item['right_num']?></b></li>
                                <li>答错<br><b class="b_red"><?= $item['wrong_num']?></b></li>
                                <li class="bo_n">未答<br><?= $item['undo_num']?></li>
                            </ul>
                        </div>
                        <?php foreach ($item['questions'] as $row):?>
                            <div class="ykyc_v4_answer_ka1">
                                <?php foreach ($row as $i => $status):?>
                                    <a class="datika <?php if($status===1):?>now_choose<?php elseif ($status === 0): ?>wrong<?php endif; ?> fl"><?= $i?></a>
                                <?php endforeach;?>
                            </div>
                        <?php endforeach;?>
                    </div>
                <?php endif;?>
            </div>
        <?php endforeach;?>
    </div>
</div>