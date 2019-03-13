<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['socket_type'] = 'tcp'; //`tcp` or `unix`
$config['host'] = REDIS_HOST;
$config['port'] = REDIS_PORT;
$config['timeout'] = 5;