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
            'class' => 'my_reservation_lesson_list class_list_v5',
            'components' => array(
                'my_reservation_lesson' => array(
                    'type' => 'view'
                )
            )
        )
    )
);