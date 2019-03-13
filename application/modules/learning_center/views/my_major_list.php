    <?php if (!empty($model['schedule_list'])):?>
     <div class="myclass_major_list_v5">
        <ul class="clearfix">
        <?php foreach ($model['schedule_list'] as $id => $item):?>
            <li class="myclass_major_course">
            <div class="myclass_container">
                <div class="myclass_major_plan">
                    <div class="complete_circle google_event" data-complete="<?php echo $item['reportCount'];?>" data-total="<?php echo $item['lessonCount'];?>" ga-type="点击<?php echo $model['curricular_zh'] . '_进度图标'; ?>" title="我的进度"></div>
                </div>
                <?php if(isset($item['able_del_schedule'])):?>
                <span class="myclass_major_delete google_event" data-id="<?php echo $id;?>" ga-type="点击<?php echo $model['curricular_zh'] . '_删除图标'; ?>" title="删除课程"></span>
                <?php endif;?>
                <div class="myclass_major_course_title">
                    <?php if($item['button_type'] === 'learn'):?>
                        <h4 class="color_333 google_event hover_blue curP link" data-link="myCourse?id=<?php echo $id;?>" ga-type="点击<?php echo $model['curricular_zh'] . '_课程标题'; ?>" ga-title="<?php echo $item['name'];?>" title="<?php echo $item['name'];?>"><?php echo $item['name'];?></h4>
                    <?php else:?>
                        <h4 class="color_333 google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_课程标题'; ?>" ga-title="<?php echo $item['name'];?>" title="<?php echo $item['name'];?>"><?php echo $item['name'];?></h4>
                    <?php endif;?>
                    <?php if (intval($item['class_mode']) === 1): ?>
                        <p class="color_aaa mt10">上课时间：<?php echo "{$item['class_time']} {$item['week_val']} {$item['class_date']}"; ?></p>
                    <?php else:?>
                        <p class="color_aaa mt10">录播课程，请观看录播课件进行学习</p>
                    <?php endif;?>
                    <p class="color_aaa mt05">主讲老师：<?php echo $item['teacher_val'];?></p>
                </div>
                <?php if (!empty($item['qq_group'])): ?>
                    <?php if (intval($item['is_finish']) === 1): ?>
                        <p class="myclass_major_course_qq google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_qq群号';?>" ga-title="<?php echo $item['qq_group'];?>">班级Q群<br><?php echo $item['qq_group'];?></p>
                    <?php else: ?>
                        <p class="myclass_major_course_qq">班级Q群<br>已解散</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="myclass_major_course_qq">班级Q群<br>---</p>
                <?php endif; ?>
                <div class="myclass_major_course_action">
                    <?php if($item['button_type'] === 'learn'):?>
                        <a href="javascript:;" class="taR color_3399ff link google_event" data-link="myCourse?id=<?php echo $id;?>&cate=exercise" ga-type="点击<?php echo $model['curricular_zh'] . '_做题';?>">
                            <i class="exercise_ov"></i><b>做题</b>
                        </a>
                        <a href="javascript:;" class="taR color_3399ff link google_event" data-link="myCourse?id=<?php echo $id;?>&cate=datum" ga-type="点击<?php echo $model['curricular_zh'] . '_资料';?>">
                            <i class="material_ov"></i><b>资料</b>
                        </a>
                        <a href="javascript:;" class="myclass_major_course_btn mr00 google_event link" data-link="myCourse?id=<?php echo $id;?>" ga-type="点击<?php echo $model['curricular_zh'] . '_看录播课';?>" ga-title="<?php echo $item['name'];?>">看录播课</a>
                    <?php elseif($item['button_type'] === 'activate'):?>
                        <a href="/purchased" class="myclass_major_course_btn mr00 google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_去激活';?>">去激活</a>
                    <?php else:?>
                        <a class="myclass_major_course_disable_btn mr00 google_event" ga-type="点击<?php echo $model['curricular_zh'] . '_已过期';?>">已过期</a>
                    <?php endif;?>
                </div>
                </div>
            </li>
        <?php endforeach;?>
        </ul>
     </div>
    <?else: ?>
        <?php if (isset($model['noCourse'])) echo $model['noCourse']; ?>
    <?php endif; ?>
