<div class="classInfo_v1027">
    <ul class="pb30">
        <li class="clearfix">
            <b class="flL">授课目标：</b>
            <p class="flL mt48 lh23 w800"><?php echo $model['target'];?></p>
        </li>
        <li class="clearfix">
            <b class="flL">使用教材：</b>
            <?php if(empty($model['relate_books'])):?><p class="flL mt48 lh23">无</p><?php else:?>
            <?php foreach ($model['relate_books'] as $index => $book):?>
                <div class="flL curP textbook_main<?php if($index !== 0) echo ' ml15';?>">
                    <a href="<?php echo $book['buy_link'];?>" target="_blank">
                    <span class="flL mt23"><img src="<?php echo $book['cover_img'];?>" alt=""></span>
                    <p class="flL mt45 ml10"><?php echo $book['name'];?></p></a>
                </div>
            <?php endforeach;?><?php endif;?>
        </li>
        <li class="clearfix">
            <b class="flL">主讲教师：</b>
            <?php foreach ($model['relate_teachers'] as $index => $teacher):?>
                <div class="flL pic_avatar<?php if($index !== 0) echo ' ml50';?>">
                    <a href="<?php echo $teacher['detail_link'];?>" target="_blank"><img src="<?php echo $teacher['avatar'];?>" alt="">
                    <p class="mt05"><?php echo $teacher['name'];?></p></a>
                </div>
            <?php endforeach;?>
        </li>
        <?php if($model['class_mode'] == '1'):?>
        <li class="clearfix">
            <b class="flL">开课日期：</b>
            <p class="flL lh120"><?php echo $model['start_time'];?> — <?php echo $model['end_time'];?></p>
        </li>
        <li class="clearfix">
            <b class="flL">上课时间：</b>
            <p class="flL lh120">周<?php echo implode('、', $model['class_week_time']);?> <?php echo $model['class_start_time'];?> — <?php echo $model['class_end_time'];?></p>
        </li>
        <?php endif;?>
        <li class="clearfix <?php if($model['class_mode'] == '2') echo 'borderBottom00';?>">
            <b class="flL">课次总数：</b>
            <p class="flL lh120"><?php echo $model['class_num'];?>次课</p>
        </li>
        <?php if($model['class_mode'] == '1'):?>
        <li class="borderBottom00 clearfix">
            <b class="flL">QQ群号：</b>
            <?php if (!empty($model['qq_group'])): ?>
                <p class="flL lh120 color_4bb866"><?php echo $model['qq_group']; ?></p>
            <?php else: ?>
                <p class="flL lh120">无</p>
            <?php endif; ?>
        </li>
        <?php endif;?>
    </ul>
</div>