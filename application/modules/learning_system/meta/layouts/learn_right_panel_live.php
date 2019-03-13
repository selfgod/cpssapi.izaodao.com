<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'right_panel',
    'name' => 'learn_right_panel',
    'components' => array(
        'top_switch' => array(
            'type' => 'view',
            'name' => 'learn',
            'components' => array(
                'record' => array(
                    'title' => '录播回顾',
                    'current' => true
                ),
                'live' => array(
                    'title' => '直播课表',
                )
            )
        ),
        'learn_panel_body' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'lesson_panel',
            'components' => array(
//                'live_panel_body' => array(
//                    'type' => 'layout'
//                )
            )
        )
    )
);