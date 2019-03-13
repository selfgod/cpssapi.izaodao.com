<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'components' => array(
        'main_wrap' => array(
            'type' => 'layout',
            'template' => 'base',
            'class' => 'unit_enter_content',
            'components' => array(
                'unit_header' => array(
                    'type' => 'view'
                ),
                'unit_prepare_body' => array(
                    'type' => 'view'
                ),
            )
        ),
        'footer' => array(
            'type' => 'view',
            'file' => PUBLIC_STATIC_DIR.'footer'.DIRECTORY_SEPARATOR.'small_footer.php'
        )
    )
);
