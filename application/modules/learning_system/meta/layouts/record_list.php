<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'record_list' => array(
            'type' => 'view'
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