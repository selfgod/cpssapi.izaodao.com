<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'header' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'header'.DIRECTORY_SEPARATOR.'header.php'
        ),
        'base' => array(
            'class' => 'contant_center',
        ),
        'footer' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'footer'.DIRECTORY_SEPARATOR.'small_footer.php'
        ),
        'consulting' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'consulting'.DIRECTORY_SEPARATOR.'cpss_consulting.php'
        )
    )
);