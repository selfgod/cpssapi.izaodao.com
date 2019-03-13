<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Privilege extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
        $this->load->model('privilege_model');
    }

    public function index()
    {
        $data = $this->privilege_model->getTopMenuInfo($this->_uid);
        $data['is_self'] = TRUE;
        $result = $this->layout->load('privilege', 'learning_center', $data);
        $this->response->html($result);
    }

}