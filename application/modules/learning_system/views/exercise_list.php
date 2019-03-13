<div class="container_v422_list mt20 w948">
    <?php foreach ($model['data'] as $index => $item):?>
        <ul<?php if($index !== 0) echo ' class="borderTop00"'?> data-starttime="<?php if(!$model['is_record']) echo $item['start_time'];?>">
            <li class="w135 <?php if($item['status'] === '1') echo 'mt05'; else echo 'mt10';?>">
                <div class="task_v422 <?php if($item['status'] !== '1') echo 'mt12';?>">
                    <?php if($item['status'] === '1'):?>
                        <?php if(isset($item['current_finish']) && $item['score']>0):?><p class="color_4bb866 flL ml10">+<?php echo $item['score'];?></p>
                        <i class="task_right_v422 ml05"><?php else:?><i class="task_right_v422 ml30"><?php endif;?>
                            <img src="<?php echo img_url('learning_system', 'task_right_v422.png');?>" alt=""/>
                        </i>
                    <?php else:?>
                        <p class="color_ccc ml10">+<?php echo $item['score'];?>
                    <?php endif;?>
                </div><?php if($item['status'] === '1'):?>
                <p class="doing_green_v422 clearfix"><?php echo $item['finish_date'];?><br/>完成</p>
                <?php endif;?>
            </li>
            <li class="w110 mt10 mr30">
                <p class="lessonTitle_v422 color_666 <?php if(!$model['is_record']) echo 'mt04'; else echo 'mt20';?> mb05 taC"><?php echo $meta['title'][$model['type']]?></p>
                <?php if(!$model['is_record']):?>
                <div class="teacher_v422 taC lh20"><?php echo date('Y/m/d', $item['start_time']);?></div>
                <?php endif;?>
            </li>
            <li class="w530 mt10">
                <p class="lessonTitle_v422 color_666 mt04 mb05"><?php echo $item['name'];?></p>
                <div class="teacher_v422">共<?php echo $item['item_count'];?>题<b class="ml20"><?php if($model['type'] === 'test') echo '预计';echo $item['total_time'];?>分钟</b></div>
            </li>
            <li class="w100">
                <?php if($model['type'] === 'test'):?>
                    <?php if(isset($item['last_time'])):?>
                        <a href="javascript:void(0);" data-url="<?php echo $item['test_retest_url'];?>" class="button_v422_01 button_green exec" ga-type="点击一课一练" ga-title="重做">重 做</a>
                        <a target="_blank" href="<?php echo $item['test_begin_url'];?>" class="button_v422_01 button_lineGreen mt08" ga-type="点击一课一练" ga-title="回顾">回 顾</a>
                    <?php else: ?>
                        <a href="javascript:void(0);" data-url="<?php echo $item['test_begin_url'];?>" class="button_v422_01 button_green mt20 exec" ga-type="点击一课一练" ga-title="开始练习">开始练习</a>
                    <?php endif;?>
                <?php else:?>
                    <?php if(isset($item['unit_result_id'])):?>
                        <a href="javascript:void(0);" data-url="<?php echo $item['unit_retest_url'];?>" class="button_v422_01 button_green retest exec" ga-type="点击单元测试" ga-title="重考">重 考</a>
                        <a target="_blank" href="<?php echo $item['unit_review_url'];?>" class="button_v422_01 button_lineGreen mt08" ga-type="点击单元测试" ga-title="回顾">回 顾</a>
                    <?php else:?>
                        <?php
                        $but = '补考';
                        if(!$item['ontime_unit'] || ($model['current'] < $item['start_time'] + 3600)) $but = '开始测试';
                        ?>
                        <a data-url="<?php echo $item['unit_begin_url'];?>" href="javascript:void(0);" class="button_v422_01 button_green mt20 exec <?php if(isset($item['ontime_unit_notstart'])) echo 'not_start'; elseif(isset($item['over_30min'])) echo ' over_30min';?>" ga-type="点击单元测试" ga-title="<?php echo $but;?>"><?php echo $but;?></a>
                    <?php endif;?>
                <?php endif;?>
            </li>
        </ul>
    <?php endforeach;?>
</div>