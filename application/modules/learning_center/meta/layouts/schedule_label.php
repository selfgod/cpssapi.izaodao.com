<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'schedule_label',
    'components' => array(
        'schedule_branch' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'select_course_schedule',
            'components' => array(
                'schedule_list' => array(
                    'type' => 'view'
                )
            )
        )
    )
);