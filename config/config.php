<?php
defined('BASEPATH') OR exit('No direct script access allowed');

switch (ENVIRONMENT)
{
    case 'development':
        include_once FCPATH . 'config/development_config.php';
        break;
    case 'testing':
        include_once FCPATH . 'config/testing_config.php';
        break;
    case 'production':
        include_once FCPATH . 'config/production_config.php';
        break;
}


