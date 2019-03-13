<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mygoods extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/goods_model');
        $this->load->library('layout');
    }

    /**
     * 已购产品首页
     */
    public function purchased()
    {
        echo $this->layout->load('purchased_main', 'learning_center');
    }

    /**
     * 已购产品次数统计
     */
    public function purchasedSummary()
    {
        $uid = $this->_uid;
        $data = $this->goods_model->getUserGoodsCount($uid);
        echo $this->layout->loadView('purchased_summary', 'learning_center', $data);
    }

    /**
     * 已购产品列表
     */
    public function purchasedList()
    {
        $uid = $this->_uid;
        $data['goodsInfo'] = $this->goods_model->getPurchaseList($uid);
        if (empty($data['goodsInfo'])) {
            $emptyLink = get_options('vip_link');
            echo $this->layout->loadView('purchased_list_empty', 'learning_center', ['link' => $emptyLink]);
        } else {
            $this->load->model('public/common_model');
            $data['extension_card_days'] = intval($this->common_model->getBaseSetValue('extension_card_days'));
            echo $this->layout->loadView('purchased_list', 'learning_center', $data);
        }

    }

    /**
     * 单个课程体系次数详情
     * @param $category
     */
    public function purchasedCurricular($category)
    {
        $typeCode = get_options('curricular_system', $category);
        $type = $this->input->get('type');
        $uid = $this->_uid;
        if (!$typeCode) {
            show_error('参数错误');
        }

        if (empty($type)) {
            $data = array(
                'system' => $typeCode,
                'category' => $category
            );
            echo $this->layout->load('purchased_detail', 'learning_center', $data);
        } elseif ($type === 'total') {
            //单个课程体系总次数统计
            $goods = $this->goods_model->curricularTotalAnalyze($uid, $typeCode);
            if ($category == 'major' || $category == 'elective' || $category == 'special') {
                $emptyLink = get_options('svip_link');
            } else {
                $emptyLink = MASTER_DOMAIN;
            }
            $data = array_merge(
                array(
                    'title' => get_options('curricular_system_zh', $category),
                    'emptyLink' => $emptyLink
                ), $goods);

            echo $this->layout->loadView('total_count_analyze', 'learning_center', $data);
        } elseif ($type === 'detail') {
            //单个课程体系次数使用明细
            $pageNum = 1;
            $limit = 5;
            $auditList = $this->goods_model->curricularDetailAnalyze($uid, $typeCode, $limit, $pageNum);
            echo $this->layout->load('purchased_detail_audit', 'learning_center', $auditList);
        } else {
            show_error('参数错误');
        }
    }

    /**
     * 单个课程体系次数使用明细
     * @param $category
     */
    public function curricularAudit($category)
    {
        $typeCode = get_options('curricular_system', $category);
        $pageNum = intval($this->input->get('page'));
        $pageNum = empty($pageNum) ? 1 : $pageNum;
        $uid = $this->_uid;
        $limit = 5;
        $auditList = $this->goods_model->curricularDetailAnalyze($uid, $typeCode, $limit, $pageNum);
        echo $this->layout->load('purchased_detail_audit_pager', 'learning_center', $auditList);
    }

    /**
     * 激活商品
     */
    public function activateGoods()
    {
        $goodsId = intval($this->input->post('goods_id'));
        $uid = $this->_uid;
        $result = $this->goods_model->activateGoods($uid, $goodsId);
        $this->response->formatJson($result['code'], $result['data'], $result['msg']);
    }

}