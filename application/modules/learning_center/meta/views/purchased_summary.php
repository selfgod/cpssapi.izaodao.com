<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'category' => array(
        array(
            'title' => '主修课',
            'img' => img_url('learning_center', 'cpss_sortIcon_01.png'),
            'name' => 'major',
            'type' => get_options('curricular_system', 'major')
        ),
        array(
            'title' => '口语课',
            'img' => img_url('learning_center', 'cpss_sortIcon_02.png'),
            'name' => 'oral',
            'type' => get_options('curricular_system', 'oral')
        ),
        array(
            'title' => '选修课',
            'img' => img_url('learning_center', 'cpss_sortIcon_03.png'),
            'name' => 'elective',
            'type' => get_options('curricular_system', 'elective')
        ),
        array(
            'title' => '技能课',
            'img' => img_url('learning_center', 'cpss_sortIcon_04.png'),
            'name' => 'special',
            'type' => get_options('curricular_system', 'special')
        ),
        array(
            'title' => '定制课',
            'img' => img_url('learning_center', 'cpss_sortIcon_05.png'),
            'name' => 'custom',
            'type' => get_options('curricular_system', 'custom')
        )
    )
);