<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/member_model');
    }

    /**
     * 用户基本信息
     */
    public function baseInfo()
    {
        $this->load->library('layout');
        $isjsonp = $this->input->get('callback');
        $uid = $this->_uid;
        $info = $this->member_model->getBaseInfo($uid);
        $data = array(
            'user_name' => $this->_login['username'],
            'currency' => $info['currency'],
            'is_teacher' => $info['isTeacher'],
            'unpaid' => intval($info['unPaidNum']),
            'score' => $info['score']['available'],
            'avatar' => $info['avatar'],
            'sms' => intval($info['sms']),
            'score_limit' => $info['score_limit'],
            'coupon' => $info['score_limit']
        );
        if (!empty($isjsonp)) {
            $this->response->formatJsonp(200, $data);
        } else {
            $this->response->formatJson(200, $data);
        }
    }

    /**
     * 获取uid
     * @return null
     */
    public function uid()
    {
        $isjsonp = $this->input->get('callback');
        if (empty($isjsonp)) {
            $this->response->formatJson(200);
        } else {
            $this->response->formatJsonp(200);
        }
    }


    /**
     * 退出用户中心
     */
    public function logout()
    {
        $isjsonp = $this->input->get('callback');
        $ret = $this->passport->logout();
        if ($ret) {
            if (empty($isjsonp)) {
                $this->response->formatJson(200, [], '退出成功');
            } else {
                $this->response->formatJsonp(200, [], '退出成功');
            }
        } else {
            if (empty($isjsonp)) {
                $this->response->formatJson(500, [], '退出失败');
            } else {
                $this->response->formatJsonp(500, [], '退出失败');
            }
        }
    }

    /**
     * 用户学习信息
     */
    public function learnInfo()
    {
        $uid = $this->_uid;
        $this->load->model('public/member_model');
        $data = $this->member_model->learnUserInfo($uid);
        $this->response->formatJson(200, $data);
    }
}
