<?php
defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    'template' => 'no_content',
    'class' => 'cpss_search_none',
    'image' => img_url('learning_center', 'cpss_time.png'),
    'content' => '<h3>你所报名的课程商品已过期，无法学习啦…</h3><h4>可以去报名新的课程继续学习</h4>',
    'button_name' => '去报名新课程',
    'button_href' => MASTER_DOMAIN,
    'button_target' => '_blank'
);