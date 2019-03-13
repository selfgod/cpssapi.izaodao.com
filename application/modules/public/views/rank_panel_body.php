<!--<div class="tabList_v422 clearfix">-->
<?php $zname = $model['type'] === 'total' ? '总' : '周'; ?>
<p class="clearfix cpss_weekCredit">
    <b><?php echo $zname;?>学分 : <?php echo $model['score'];?>分</b>
    <b class="ml10">排名: <?php if($model['rank'] === 0) echo '未计入名次'; else echo $model['rank'].'名';?></b>
</p>
<!---
<span class="cpss_learn">
    <p class="ranking_student">111学员</p>
    <p class="ranking_credit">学分</p>
</span>
----->
<!---tabBar 开始--------------------------------------------------->
<div class="tabBar">
    <?php if(intval($model['total_page']) === 0):?>
        <div class="c_txt"><?php if($model['type']=='week') echo '本周';?>还没有人获得学分，继续加油吧！</div>
    <?php else:?>
    <!---conWrap 开始--------------------------------------------------->
    <div class="conWrap">
        <!-----con 开始-------------------------------->
        <div class="con">
            <div class="studentlist" data-current="<?php echo $model['current'];?>" data-total="<?php echo $model['total_page'];?>">
                <?php echo $content;?>
            </div>
        </div>
        <!-----con 结束-------------------------------->
    </div>
    <?php endif;?>
    <!---conWrap 结束--------------------------------------------------->
    <a class="google_event prev_zxf <?php if($model['current'] === 1) echo 'prevStop';?>" href="javascript:void(0)" ga-type="<?php echo $zname; ?>学榜翻页"></a>
    <a class="google_event next_zxf <?php if(intval($model['current']) === intval($model['total_page']) || intval($model['total_page']) === 0) echo 'nextStop';?>" href="javascript:void(0)" ga-type="<?php echo $zname; ?>学榜翻页"></a>
</div>
