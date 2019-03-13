<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'category' => array(
        'week' => array(
            'current' => true,
            'title' => '周学分榜'
        ),
        'total' => array(
            'title' => '总学分榜'
        )
    ),
    'components' => array(
        'rank_panel_body' => array(
            'type' => 'layout',
        )
    )
);