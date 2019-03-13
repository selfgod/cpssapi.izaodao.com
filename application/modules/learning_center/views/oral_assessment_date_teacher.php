<div class="myclassv222_day_list_title">
    <p class="myclassv222_day_titleL flL" id="last_date"
       data-date="<?php echo date('Y-m-d', strtotime($model['date'] . ' -1 days')); ?>">< 前一天</p>
    <p class="myclassv222_day_titleC flL"><?php echo date('Y年m月d日', strtotime($model['date'])); ?></p>
    <p class="myclassv222_day_titleR flL" id="next_date"
       data-date="<?php echo date('Y-m-d', strtotime($model['date'] . ' +1 days')); ?>">后一天 ></p>
</div>
<div class="myclassv222_day_week">
    <table border="1" cellpadding="1" cellspacing="0" class="table_list">
        <thead>
        <tr>
            <td></td>
            <?php if (!empty($model['teacher'])):
                $i = 0;
                foreach ($model['teacher'] as $tv):
                    $i++;
                    ?>
                    <td<?php if ($i == count($model['teacher'])): ?> class="borderRight00"<?php endif; ?>><?php echo $tv['teacher_name']; ?></td>
                <?php endforeach;endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($model['assessment_date'])):
            foreach ($model['assessment_date'] as $val):
                $val['time_name_arr'] = explode('-', $val['value']);
                if (empty($val['time_name_arr'])) continue; ?>
                <tr>
                    <td class="fz16 color_888">
                        <span class="bg_fff block"><?php echo $val['time_name_arr'][0]; ?>
                            - <?php echo $val['time_name_arr'][1]; ?></span>
                    </td>
                    <?php if (!empty($model['teacher'])):
                        $i = 0;
                        foreach ($model['teacher'] as $tv):
                            $i++;
                            ?>
                            <td<?php if ($i == count($model['teacher'])): ?> class="borderRight00"<?php endif; ?>>
                                <?php if (isset($model['apply_data'][$val['value'] . '_' . $tv['teacher_uid']]) && !empty($model['apply_data'][$val['value'] . '_' . $tv['teacher_uid']])):
                                    $apply_val = $model['apply_data'][$val['value'] . '_' . $tv['teacher_uid']];
                                    ?>
                                    <?php if ($apply_val['apply']): ?>
                                    <span class="hover_bg">已预约<i><img
                                                    src="<?php echo img_url('learning_center', 'myclassv222_arrow.png'); ?>"
                                                    alt=""/></i></span>
                                <?php elseif ($apply_val['expired']): ?>
                                    <span class="hover_bg_on">已过期<i><img
                                                    src="<?php echo img_url('learning_center', 'myclassv222_arrow.png'); ?>"
                                                    alt=""/></i></span>
                                <?php else: ?>
                                    <span class="hover_bg apply_data_teacher"
                                          data-teacher="<?php echo $tv['teacher_uid']; ?>"
                                          data-time="<?php echo $apply_val['hope_time']; ?>">可预约<i><img
                                                    src="<?php echo img_url('learning_center', 'myclassv222_arrow.png'); ?>"
                                                    alt=""/></i></span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach;endif; ?>
                </tr>
            <?php endforeach;endif; ?>
        </tbody>
    </table>
</div>