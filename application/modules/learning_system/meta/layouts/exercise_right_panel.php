<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'right_panel',
    'components' => array(
        'top_switch' => array(
            'type' => 'view',
            'name' => 'exercise',
            'error_record_img' => img_url('learning_system', 'error_icon.png'),
            'components' => array(
                'test' => array(
                    'title' => '一课一练',
                    'current' => true
                ),
                'unit' => array(
                    'title' => '单元测试'
                )
            )
        ),
        'exe_panel_body' => array(
            'type' => 'layout',
        )
    )
);