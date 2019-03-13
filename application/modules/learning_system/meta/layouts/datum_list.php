<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'datum_list' => array(
            'type' => 'view',
            'help_link' => MASTER_DOMAIN . 'misc.php?mod=faq#id=28&messageid=93',
            'icons' => array(
                '1' => array(
                    'title' => '电子教材',
                    'class' => 'book_v422'
                ),
                '2' => array(
                    'title' => '音频下载',
                    'class' => 'audio_v422'
                ),
                '3' => array(
                    'title' => '习题资料',
                    'class' => 'problem_v422'
                )
            )
        ),
        'pagination' => array(
            'module' => 'public',
            'class' => 'page_v422 clearfix',
            'currentClass' => 'page_v422_current',
            'type' => 'view',
            'max' => 5
        )
    )

);