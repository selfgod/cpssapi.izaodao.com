<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zaodao_model extends MY_Model
{
    /**
     * 获取网站基础信息
     * @return array
     */
    public function info()
    {
        $info = [];
        $phone = parent::$minor_db->select('set_val')
            ->from('zd_base_set')
            ->where(['set_key' => 'content_phone'])
            ->limit(1)
            ->get()->row_array();

        if (empty($phone)) {
            $info['phone']['area'] = '';
            $info['phone']['main'] = '';
        } else {
            if (($pos = strpos($phone['set_val'], '-')) !== FALSE) {
                $info['phone']['area'] = substr($phone['set_val'], 0, $pos);
                $info['phone']['main'] = substr($phone['set_val'], $pos + 1);
            } else {
                $info['phone']['area'] = '';
                $info['phone']['main'] = $phone['set_val'];
            }
        }
        return $info;
    }

    /**
     * 获取校园公告
     * @return mixed
     */
    public function notice($limit)
    {
        $key = 'notice_hList';
        $this->load->driver('cache');
        $result = unserialize($this->cache->redis->hGet($key, 'cpss'));
        if (empty($result)) {
            $result = parent::$main_db->select('jns.title, jns.link')
                ->from('jp_notice_setting as jns')
                ->join('jp_notice_item as jni', 'jni.notice_id = jns.id', 'LEFT')
                ->where([
                    'jns.status' => 1,
                    'jni.category_id' => 2,
                    'jni.is_del' => 0,
                    'jns.is_del' => 0
                ])
                ->order_by('jns.order DESC')
                ->limit($limit)
                ->get()->result_array();
            $this->cache->redis->pipeline();
            $this->cache->redis->hSet($key, 'cpss', serialize($result));
            $this->cache->redis->expire($key, 60 * 20);
            $this->cache->redis->sync();
        }
        return $result;
    }
}
