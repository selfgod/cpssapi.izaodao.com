<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'week_day' => array(
            'type' => 'layout',
            'template' => 'none',
            'components' => array(
                'day_switch' => array(
                    'type' => 'layout',
                    'components' => array(
                        'calendar' => array(
                            'type' => 'view',
                            'module' => 'learning_system',
                        )
                    )
                )
            )
        ),
        'week_schedule' => array(
            'type' => 'layout',
            'template' => 'base',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'class' => 'calendar_list_v5',
                    'id' => 'live_schedule_detial',
                    'components' => array(
                        'day_schedule' => array(
                            'type' => 'view'
                        )
                    )
                )
            )
        )
    )
);