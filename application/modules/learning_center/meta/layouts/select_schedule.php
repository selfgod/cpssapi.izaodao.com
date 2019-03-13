<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'base' => array(
            'class' => 'cpss_container',
            'type' => 'layout',
            'components' => array(
                'switch_class_mode' => array(
                    'type' => 'view'
                ),
                'base' => array(
                    'class' => 'schedule_lable',
                    'type' => 'layout',
                    'components' => array(
                        'schedule_label' => array(
                            'type' => 'layout'
                        )
                    )
                )
            )
        )
    )
);