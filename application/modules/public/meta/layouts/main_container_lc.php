<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'header' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR . 'header' . DIRECTORY_SEPARATOR . 'learning_center_header.php'
        ),
        'lc_left' => array(
            'type' => 'view'
        ),
        'base' => array(
            'class' => 'content_v5 mt120 clearfix cpss_lc_main ga',
        ),
        'consulting' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR . 'consulting' . DIRECTORY_SEPARATOR . 'cpss_consulting.php'
        ),
        'footer' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR . 'footer' . DIRECTORY_SEPARATOR . 'learning_center_footer.php'
        )
    )
);