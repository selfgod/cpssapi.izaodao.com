<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_options')) {
    /**
     * 获取选项列表内容
     * @param $key
     * @param null $optionKey
     * @return bool|mixed
     */
    function get_options($key, $optionKey = null)
    {
        $CI = &get_instance();
        $CI->config->load('options', TRUE);
        $totalOptionsArray = $CI->config->item('options_array', 'options');
        if (isset($totalOptionsArray[$key])) {
            if ($optionKey !== null &&
                isset($totalOptionsArray[$key][$optionKey])
            ) {
                return $totalOptionsArray[$key][$optionKey];
            } elseif ($optionKey === null) {
                return $totalOptionsArray[$key];
            }
        }
        return FALSE;
    }
}

if (!function_exists('convert_keys_2_vaules')) {
    /**
     * 通过options keys数组获取对应的values数组
     * @param $key
     * @param array $keys
     * @return array
     */
    function convert_keys_2_vaules($key, $keys = array())
    {
       $result = array();
       if ($options = get_options($key)) {
           foreach ($keys as $key) {
               if (isset($options[$key])) {
                   $result[] = $options[$key];
               }
           }
       }
       return $result;
    }
}