<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'lc_left',
    'div_class' => 'left_side_nav left_curricular',
    'new_guide_list' =>array(
        array(
            'class' => 'shade_text top95',
            'i_class' => 'jiantou',
            'image' => img_url('learning_center', 'shade_text_icon_01.png'),
            'name' => '学习主页',
            'content_text' => '选课后，在这里进入教室，上直播课',
            'step' => 4
        ),
        array(
            'class' => 'shade_text top175',
            'i_class' => 'jiantou',
            'image' => img_url('learning_center', 'shade_text_icon_02.png'),
            'name' => '我的课程',
            'content_text' => '在这里查看已选课程、回顾录播课件、做题、下载资料等',
            'step' => 3
        ),
        array(
            'class' => 'shade_text top240 select_class_top',
            'i_class' => 'jiantou',
            'image' => img_url('learning_center', 'shade_text_icon_03.png'),
            'name' => '我要选课',
            'content_text' => '在这里选新课',
            'step' => 2
        ),
        array(
            'class' => 'shade_text_top',
            'i_class' => 'jiantou_top',
            'image' => img_url('learning_center', 'shade_text_icon_04.png'),
            'name' => '用户中心',
            'content_text' => '可以在这里修改、完善个人信息、申请休学、请假等',
            'step' => 5
        ),

    ),
    'column_list' => array(
        array(
            'name' => '学习主页',
            'class' => 'learn_nav learnHomeClass link',
            'data-link' => 'learnHome',
            'img' => img_url('learning_center', 'nav_icon_01_v5.png'),
            'href' => '/study/#/learnHome',
            //'href' => 'javascript:;'
        ),
        array(
            'name' => '我的课程',
            'class' => 'learn_nav myCourseClass link',
            'data-link' => 'myCourse/major',
            'img' => img_url('learning_center', 'nav_icon_02_v5.png'),
            'href' => '/study/#/myCourse',
            //'href' => 'javascript:;'
        ),
        array(
            'name' => '我要选课',
            'class' => 'learn_nav selectCourseClass link',
            'data-link' => 'selectCourse/major',
            'img' => img_url('learning_center', 'nav_icon_03_v5.png'),
            'href' => '/study/#/chooseClass',
            //'href' => 'javascript:;'
        ),
        array(
            'name' => '我要提问',
            'class' => 'ask_question',
            'data-link' => 'ask_question',
            'img' => img_url('learning_center', 'nav_icon_04_v5.png'),
            'href' => KNOW_ZAODAO,
            'target' => '_blank'
        )
    )
);