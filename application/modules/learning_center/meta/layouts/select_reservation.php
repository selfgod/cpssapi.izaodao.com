<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'base' => array(
            'class' => 'cpss_container',
            'type' => 'layout',
            'components' => array(
                'reservation_used' => array(
                    'type' => 'view'
                ),
                'base' => array(
                    'class' => 'reservation_content',
                    'type' => 'layout',
                    'components' => array(
                        'reservation_label' => array(
                            'type' => 'view'
                        ),
                        'base' => array(
                            'class' => 'reservation_main',
                            'type' => 'layout',
                            'components' => array(
                                'reservation_mains' => array(
                                    'type' => 'layout'
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);