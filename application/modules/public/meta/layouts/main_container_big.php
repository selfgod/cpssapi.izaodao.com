<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'header' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'header'.DIRECTORY_SEPARATOR.'top_header.php'
        ),
        'nav' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'header'.DIRECTORY_SEPARATOR.'top_nav_jp.php'
        ),
        'base' => array(
//            'class' => 'contant_center',
        ),
        'footer' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'footer'.DIRECTORY_SEPARATOR.'large_footer.php'
        ),
        'consulting' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'consulting'.DIRECTORY_SEPARATOR.'cpss_consulting.php'
        )
    )
);