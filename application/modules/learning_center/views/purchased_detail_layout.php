<div class="cpss_menu green_bg">
    <div class="cpss_user taC">
        <i class="number_iconBg"><img src="<?php echo $meta['category'][$model['system']]['img'];?>" alt=""></i>
        <p class="fz16 color_42b05d"><?php echo $meta['category'][$model['system']]['title'];?></p>
    </div>
</div>
<div class="white_bg_borB1px"></div>
<div id="p_detail_container" class="cpss_module pb30 mt30 clearfix">
    <h2 class="cpss_icon_02">次数使用规则</h2>
    <div class="cpss_detail_rule">
        <?php if($model['category']==='major'):?>
            <p>1、换班次数是指按班级扣次数，即每添加一个课程（班级），将会扣除一次次数。如您有20次换班次数，表示您可以添加20个主修课班级，每个班级会包含N节系列课。</p>
            <p>2、每个阶段首次选课，不扣次数</p>
            <p>3、每个阶段第二次选课起，开始扣次数。即您享有的主修课次数，实际是从第二次选课开始扣除次数的</p>
            <p>4、开课日期前将课程删除，返还1次次数（首次选课除外）</p>
            <p>5、开课后将课程删除，不返还次数</p>
            <p>6、过期后，剩余次数无法继续使用，因此，请在有效期内尽快使用剩余次数</p>
        <? else:?>
            <p>1、预约次数是指按节扣次数，即每预约一节课，扣除一次次数。如您有90次口语课次数，表示您可以预约90节口语课。</p>
            <p>2、成功预约后，如临时有事不能上课，请去我的课程-口语课下取消预约，否则未上课也会扣除1次预约次数</p>
            <p>3、上课时间开始前2小时，取消预约，返还1次预约次数</p>
            <p>4、过期后，剩余次数无法继续使用，因此，请在有效期内尽快使用剩余次数。</p>
            <p>5、当预约人数未达到最低上课人数时，此时课程将被自动取消，所使用的次数将自动返还。</p>
        <? endif;?>
    </div>
</div>
<input type="hidden" id="category" value="<?php echo $model['category'];?>">