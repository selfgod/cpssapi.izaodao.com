<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends MY_Model
{
    /**
     * 获取用户订单信息
     * @param $uid
     * @param int $status 订单状态 0:等待付款,1:付款成功,2已过期,3:已退款
     * @param null $timestamp
     * @return mixed
     */
    public function userOrder($uid, $status, $timestamp = null)
    {
        $where = [
            'po_uid' => $uid,
            'status' => $status,
        ];
        if ($timestamp) {
            $where['po_submittime >='] = $timestamp;
        }
        return parent::$minor_db->select('po_sid')
            ->from('netschool_pay_order')
            ->where($where)
            ->get()->result_array();
    }
}