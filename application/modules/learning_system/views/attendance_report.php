<div class="main_contant_v422 ml70">
    <div id="main_item01_v422" style="width:240px;height: 240px;" data-checked="<?php echo $model['total']['checked'];?>" data-unchecked="<?php echo $model['total']['unchecked'];?>"></div>
    <div class="item_text_v422">
        <p class="fz18 color_444 taC">总出勤率</p>
        <p class="fz36 color_4bb866 taC fwB mt05"><?php echo $model['total']['percentage'];?>%</p>
        <p class="updated_v422">已更新<?php echo $model['total']['total'];?>次</p>
        <span class="item_line_v422"></span>
        <span class="clearfix report_text_v422">
            <p class="flL color_4bb866">已报到<?php if(!empty($model['total']['checked'])):?>
                <a href="<?php echo $model['total']['checked_link'];?>" target="_blank">
                    <b class="color_4bb866"><?php echo $model['total']['checked'];?>次</b>
                </a><?php else:?><b class="color_4bb866 tdN"><?php echo $model['total']['checked'];?>次</b><?php endif;?>
            </p>
            <p class="flR color_888">未报到<?php if(!empty($model['total']['unchecked'])):?>
                <a href="<?php echo $model['total']['unchecked_link'];?>" target="_blank">
                    <b class="color_888"><?php echo $model['total']['unchecked'];?>次</b>
                </a><?php else:?><b class="color_888 tdN"><?php echo $model['total']['unchecked'];?>次</b><?php endif;?>
            </p>
        </span>
    </div>
</div>
<div class="main_contant_v422 ml50">
    <div id="main_item02_v422" style="width:240px;height: 240px;" data-checked="<?php echo $model['live']['checked'];?>" data-unchecked="<?php echo $model['live']['unchecked'];?>"></div>
    <div class="item_text_v422">
        <p class="fz18 color_444 taC">直播出勤率</p>
        <p class="fz36 color_4bb866 taC fwB mt05"><?php echo $model['live']['percentage'];?>%</p>
        <p class="updated_v422">已更新<?php echo $model['live']['total'];?>次</p>
        <span class="item_line_v422"></span>
        <span class="clearfix report_text_v422">
            <p class="color_4bb866 taC">已报到<b class="color_4bb866 tdN"><?php echo $model['live']['checked'];?>次</b></p>
        </span>
    </div>
</div>
<div class="main_contant_v422 ml70 mb110 mt40">
    <div id="main_item03_v422" style="width:240px;height: 240px;" data-checked="<?php echo $model['practice']['checked'];?>" data-unchecked="<?php echo $model['practice']['unchecked'];?>"></div>
    <div class="item_text_v422">
        <p class="fz18 color_444 taC">一课一练完成率</p>
        <p class="fz36 color_4bb866 taC fwB mt05"><?php echo $model['practice']['percentage'];?>%</p>
        <p class="updated_v422">已开放<?php echo $model['practice']['total'];?>次</p>
        <span class="item_line_v422"></span>
        <span class="clearfix report_text_v422">
            <p class="flL color_4bb866">已完成<?php if(!empty($model['practice']['checked'])):?>
                <a href="<?php echo $model['practice']['checked_link'];?>" target="_blank">
                    <b class="color_4bb866"><?php echo $model['practice']['checked'];?>次</b>
                </a><?php else:?><b class="color_4bb866 tdN"><?php echo $model['practice']['checked'];?>次</b><?php endif;?>
            </p>
            <p class="flR color_888">未完成<?php if(!empty($model['practice']['unchecked'])):?>
                <a href="<?php echo $model['practice']['unchecked_link'];?>" target="_blank">
                    <b class="color_888"><?php echo $model['practice']['unchecked'];?>次</b>
                </a><?php else:?><b class="color_888 tdN"><?php echo $model['practice']['unchecked'];?>次</b><?php endif;?>
            </p>
        </span>
    </div>
</div>
<div class="main_contant_v422 ml50 mb110 mt40">
    <div id="main_item04_v422" style="width:240px;height: 240px;" data-checked="<?php echo $model['unit']['checked'];?>" data-unchecked="<?php echo $model['unit']['unchecked'];?>"></div>
    <div class="item_text_v422">
        <p class="fz18 color_444 taC">单元测试完成率</p>
        <p class="fz36 color_4bb866 taC fwB mt05"><?php echo $model['unit']['percentage'];?>%</p>
        <p class="updated_v422">已开放<?php echo $model['unit']['total'];?>次</p>
        <span class="item_line_v422"></span>
        <span class="clearfix report_text_v422">
            <p class="flL color_4bb866">已完成<?php if(!empty($model['unit']['checked'])):?>
                <a href="<?php echo $model['unit']['checked_link'];?>" target="_blank">
                    <b class="color_4bb866"><?php echo $model['unit']['checked'];?>次</b>
                </a><?php else:?><b class="color_4bb866 tdN"><?php echo $model['unit']['checked'];?>次</b><?php endif;?>
            </p>
            <p class="flR color_888">未完成<?php if(!empty($model['unit']['unchecked'])):?>
                <a href="<?php echo $model['unit']['unchecked_link'];?>" target="_blank">
                    <b class="color_888"><?php echo $model['unit']['unchecked'];?>次</b>
                </a><?php else:?><b class="color_888 tdN"><?php echo $model['unit']['unchecked'];?>次</b><?php endif;?>
            </p>
        </span>
    </div>
</div>