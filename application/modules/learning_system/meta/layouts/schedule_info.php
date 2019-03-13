<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'right_panel',
    'components' => array(
        'top_switch' => array(
            'type' => 'view',
            'components' => array(
                'download' => array(
                    'title' => '基本信息',
                    'current' => true
                ),
            )
        ),
        'schedule_info' => array(
            'type' => 'view'
        )
    )
);