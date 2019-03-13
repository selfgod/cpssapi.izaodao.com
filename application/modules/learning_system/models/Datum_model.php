<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 课程文档模型
 */
class Datum_model extends MY_Model {

    /**
     * 获取课程下载资料
     * @param $scheduleId
     * @param int $pageNum 当前页码
     * @param $pageCount 一页要显示的数量
     * @param int $type 资料类型 1 电子教材 2音频下载 3习题资料 0 全部
     * @return array
     */
    public function getDatums($scheduleId, $pageNum = 1, $pageCount, $type = 0)
    {
        $offset = ($pageNum - 1) * $pageCount;
        $result = [];
        $where = array(
            'ssd.schedule_id' => $scheduleId,
            'ssd.is_del' => 0
        );
        if ($type && is_int($type)) {
            $where['zdl.download_type'] = $type;
        }
        parent::$minor_db->select('names, download_type, tfp_url')
            ->join('zd_netschool.sty_schedule_datum ssd', 'ssd.datum_id = zdl.id', 'left')
            ->where($where);

        $totalCount = parent::$minor_db->count_all_results('zd_downloads zdl', FALSE);
        $query = parent::$minor_db->order_by('update_time', 'DESC')
            ->limit($pageCount, $offset)
            ->get();
        $rows = $query->result_array();
        if (!empty($rows)) {
            $result['data'] = $rows;
            $result['total_page'] = intval(ceil($totalCount / $pageCount));
            $result['current_page'] = $pageNum;
        }
        return $result;
    }
}
