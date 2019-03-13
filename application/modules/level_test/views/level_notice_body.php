<div id="header">
    <div class="header_main">
        <h1 class="title_left">日语等级测试</h1>
    </div>
</div>
<?php if($model['data']['grade_id'] <= 9){
    $m = '0'. $model['data']['grade_id'];
}else{
    $m = $model['data']['grade_id'];
};?>
<div id="main">
    <div class="contant" >
        <!--升级-->
        <div class="instructions" >
            <span class="level_img"><img src="<?php echo img_url('level_test', 'level_little_on_'.$m.'.png') ?>" alt=""></span>
            <p class="level_text">L<?= $model['data']['grade_id'];?>等级测试</p>
            <p class="fz16 color_666">你即将参加水平测试，如果顺利通过，
                <?php if($model['data']['is_max_grade']):?>你将被定级为目前的最高日语水平<?php else:?>你将升级至L<?= $model['data']['grade_id']+1;?>等级开始选课、学习<?php endif;?></p>
            <div class="level_text_icon taL">
                <p>本次测试共计<?= $model['data']['question_num'];?>道题，预计需要<?= $model['data']['duration'];?>分钟，请保证网络畅通</p>
                <p>测试合格，将须在所定级别，选课、学习</p>
                <p>测试不合格，可以选择重新测试</p>
            </div>
<!--            <a href="/grade/main" class="button_line_blue start-test-btn mr10">重新选择</a>-->
            <a href="javascript:void(0);" class="button_blue start-test-btn generateExam" data-grade="<?= $model['data']['grade_id'];?>">开始测试</a>
        </div>
    </div>
</div>