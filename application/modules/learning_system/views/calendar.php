<!-- start 日历 -->
<div id="calendar_panel" class="calendar_v422" style="display: none;">
    <!-- start loahideg -->
    <!--<div class="load_v422_30"></div>-->
    <!-- start loahideg -->
    <div class="calendar_v422_title clearfix">
        <span class="flL year_v422">
            <b class="flL curP" id="last_year"><img src="<?php echo img_url('learning_system','page_left_v422.png?v=201708020810'); ?>" alt=""/></b>
            <p class="flL mt00" id="current_year"><?php echo $model['current_year'];?></p>
            <b class="flL curP" id="next_year"><img src="<?php echo img_url('learning_system','page_right_v422.png?v=201708020810'); ?>" alt=""/></b>
        </span>
        <span class="flR day_v422">
            <b class="flL curP" id="last_month"><img src="<?php echo img_url('learning_system','page_left_v422.png?v=201708020810'); ?>" alt=""/></b>
            <p class="flL mt00" id="current_month"><?php echo $model['current_month'];?></p>
            <b class="flL curP" id="next_month"><img src="<?php echo img_url('learning_system','page_right_v422.png?v=201708020810'); ?>" alt=""/></b>
        </span>
    </div>
    <div class="week_v422">
        <ul>
            <li>日</li>
            <li>一</li>
            <li>二</li>
            <li>三</li>
            <li>四</li>
            <li>五</li>
            <li>六</li>
        </ul>
    </div>
    <div class="number_v422 clearfix">
        <ul id="calendar_detail">

        </ul>
    </div>
</div>
<!-- end 日历 -->