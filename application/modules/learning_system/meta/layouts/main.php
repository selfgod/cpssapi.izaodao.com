<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'css' => array(
        css_url('learning_system', 'style.min.css'),
        css_url('public', 'modules.min.css'),
    ),
    'components' => array(
        'main_container' => array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'components' => array(
                        'switch_schedule' => array(
                            'type' => 'view'
                        ),
                        'main_body' => array(
                            'type' => 'layout',
                            'template' => 'base',
                            'id' => 'schedule_body',
                            'class' => 'warrp_v422 clearfix',
                            'components' => array(
                                'schedule_body' => array(
                                    'type' => 'layout'
                                )
                            )
                        ),
                    )
                )
            )
        ),
    )
);
