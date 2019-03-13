<div class="ykyc_v4_con pos_r">
    <div class="exam_v4_result">
        <div class="exam_v4_befTitle">考前须知</div>
        <div class="exam_v4_befDiv">
            <b>考试内容和时间</b>
            <div class="txt_head"><span class="span1">第一部分</span><span class="span2">第二部分</span></div>
            <table class="exam_v4_befDiv_table" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <th width="25%" style="border-left:none;">单词</th>
                    <th width="25%">文法</th>
                    <th width="25%">读解</th>
                    <th width="25%">听解</th>
                </tr>
                <tr class="tr_line">
                    <td style="border-left:none;">共<?= $model['word_num'];?>题</td>
                    <td>共<?= $model['grammar_num'];?>题</td>
                    <td>共<?= $model['reading_num'];?>题</td>
                    <td>共<?= $model['listening_num'];?>题</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-left:none;">共<?= $model['total_time_1'];?>分钟</td>
                    <td>共<?= $model['total_time_2'];?>分钟</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="exam_v4_befNotice">
            <span>注意事项</span>
            <p><?= $model['tips'];?></p>
        </div>
        <div class="exam_v4_befLink">
            <a id="start" href="javascript:;" data-paper="<?= $model['id']?>" data-schedule="<?= $model['schedule_id']?>" data-lesson="<?= $model['lesson_id']?>" class="btn">开始考试（05）</a>
        </div>
    </div>
</div>