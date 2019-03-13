<?php
defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    'template' => 'no_content',
    'class' => 'cpss_search_none',
    'image' => img_url('learning_center', 'cpss_none.png'),
    'content' => '<h3>你还没有报名任何课程哦</h3>',
    'button_name' => '去报名',
    'button_href' => get_options('vip_link'),
    'button_target' => '_blank',
    'button_class' => 'google_event',
    'ga_type' => '点击去报名',
);