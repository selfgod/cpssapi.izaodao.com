<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'my_curricular_type' => array(
            'type' => 'view',
        ),
        'base' => array(
            'type' => 'layout',
            'class' => 'my_major_list',
            'components' => array(
                'my_major_list' => array(
                    'type' => 'view'
                )
            )
        )
    )
);