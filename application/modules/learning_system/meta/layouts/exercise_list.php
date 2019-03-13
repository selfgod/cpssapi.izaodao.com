<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'exercise_list' => array(
            'type' => 'view',
            'title' => array(
                'test' => '一课一练',
                'unit' => '单元测试'
            ),
        ),
        'pagination' => array(
            'module' => 'public',
            'class' => 'page_v422 clearfix',
            'currentClass' => 'page_v422_current',
            'type' => 'view',
            'max' => 5
        )
    )

);