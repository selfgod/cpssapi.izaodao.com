<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 协议
 */
class Protocol_model extends MY_Model
{
    /**
     * 获取用户商品的协议
     * @param $uid
     * @param $goodsIds
     * @return mixed
     */
    public function getUserProtocol($uid, $goodsIds)
    {
        parent::$main_db->select('checkActive, goods_id')
            ->from('sty_user_protocol')
            ->where('uid', $uid);

        if (is_array($goodsIds)) {
            return parent::$main_db->where_in('goods_id', $goodsIds)->get()->result_array();
        } else {
            return parent::$main_db->where('goods_id', $goodsIds)->get()->row_array();
        }
    }
}