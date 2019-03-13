<?php
defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    'template' => 'main',
    'title' => '教务中心-休学',
    'components' => array(
        'main_container_jp' => array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'components' => array(
                        'holiday_banner' => array(
                            'type' => 'view'
                        ),
                        'base' => array(
                            'type' => 'layout',
                            'class' => 'main',
                            'components' => array(
                                'holiday_tab' => array(
                                    'type' => 'view',
                                    'leave_class' => 'title_tab',
                                    'suspend_class' => 'title_tab current'
                                ),
                                'suspend_main' => array(
                                    'type' => 'view'
                                )
                            )
                        )
                    )
                )
            )

        )
    ,)
);