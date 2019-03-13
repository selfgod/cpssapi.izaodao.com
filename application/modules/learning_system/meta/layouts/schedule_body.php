<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'schedule_body',
    'components' => array(
        'left_menu' => array(
            'type' => 'view',
        ),
        'base_right_panel' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'ls_right',
            'class' => 'right_panel'
        ),
    )
);