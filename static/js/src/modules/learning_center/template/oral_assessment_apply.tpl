<div class="myclassv222_alert_w800 oral_assessment_apply">
    <p class="myclassv222_alert_title_w800">申请口语测评</p>
    <span class="myclassv222_close dialog_close"></span>
    <p class="taC mt48 fz18 color_ff6e1f">恭喜您完成了<%= title %>所有口语课程，欢迎参与早道口语测评！</p>
    <a href="<%= master_domain %>T/JSAT" target="_blank" class="mt10 myclassv222_href">什么是口语测评？</a>
    <div class="myclassv222_form mt25">
        <ul>
            <li class="clearfix">
                <p class="flL taR w150 mr10">输入您的姓名：</p>
                <input type="text" id="reg_name" name="reg_name" class="myclassv222_form_input flL"/>
                <p class="flL color_ff0000 fz14 ml10 reg_name"></p>
            </li>
            <li class="clearfix mb45">
                <p class="flL taR w150 mr10">输入您的电话号码：</p>
                <input type="text" id="mobile" name="mobile" class="myclassv222_form_input flL"/>
                <p class="flL color_ff0000 fz14 ml10 mobile"></p>
                <b>* 口语测评将采取专业日语老师1对1电话测评的方式进行</b>
            </li>
            <li class="clearfix mb45">
                <p class="flL taR w150 mr10">您方便测评的时间：</p>
                <input type="text" id="hope_time" name="hope_time" class="myclassv222_form_input flL" value=""/>
                <input type="hidden" id="teacher_uid" name="teacher_uid"/>
                <p class="flL color_ff0000 fz14 ml10 hope_time"></p>
                <b>* 口语测评时间约5-10分钟，请确保该时间方便通话</b>
                <div class="oral_assessment_detial"></div>
            </li>
        </ul>
    </div>
    <div class="myclassv222_alert_button submit_oral_assessment">提交申请</div>
</div>