<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'purchased_detail_main',
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
                    'components' => array(
                        'purchased_detail_layout' => array(
                            'type' => 'layout',
                            'href' => '/curriculum/',
                            'category' => array(
                                1 => array(
                                    'name' => 'major',
                                    'title' => '主修课',
                                    'img' => img_url('purchased', 'number_icon_01.png'),
                                ),
                                2 => array(
                                    'name' => 'oral',
                                    'title' => '口语课',
                                    'img' => img_url('purchased', 'number_icon_02.png'),
                                ),
                                3 => array(
                                    'name' => 'elective',
                                    'title' => '选修课',
                                    'img' => img_url('purchased', 'number_icon_03.png'),
                                ),
                                4 => array(
                                    'name' => 'special',
                                    'title' => '技能课',
                                    'img' => img_url('purchased', 'number_icon_04.png'),
                                ),
                                5 => array(
                                    'name' => 'custom',
                                    'title' => '定制课',
                                    'img' => img_url('purchased', 'number_icon_05.png'),
                                )
                            ),
                        ),
                    )
                )
            )
        )
    )
);