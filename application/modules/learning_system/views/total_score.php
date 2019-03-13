<p class="taC fz20 color_4bb866 mt55 mb20">学分：<b class="fz36 fwB"><?php echo $model['score'];?></b>分</p>
<div class="score_text_v422">
    <a href="<?php echo MASTER_DOMAIN . get_options('url_link', 'get_score');?>" class="borderRight00" target="_blank">如何得学分</a>
    <a href="<?php echo MASTER_DOMAIN . get_options('url_link', 'score_record');?>" target="_blank">学分记录</a>
</div>
<p class="info_text_v422">
    <i class="info_icon_v422 mr05">
        <img src="<?php echo $meta['icon'];?>" alt=""/>
    </i>
    <a href="<?php echo MASTER_DOMAIN . get_options('url_link', 'score_rule');?>" target="_blank">统计规则说明</a>
</p>