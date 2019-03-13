<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'template' => 'right_panel',
    'components' => array(
        'top_switch' => array(
            'type' => 'view',
            'components' => array(
                'download' => array(
                    'title' => '资料下载',
                    'current' => true
                ),
            )
        ),
        'datum_panel_body' => array(
            'type' => 'layout',
            'template' => 'base',
            'components' => array(
                'sub_category' => array(
                    'type' => 'view',
                    'class' => 'dataDownload_v422',
                    'components' => array(
                        array(
                            'name' => '0',
                            'title' => '全部',
                            'current' => true
                        ),
                        array(
                            'name' => '1',
                            'title' => '电子教材'
                        ),
                        array(
                            'name' => '2',
                            'title' => '音频下载'
                        ),
                        array(
                            'name' => '3',
                            'title' => '习题资料'
                        )
                    )
                ),
                'detail_content' => array(
                    'type' => 'layout',
                    'template' => 'base',
                    'id' => 'detail_content',
                    'components' => array(
//                        'datum_list' => array(
//                            'type' => 'layout'
//                        )
                    )
                ),
            )
        )
    )
);
