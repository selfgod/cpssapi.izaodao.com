<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'base' => array(
            'class' => 'cpss_container',
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'class' => 'evaluation_top_v5 clearfix oral_assessment_current',
                    'components' => array(
                        'oral_assessment_current' => array(
                            'type' => 'view'
                        )
                    )
                ),
                'oral_assessment_main' => array(
                    'type' => 'view'
                )
            )
        )
    )
);