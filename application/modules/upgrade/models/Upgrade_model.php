<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upgrade_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/goods_model');
    }

    /**
     * 获取已购买商品信息
     * @param $uid
     * @return array
     */
    public function purchasedGoods($uid)
    {
        $this->load->helper('date');
        $goods = array();
        $now = time();
        $curricular = array_flip(get_options('curricular_system'));
        $baseGoods = $this->goods_model->getUsergoodsType($uid);
        foreach ($baseGoods as $item) {
            $goods[$item['goods_id']]['is_activate'] = $item['is_activate'];
            $goods[$item['goods_id']]['unlimit_expire'] = !empty($item['unlimit_expire']);
            $goods[$item['goods_id']]['deal_price'] = $item['deal_price'];
            if ($item['is_activate'] === '1') {
                $goods[$item['goods_id']]['activate_date'] = date('Y/m/d', strtotime($item['activate_time']));
                $goods[$item['goods_id']]['expire_date'] = date('Y/m/d', strtotime($item['expire']));
                $goods[$item['goods_id']]['activate_days'] = diff_days(strtotime($item['activate_time']), $now);
            }
            //商品有效期
            $buyInfo = json_decode($item['buy_info'], TRUE);
            if (empty($buyInfo)) {
                $buyInfo = unserialize($item['buy_info']);
            }
            if (!isset($buyInfo['valid_type']) || $buyInfo['valid_type'] === 0) {
                $expire_num = intval($buyInfo['valid']['num']);
                $expire_unit = intval($buyInfo['valid']['unit']);
                $goods[$item['goods_id']]['expire_str'] = $expire_num . get_options('expire_unit_zh', $expire_unit);
            } else {
                $goods[$item['goods_id']]['expire_str'] = '至' . date('Y/m/d', strtotime($buyInfo['valid']['end_time']));
            }
            $type = $curricular[$item['type']];
            if (!empty($item['unlimit_count'])) {
                $goods[$item['goods_id']]['count_list'][$type]['is_unlimit'] = 1;
            } else {
                $goods[$item['goods_id']]['count_list'][$type]['total'] = intval($item['count']);
            }
        }

        $goodsBaseInfo = $this->goods_model->goodsBaseInfo(array_keys($goods));
        foreach ($goodsBaseInfo as $info) {
            $goods[$info['id']]['name'] = $info['name'];
        }

        return $goods;
    }

    /**
     * 获取指定商品的可升级商品列表
     * @param $uid
     * @param $goodsId
     * @return array
     */
    public function upgradeGoodsList($uid, $goodsId)
    {
        $goodsInfo = $this->goods_model->getGoodsInfo($uid, $goodsId);
        $validData = array(
            'uid' => $uid,
            'goods' => $goodsInfo
        );
        if (($isValid = $this->validation->validate('upgrade', $validData)) !== TRUE) {
            return $isValid;
        }
//        规定日期后，有可查订单的商品(暂定2016/9/6 零点)
        $this->load->model('public/member_model');
        if ($this->member_model->hasValidOrder($uid) === FALSE)
        {
            return [];
        }

        $purchased = $this->goods_model->userPurchasedGoods($uid);
        $formatedGoods = [];
        $scrap_value = $this->upgrade_discount($goodsInfo);
        if ($goodsInfo['identity'] == 5) {
            $goodsArray = $this->allOtherGoods($goodsId, $scrap_value);
        } else {
            $goodsArray = $this->sameCategoryGoods($goodsId, $scrap_value);
            $higherTierGoods = $this->higherTierGoods($goodsId, $scrap_value);
            foreach ($higherTierGoods as $higherGoods) {
                $goodsArray[] = $higherGoods;
            }
        }
        foreach ($goodsArray as $goods) {
            //已经购买的商品在可升级列表不显示
            if (!in_array($goods['id'], $purchased)) {
                if (intval($goods['valid_type']) === 0) {
                    $goods['expire_str'] = $goods['valid_num'] . get_options('expire_unit_zh', $goods['valid_unit']);
                } else {
                    $goods['expire_str'] = '至' . date('Y/m/d', strtotime($goods['end_time']));
                }
                $formatedGoods[$goods['id']] = $goods;
            }

        }
        if (!empty($formatedGoods)) {
            $curricular = array_flip(get_options('curricular_system'));
            $coutList = $this->goods_model->goodsOriginalCount(array_keys($formatedGoods));
            foreach ($coutList as $count) {
                $type = $curricular[$count['category']];
                if (!empty($count['class_num_nolimit'])) {
                    $formatedGoods[$count['goods_id']]['count_list'][$type]['is_unlimit'] = 1;
                } else {
                    $formatedGoods[$count['goods_id']]['count_list'][$type]['total'] = $count['class_num'];
                }
            }
        }

        return $formatedGoods;
    }

    /**
     * 达人卡，获取所有分类下的可升级商品
     * @param int $goodsId 商品id
     * @param int $dealPrice 成交价
     */
    public function allOtherGoods($goodsId, $dealPrice)
    {
        $result = parent::$main_db->select('id, name, discount_price, valid_unit, valid_num, valid_type, end_time')
            ->from('sty_goods')
            ->where(array(
                'is_del' => 0,
                'discount_price >' => $dealPrice,
                'is_open' => 1,
                'upgradeable' => 1
            ))
            ->get()->result_array();
        //移除原商品信息
        foreach ($result as $index => $goods) {
            if ($goods['id'] === $goodsId) {
                unset($result[$index]);
                break;
            }
        }
        return $result;
    }

    /**
     * 获取相同升级分类下的可升级商品
     * @param int $goodsId 商品id
     * @param int $dealPrice 成交价
     */
    public function sameCategoryGoods($goodsId, $dealPrice)
    {
        $result = parent::$main_db->select('sg2.id, sg2.name, sg2.discount_price,
          sg2.valid_unit, sg2.valid_num, sg2.valid_type, sg2.end_time')
            ->from('sty_goods sg1')
            ->join('sty_goods sg2', 'sg1.upgrade_cat = sg2.upgrade_cat', 'left')
            ->where(array(
                'sg1.id' => $goodsId,
                'sg1.upgradeable' => 1,
                'sg1.is_del' => 0,
                'sg2.is_del' => 0,
                'sg2.discount_price >' => $dealPrice,
                'sg2.is_open' => 1,
                'sg2.upgradeable' => 1
            ))
            ->get()->result_array();

        //移除原商品信息
        foreach ($result as $index => $goods) {
            if ($goods['id'] === $goodsId) {
                unset($result[$index]);
                break;
            }
        }

        return $result;
    }

    /**
     * 获取不同升级分类下的可升级商品
     * 根据商品的tier值计算， 值低的可向高的升
     * @param $goodsId
     * @param $dealPrice
     * @return array
     */
    public function higherTierGoods($goodsId, $dealPrice)
    {
        $tier = $this->goodsTier($goodsId);
        if (empty($tier)) {
            return [];
        }
        $upgradeCateKey = get_options('sys_category', 'upgrade');
        $result = parent::$main_db->select('sg.id, sg.name,
            sg.discount_price, sg.valid_num, sg.valid_unit, sg.valid_type, sg.end_time')
            ->from('sty_sys_category_item ssci')
            ->join('sty_sys_category ssc', 'ssc.id = ssci.category_id', 'left')
            ->join('sty_goods sg', 'sg.upgrade_cat = ssci.order', 'left')
            ->where(array(
                'ssc.key' => $upgradeCateKey,
                'ssci.is_del' => 0,
                'ssc.is_del' => 0,
                'ssci.tier >' => $tier,
                'sg.is_del' => 0,
                'sg.is_open' => 1,
                'sg.discount_price >' => $dealPrice,
                'sg.upgradeable' => 1
            ))
            ->get()->result_array();

        return $result;
    }

    /**
     * 获取可升级商品的级别
     * @param $goodsId
     * @return int
     */
    public function goodsTier($goodsId)
    {
        $upgradeCateKey = get_options('sys_category', 'upgrade');
        $result = parent::$main_db->select('ssci.tier, ssci.name')
            ->from('sty_sys_category_item ssci')
            ->join('sty_sys_category ssc', 'ssc.id = ssci.category_id', 'left')
            ->join('sty_goods sg', 'sg.upgrade_cat = ssci.order', 'left')
            ->where(array(
                'ssc.key' => $upgradeCateKey,
                'ssci.is_del' => 0,
                'ssc.is_del' => 0,
                'sg.id' => $goodsId,
                'sg.is_del' => 0,
                'sg.upgradeable' => 1
            ))
            ->get()->row_array();

        if (empty($result)) {
            return 0;
        } else {
            return $result['tier'];
        }
    }

    /**
     * 商品升级核算
     * @param $uid
     * @return array
     */
    public function upgradeAccounting($uid, $from)
    {
        $originGoods = $this->goods_model->getGoodsInfo($uid, $from);
        $validData = array(
            'uid' => $uid,
            'goods' => $originGoods
        );
        if (($isValid = $this->validation->validate('accounting', $validData)) !== TRUE) {
            show_error($isValid['msg']);
        }
        $this->load->model('public/member_model');
        $userInfo = $this->member_model->realNamePhone($uid);
        $userInfo['currency'] = $this->member_model->getCurrency($uid);
        $to = $originGoods['upgrade_intention'];
        $targetGoods = $this->goods_model->goodsBaseInfo($to);
        return array(
            'origin' => array(
                'id' => $from,
                'name' => $originGoods['name'],
                'deal_price' => $originGoods['deal_price']
            ),
            'target' => array(
                'id' => $to,
                'name' => $targetGoods['name'],
                'discount_price' => $targetGoods['discount_price']
            ),
            'user' => $userInfo,
            'protocol' => MASTER_DOMAIN . "main.php/Course/Protocol/index/goods_id/{$to}.html"
        );
    }

    /**
     * 设置用户升级意向
     * @param $uid
     * @param $from
     * @param $to
     * @return mixed
     */
    public function userIntention($uid, $from, $to)
    {
        $originGoods = $this->goods_model->getGoodsInfo($uid, $from);
        $originGoods['scrap_value'] = $this->upgrade_discount($originGoods);
        $targetGoods = $this->goods_model->goodsBaseInfo($to, TRUE);

        $validData = array(
            'uid' => $uid,
            'goods' => $originGoods,
            'originGoods' => $originGoods,
            'targetGoods' => $targetGoods
        );
        if (($result = $this->validation->validate('intention', $validData)) !== TRUE) {
            $this->response->formatJson($result['code'], [], $result['msg']);
        }

        return parent::$main_db->set([
            'upgrade_intention' => $to
        ])->where([
            'uid' => $uid,
            'goods_id' => $from,
            'is_del' => 0
        ])->update('sty_user_goods');
    }

    /**
     * 计算升级优惠
     *
     * （1）已激活90天以内（包括90天）
     * 升级支付价=（将升级课程商品优惠价-原商品成交价-其他优惠-早元
     * （2）已激活90天以上（第91天起）
     * 升级支付价=（将升级课程商品优惠价-原未消费学费-其他优惠-早元
     * 注：
     * 未消费学费=原商品成交价 - (原商品成交价/原商品有效期天数)*已激活天数
     * @param $uid
     * @param $origin_goods_id
     * @param int $deal_price 指定成交价（zdmis）
     * @return array
     */
    public function upgrade_discount($goods) {
        $this->load->helper('date');
        if ($goods) {
            $deal_price = intval($goods['deal_price']);
            $now = time();
            $diffDays = diff_days(strtotime($goods['activate_time']), $now);
            if ($diffDays <= 90) {
                return $deal_price;
            } else {
                $expire_days = diff_days(strtotime($goods['activate_time']), strtotime($goods['expire']));
                $scrap_value = round($deal_price - $deal_price * $diffDays / $expire_days);
                return $scrap_value;
            }
        } else {
            return 0;
        }
    }
}