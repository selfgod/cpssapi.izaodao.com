<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'class' => 'cpss_info_mian',
    'components' => array(
        'base' => array(
            'type' => 'layout',
            'class' => 'teb_title_v5 switch_plan',
            'components' => array(
                'switch_plan' => array(
                    'type' => 'view'
                )
            )
        ),
        'select_plan_stage' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'switch_plan_stage',
            'components' => array(
                'switch_plan_stage' => array(
                    'type' => 'view'
                )
            )
        )
    )
);