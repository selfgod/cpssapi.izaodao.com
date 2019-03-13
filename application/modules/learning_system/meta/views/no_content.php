<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$recordTitle = '还未开课，没有录播回顾的课件哦~';
$unfinishedTitle = '目前还没有未完成的任务哦~';
$finishedTitle = '目前还没有已完成的任务哦~';

$nonAllMeta = array(
    'unfinished' => array(
        'title' => $unfinishedTitle,
        'wclass' => 'w315'
    ),
    'finished' => array(
        'title' => $finishedTitle,
        'wclass' => 'w315'
    )
);

return array(
    'image' => img_url('learning_system', 'ff_v422_01.png'),
    'category' => array(
        'record' => array_merge(array(
            'all' => array(
                'title' => $recordTitle
            )
        ), $nonAllMeta),
        'test' => array_merge(array(
            'all' => array(
                'title' => '老师没发布一课一练任务，自己要多练习哦~',
                'wclass' => 'w425',
                'button' => array(
                    'title' => '进入题库',
                    'href' => MASTER_DOMAIN . 'main.php/tiku/practice'
                )
            )
        ), $nonAllMeta),
        'unit' => array_merge(array(
            'all' => array(
                'title' => '老师没发布单元测试任务，自己要多练习哦~',
                'wclass' => 'w425',
                'button' => array(
                    'title' => '进入题库',
                    'href' => MASTER_DOMAIN . 'main.php/tiku/practice'
                )
            )
        ), $nonAllMeta),
        'datum' => array(
            'all' => array(
                'title' => '还没有资料哦~',
                'wclass' => 'w220'
            )
        )
    )
);