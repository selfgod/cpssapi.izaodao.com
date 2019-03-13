<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'js' => array(
        base_url('/static/lib/jquery.rotate.min.js')
    ),
    'components' => array(
        'main_container_nohonor'=>array(
            'type' => 'layout',
            'components' => array(
                'base' => array(
                    'type' => 'layout',
                    'components' => array(
                        'level_index_body' => array(
                            'type' => 'view'
                        )
                    )
                )
            )
        )
    )
);
