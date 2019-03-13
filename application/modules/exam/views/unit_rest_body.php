<input type="hidden" id="remain_time" value="<?= $model['remain']?>">
<input type="hidden" id="paper_id" value="<?= $model['paper']['id']?>">
<div class="exam_v4_notice"><?= $model['notice']?></div>
<div class="ykyc_v4_con">
    <div class="exam_v4_result">
        <div class="exam_v4_rest1">
            <img src="<?= img_url('exam', 'img8.jpg');?>">
            <p>休息一下...</p>
        </div>
        <div class="exam_v4_rest2">
            <span id="minute_remain" style="margin-left:10px;letter-spacing: 20px;"></span>
            <span id="second_remain" style="margin-left:34px;letter-spacing: 20px;"></span>
        </div>
        <div class="exam_v4_rest3">
        	<span>
                <img src="<?= img_url('exam', 'tip.jpg');?>">休息时间为10分钟。第一部分已经交卷，倒计时过后将自动进入听解部分。<br>
你也可以点击下面的按钮马上开始考试</span>
            <a href="javascript:;" id="btn_restOver"  style="cursor:pointer;">休息好了，继续考试</a>
        </div>
    </div>
</div>