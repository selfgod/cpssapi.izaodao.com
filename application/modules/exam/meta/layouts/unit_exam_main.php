<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(
    'js' => array(
        base_url('/static/lib/jwplayer/jwplayer.js')
    ),
    'components' => array(
        'unit_header' => array(
            'type' => 'view'
        ),
        'unit_exam_body' => array(
            'type' => 'view'
        )
    )
);
