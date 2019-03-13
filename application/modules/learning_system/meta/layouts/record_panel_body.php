<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'base',
    'components' => array(
        'sub_category' => array(
            'type' => 'view',
            'class' => 'subNav_v422',
            'components' => array(
                array(
                    'name' => 'all',
                    'title' => '全部',
                    'current' => true
                ),
                array(
                    'name' => 'unfinished',
                    'title' => '未完成'
                ),
                array(
                    'name' => 'finished',
                    'title' => '已完成'
                )
            )
        ),
        'detail_content' => array(
            'type' => 'layout',
            'template' => 'base',
            'id' => 'detail_content',
        ),
    )
);
