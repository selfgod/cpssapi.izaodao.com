<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zaodao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
    }

    /**
     * 获取网站基础信息
     */
    public function info()
    {
        $isjsonp = $this->input->get('callback');
        $this->load->model('api/zaodao_model');
        $info = $this->zaodao_model->info();
        if (!empty($isjsonp)) {
            $this->response->formatJsonp(200, $info);
        } else {
            $this->response->formatJson(200, $info);
        }
    }

    /**
     * 校园公告接口
     */
    public function notice()
    {
        $isjsonp = $this->input->get('callback');
        $this->load->model('api/zaodao_model');
        $info = $this->zaodao_model->notice(10);
        if (!empty($isjsonp)) {
            $this->response->formatJsonp(200, $info);
        } else {
            $this->response->formatJson(200, $info);
        }
    }

    public function ios()
    {
        echo $this->layout->loadView('zdtalk_ios_test', 'learning_system');
    }
}