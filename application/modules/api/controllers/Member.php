<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller
{
    /**
     * 用户中心注册用户回调
     */
    public function register()
    {
        $inputStream = $this->input->post('user');
        $this->load->model('public/passport_model');
        $ret = $this->passport_model->register($inputStream);
        $this->response->formatJson($ret['code'], [], $ret['msg']);
    }

    /**
     * 修改用户信息
     */
    public function update()
    {
        $inputStream = $this->input->post('user');
        $this->load->model('public/passport_model');
        $ret = $this->passport_model->update($inputStream);
        $this->response->formatJson($ret['code'], [], $ret['msg']);
    }

}
