<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'week_day_panel' => array(
            'type' => 'layout',
            'components' => array(
                'day_switch' => array(
                    'type' => 'view',
                    'left_pic' => img_url('learning_system', 'mission_v422_left.png'),
                    'right_pic' => img_url('learning_system', 'mission_v422_right.png')
                ),
                'calendar' => array(
                    'type' => 'layout',
                )
            )
        ),
        'detail_content' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'detail_content',
            'class' => 'container_v422'
        )
    )
);