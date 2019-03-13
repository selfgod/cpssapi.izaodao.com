<!--有用户信息-->
<?php if($model['data']):?>
<div class="banner">
    <div class="banner_auto">
        <div class="grade_card taC">
            <?php if($model['data']['grade_id'] <= 9){
                $m = '0'. $model['data']['grade_id'];
            }else{
                $m = $model['data']['grade_id'];
            };?>
            <span class="class_big_icon"><img src="<?php echo img_url('level_test', 'level_big_on_'.$m.'.png') ?>" alt=""></span>
            <?php if($model['is_max_grade'] && $model['is_pass_max']):?>
                <p class="fz16 mt60">恭喜你，完成最高级别的测试</p>
                <p class="fz16 mt05">要继续加强听说读写的能力哦</p>
            <?php else:?>
                <p class="fz28 mb05"><?php if($model['c_grade_type'] == 1):?> 恭喜你，首次测试定级为
                    <?php elseif ($model['c_grade_type'] == 2):?>恭喜你升级至<?php else:?>你的日语水平相当于<?php endif;?></p>
                <p class="fz36 mb10"><?php if($model['data']['grade_id'] == 1):?>零基础<?php else:?>L<?= $model['data']['grade_id'];?><?php endif;?></p>
                <p class="fz16">目前适合学习L<?= $model['data']['grade_id'];?>等级课程 </p>
            <?php endif;?>
        </div>
        <a href="/grade/examList" target="_blank" class="grade_btn_history">查看历史测评结果</a>
    </div>

    <?php  if($model['c_grade_type'] || ($model['is_max_grade'] && $model['is_pass_max'])):?>
    <div class="banner_star">
            <span class="flL">
                <i class="srar-01"><img src="<?php echo img_url('level_test', 'star-01.png') ?>" alt=""></i>
                <i class="srar-02"><img src="<?php echo img_url('level_test', 'star-02.png') ?>" alt=""></i>
                <i class="srar-03"><img src="<?php echo img_url('level_test', 'star-03.png') ?>" alt=""></i>
            </span>
        <span class="flR mr140">
                <i class="srar-04"><img src="<?php echo img_url('level_test', 'star-04.png') ?>" alt=""></i>
                <i class="srar-05"><img src="<?php echo img_url('level_test', 'star-05.png') ?>" alt=""></i>
                <i class="srar-06"><img src="<?php echo img_url('level_test', 'star-06.png') ?>" alt=""></i>
            </span>
    </div>
    <div class="banner_rotation"></div>
    <?php endif;?>
</div>
<?php endif;?>
<div class="contant level_v530" id="indexContant">
    <?php if($model['data']):?>
        <!-- 有级别-->
        <h1 class="grade_text clearfix">等级分析</h1>
        <div class="contant_text flL">
            <h2 class="grade_text">学习建议</h2>
            <p class="suggest_text">
                <?= $model['data']['learn_suggest_content'];?>
            </p>
            <?php if($model['apply']):?>
                <p class="fz14 color_999 taC">你已申请调级，工作日17:30前处理调级申请，遇周末或节假日顺延至下一个工作日</p>
                <a href="javascript:void (0);" class="infoLevel_btn_02 button_Hui curD">已申请调级</a>
            <?php else:?>
                <p class="fz14 color_999 taC">如认为当前等级不合适，可申请调级</p>
                <a href="javascript:void (0);" class="infoLevel_btn_02 button_line_blue  applyChangeGrade" >申请调级</a>
            <?php endif;?>
        </div>
        <div class="contant_text flR">
            <h2 class="grade_text">选课建议</h2>
            <p class="suggest_text">
                <?= $model['data']['course_suggest_content'];?>
            </p>
            <?php if($model['is_selection']):?>
                <a href="/study/#/chooseClass" class="infoLevel_btn_03 button_blue">去选课</a>
            <?php endif;?>
        </div>
    <?php else:?>
<!--无级别-->
            <div class="graded-test pb20">
                <p class="fz36 color_333 mt40 mb40 taC">日语等级测试</p>
                <p class="taC color_ff4a4a fz14 mb30">为保证你的学习体验，请你在2018年1月10日前完成日语水平定级测试。从2018年1月10日零时起，未评定等级的同学需要先定级，再按等级选课哦</p>
                <div class="graded-test-box clearfix">
                    <div class="graded-test-box-img"><img src="<?php echo img_url('level_test', 'level_big_no.png') ?>" alt=""></div>
                    <div class="graded-test-box-text taC" id="ChoseBasics">
                        <p class="fz16 color_666 fwB mt20">完成日语水平测试，划定你的日语等级，为你提供更精准的学习建议和选课建议</p>
                        <p class="color_ff7e00 fz14 mt35 mb25">开始测试前，请准确选择你的日语基础</p>
                        <a href="#" class="button_blue basics-btn have_no_basic">我是零基础</a>
                        <a href="/grade/generate" class="button_blue basics-btn ml20  have_basic">我有基础</a>
                    </div>
                </div>
            </div>
    <?php endif;?>
<!--    ajax等级-->
    <div id="levels_content"></div>
</div>
