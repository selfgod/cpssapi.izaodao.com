<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'reservation_mains',
    'components' => array(
        'reservation_list' => array(
            'type' => 'view',
        ),
        'pagination' => array(
            'module' => 'public',
            'class' => 'page_list_v5',
            'currentClass' => 'page_v422_current',
            'type' => 'view',
            'max' => 5
        )
    )
);