<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('human_time_diff')) {

    /**
     * 返回两个时间点的差距
     * 不到一小时显示相差多少分钟
     * 不到一天显示相差多少小时
     * 超过一天的显示相差多少天
     * @param $start
     * @param $end
     * @return bool|string
     */
    function human_time_diff($start, $end)
    {
        $start = intval($start);
        $end = intval($end);

        if ($end < $start) {
            return FALSE;
        }

        $diff = $end - $start;
        if ($diff < 60) {
            return '1分钟';
        } elseif ($diff < 3600) {
            return intval($diff/60).'分钟';
        } elseif ($diff < 3600 * 24) {
            return intval($diff / 3600).'小时';
        } else {
            return intval($diff / (3600 * 24)).'天';
        }
    }
}

if (!function_exists('remain_time')) {
    /**
     * 将秒数转换成剩余时间文字
     * @param $remain
     * @return string
     */
    function remain_time($remain)
    {
        $remain = intval($remain);
        if ($remain === 0) {
            return '0秒';
        }
        $str = '';
        $day = intval(floor($remain / (60 * 60 * 24)));
        $hour = intval(floor($remain / (60 * 60)) - ($day * 24));
        $min = intval(floor($remain / 60) - ($day * 24 * 60) - ($hour * 60));
        $sec = intval(floor($remain) - ($day * 24 * 60 * 60) - ($hour * 60 * 60) - ($min * 60));

        if ($day) {
            $str .= $day . '天';
        }
        if ($hour) {
            $str .= $hour . '小时';
        }
        if ($min) {
            $str .= $min . '分钟';
        }
        if ($sec) {
            $str .= $sec . '秒';
        }
        return $str;
    }
}

if (!function_exists('diff_days')) {
    /**
     * 计算两个时间点相差的天数（按照日期计算）
     * 例如：2016-12-03 08：12：00 和 2016-12-04 10：12：00 相差一天，
     * 2016-12-03 15：12：00 和 2016-12-04 06：12：00 相差一天，
     * @param $start
     * @param $end
     * @return int
     */
    function diff_days($start, $end)
    {
        $start = intval($start);
        $end = intval($end);

        if ($start > $end) {
            return 0;
        }

        $start = strtotime(date('Y/m/d 00:00:01', $start));
        $end = strtotime(date('Y/m/d 00:00:01', $end));

        return intval(($end - $start) / (3600 * 24));
    }
}


if (!function_exists('remain_days')) {
    /**
     * 计算两个时间相差多少天，如果超过一年则返回x年x天, 包括当天
     * @param $start
     * @param $end
     * @return array
     */
    function remain_days($start, $end)
    {
        $start = intval($start);
        $end = intval($end);

        if ($start >= $end) {
            return array(0, 0);
        }
        $end += 3600 * 24;//向后推一天
        $start = date_create(date('Y-m-d', $start));
        $end = date_create(date('Y-m-d', $end));
        $interval = date_diff($start, $end);

        $str = $interval->format('%y %a');
        list($years, $days) = explode(' ', $str);
        if ($years === 0) {
            return [0, intval($days)];
        } else {
            date_modify($start, "+$years years");
            $days = intval(date_diff($start, $end)->format('%a'));
            return [$years, $days];
        }
    }
}
