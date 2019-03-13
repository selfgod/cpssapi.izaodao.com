<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'class' => 'report_right_panel',
    'components' => array(
        'report_panel' => array(
            'type' => 'layout',
            'template' => 'base',
            'class' => 'centen_v422',
            'components' => array(
                'report_top_nav' => array(
                    'type' => 'view'
                ),
                'total_score' => array(
                    'type' => 'view',
                    'icon' => img_url('learning_system', 'info_icon_v422.png')
                ),
                'attendance_report' => array(
                    'type' => 'view'
                )
            )
        ),
        'detail_content' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'detail_content',
        ),
    )
);
