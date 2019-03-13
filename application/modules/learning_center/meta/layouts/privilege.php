<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'privilege_main',
    'title' => '我的特权_早道日语网校_多语种在线学习平台',
    'css' => array(
        css_url('learning_center', 'menu.min.css'),
        css_url('learning_center', 'layout.min.css'),
        css_url('public', 'modules.min.css'),
    ),
    'components' => array(
        'main_container_jp' => array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'components' => array(
                        'top_menu' => array(
                            'type' => 'view'
                        ),
                        'privilege_detial' => array(
                            'type' => 'view'
                        )
                    )
                )
            )
        ),
    )
);