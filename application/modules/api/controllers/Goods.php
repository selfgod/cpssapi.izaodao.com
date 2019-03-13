<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends CI_Controller
{
    /**
     * 激活商品
     */
    public function activate()
    {
        $uid = intval($this->input->post('uid'));
        $goodsId = intval($this->input->post('goods_id'));

        if (empty($uid) || empty($goodsId)) {
            $this->response->formatJson(500, [], '参数错误');
        }

        $this->load->model('public/goods_model');
        $result = $this->goods_model->activateGoods($uid, $goodsId);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }

    /**
     * zdmis获取用户已购商品
     */
    public function purchaseList()
    {
        $uid = intval($this->input->get('uid'));
        $this->load->model('upgrade/upgrade_model');
        if (!empty($uid)) {
            $data = $this->upgrade_model->purchasedGoods($uid);
            $this->response->formatJsonp(200, $data);
        } else {
            $this->response->formatJsonp(400, [], '参数错误');
        }
    }

    /**
     * zdmis获取用户升级目标商品信息
     */
    public function targetList()
    {
        $uid = intval($this->input->get('uid'));
        $goodsId = intval($this->input->get('goods'));
        $this->load->model('upgrade/upgrade_model');
        if (!empty($uid)) {
            $data = $this->upgrade_model->upgradeGoodsList($uid, $goodsId);
            if (isset($data['code']) && $data['code'] !== 200) {
                $this->response->formatJsonp($data['code'], [], $data['msg']);
            } else {
                $this->response->formatJsonp(200, $data);
            }
        } else {
            $this->response->formatJsonp(400, [], '参数错误');
        }

    }

    /**
     * 休学结束
     */
    public function suspend_end()
    {
        $result = array('code' => 0);
        $this->load->model('teaching_center/leave_model');
        $uid = intval($this->input->post('uid'));
        if($uid){
            $result = $this->leave_model->suspendStop($uid);
        }
        $this->response->json($result);
    }

}