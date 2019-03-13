<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 续费升级
 * Class Upgrade
 */
class Upgrade extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $noSuspend = $this->validation->validate('suspend', ['uid' => $this->_uid]);
        $method = $this->router->fetch_method();
        if ($noSuspend !== TRUE) {
            if ($method !== 'index' && $method !== 'accounting') {
                $this->response->formatJson($noSuspend['code'], [], $noSuspend['msg']);
            } else {
                redirect('/');
            }
        }
        $this->load->library('layout');
        $this->load->model('upgrade/upgrade_model');
    }

    /**
     * 选择升级商品页
     */
    public function index()
    {
        $goodsId = intval($this->input->get('goods'));
        $uid = $this->_uid;
        $validData = array(
            'uid' => $uid,
        );
        if (($isValid = $this->validation->validate('suspend', $validData)) !== TRUE) {
            show_error($isValid['msg']);
        }
        $data = array('goodsId' => $goodsId);
        $this->load->model('public/common_model');
        $data['extension_card_days'] = intval($this->common_model->getBaseSetValue('extension_card_days'));
        echo $this->layout->load('upgrade', 'upgrade', $data);
    }

    /**
     * 如果goodsid为0,则获取当前用户的已购商品信息
     * 否则获取该商品的可升级商品列表信息
     * @param int $goodsId
     */
    public function goods($goodsId = 0)
    {
        $uid = $this->_uid;
        if (empty($goodsId)) {
            if (($isValid = $this->validation->validate('suspend', ['uid' => $uid])) !== TRUE) {
                $this->response->formatJson($isValid['code'], [], $isValid['msg']);
            }
            $data = $this->upgrade_model->purchasedGoods($uid);
        } else {
            $data = $this->upgrade_model->upgradeGoodsList($uid, $goodsId);
        }
        if (isset($data['code']) && $data['code'] !== 200) {
            $this->response->formatJson($data['code'], [], $data['msg']);
        } else {
            $this->response->formatJson(200, $data);
        }

    }

    /**
     * 用户升级意向
     */
    public function intention()
    {
        $from = intval($this->input->post('from'));
        $to = intval($this->input->post('to'));
        $uid = $this->_uid;
        $result = $this->upgrade_model->userIntention($uid, $from, $to);
        if ($result) {
            $this->response->formatJson(200, [], 'ok');
        } else {
            $this->response->formatJson(500, [], 'error');
        }
    }

    /**
     * 升级优惠核算
     */
    public function accounting()
    {
        $from = intval($this->input->get('from'));
        $uid = $this->_uid;
        $data = $this->upgrade_model->upgradeAccounting($uid, $from);
        echo $this->layout->load('accounting', 'upgrade', $data);
    }

}