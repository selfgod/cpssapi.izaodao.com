<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'title' => '升级商品',
    'components' => array(
        'main_container_big' => array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'class' => 'container pt50 pb50',
                    'components' => array(
                        'select_goods' => array(
                            'type' => 'view'
                        ),
                        'description' => array(
                            'type' => 'view'
                        ),
                    )
                )
            )
        ),
    )
);
