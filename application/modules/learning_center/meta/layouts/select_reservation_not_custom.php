<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'none',
    'components' => array(
        'reservation_used' => array(
            'type' => 'view'
        ),
        'not_custom' => array(
            'template' => 'no_content',
            'type' => 'view',
            'class' => 'cpss_customize_text',
            'image' => img_url('learning_center', 'ff_02.png'),
            'content' => '<p class="fz20 color_555">SVIP专属定制课，哪里不会补哪里！</p>
                            <p class="fz12 color_999 mt20">SVIP学员可在社群中提出自己的学习难题，发起上课需求，达到一定人数响应后，可考虑开课。</p>
                            <p class="fz14 color_ff620b mt20">SVIP社群号请联系班主任获取，<a class="qq_text" href="http://wpa.qq.com/msgrd?v=3&uin=387719168&site=qq&menu=yes" target="_blank" ga-type="点击定制课_班主任QQ">QQ号：387719168</a></p>'
        )
    )
);