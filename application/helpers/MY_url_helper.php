<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('img_url')) {
    function img_url($module, $filename)
    {
        $uri = '/static/image/' . $module . '/' . $filename;
        return get_instance()->config->base_url($uri, NULL);
    }
}

if (!function_exists('css_url')) {
    function css_url($module, $filename)
    {
        $uri = '/static/css/' . $module . '/' . $filename.'?v=201708021706';
        return get_instance()->config->base_url($uri, NULL);
    }
}

if (!function_exists('avatar')) {
    function avatar($uid, $size = 'middle')
    {
        //http://ohp6uiqfe.bkt.clouddn.com/avatar/1b63a06791ec42ddae907d44ac471a07?xxxxx&imageView2/1/w/44/h/44
        $sizeArr = [
            'small' => 48,
            'middle' => 120,
            'big' => 200
        ];
        if (isset($sizeArr[$size])) {
            $pix = $sizeArr[$size];
        } else {
            $pix = $sizeArr['middle'];
        }
        get_instance()->load->model('public/member_model');
        $member = get_instance()->member_model->getUserMemberInfo($uid);
        if (!empty($member) && !empty($member['avatar'])) {
            return get_options('qiniu_prefix') . "avatar/{$member['open_id']}?{$member['version']}&imageView2/1/w/{$pix}/h/{$pix}";
        } else {
            $size = isset($sizeArr[$size]) ? $size : 'middle';
            $uri = MASTER_DOMAIN . 'uc/avatar.php?uid=' . $uid . '&size=' . $size;
            return $uri;
        }
    }
}
