<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'purchased_main',
    'title' => '已购商品_早道日语网校_多语种在线学习平台',
    'css' => array(
        css_url('learning_center', 'menu.min.css'),
        css_url('learning_center', 'layout.min.css'),
    ),
    'components' => array(
        'main_container_jp' => array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'id' => 'container',
                )
            )
        ),
    )
);