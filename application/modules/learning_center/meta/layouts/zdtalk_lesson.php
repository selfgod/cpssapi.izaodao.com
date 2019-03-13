<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'zdtalk_main',
    'title' => '启动早道talk',
    'components' => array(
        'header' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR . 'header' . DIRECTORY_SEPARATOR . 'learning_center_header.php'
        ),
        'zdtalk_detail' => array(
            'type' => 'view',
        )
    )
);