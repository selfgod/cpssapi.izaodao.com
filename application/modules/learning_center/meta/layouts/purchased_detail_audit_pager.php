<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'detail_count_analyze' => array(
            'type' => 'view'
        ),
        'pagination' => array(
            'module' => 'public',
            'class' => 'cpss_page_list',
            'currentClass' => 'cpss_page_current',
            'type' => 'view',
            'max' => 5
        )
    )
);