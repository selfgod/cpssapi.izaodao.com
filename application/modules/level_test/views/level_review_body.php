<div id="header">
    <div class="header_main">
        <h1 class="title_left"><?php echo $model['grade_exam_name'];?></h1>
<!--        <a href="/grade/examList" class="test-result-btn">查看测试结果</a>-->
        <p class="flR fz18 color_fff mt22">正确率：<?php echo $model['correct_rate']/100;?>%</p>
    </div>
</div>
<input type="hidden" id="last_cate" value="reading">
<input type="hidden" id="default_cate" value="word">
<input type="hidden" id="result_id" value="<?= $_GET['result_id']?>">

<div id="main">
    <div class="contant clearfix">
        <div class="exam_navi clearfix ">
            <ul>
                <li class="current js_cate_tab subject_1 js_main" data-cate="word" >单词</li>
                <li class="js_cate_tab subject_1 js_main" data-cate="grammar">文法</li>
                <li class="js_cate_tab subject_1 js_main" data-cate="reading">读解</li>
            </ul>
            <span class="exam_none"></span>
        </div>
        <div class="contant_left" id="detail_content">

        </div>

        <div class="contant_right">
            <p class="scantron">
                <b class="color_888 flL">答题卡</b>
                <b class="flR color_aaa ffA fz14"></b>
            </p>
<!-- 答题选项卡-->
            <div class="exam_navi_small clearfix" id="question_nums">
                <ul>
                    <li class="current_small js_cate_tab card_1" data-cate="word">单词</li>
                    <li class="js_cate_tab card_1" data-cate="grammar">文法</li>
                    <li class="js_cate_tab borderRight00 card_1" data-cate="reading">读解</li>
                </ul>
            </div>
<!--答题卡具体选项-->

            <div id="question_num"></div>

        </div>
    </div>
</div>