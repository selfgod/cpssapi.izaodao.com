<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rank extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('learning_system/learning_report_model');
        $this->load->library('layout');
    }

    /**
     * 排行榜面板页面
     */
    public function rankPanel()
    {
        $scheduleId = intval($this->input->get('schedule'));
        $action = $this->input->get('action', TRUE);
        $page = 1;
        $uid = $this->_uid;
        $type = 'week';
        $pageCount = 5;
        $data = $this->getRankPanelData($uid, $type, $scheduleId, $page, $pageCount);
        $data['action'] = $action;
        $ret = $this->layout->load('rank_panel', 'public', $data);
        echo $ret;
    }

    /**
     * 切换周学榜，总学榜
     * @param $type
     */
    public function rankPanelBody($type)
    {
        $scheduleId = intval($this->input->get('schedule'));
        $type = empty($type) ? 'week' : $type;
        $page = 1;
        $uid = $this->_uid;
        $pageCount = 5;

        $data = $this->getRankPanelData($uid, $type, $scheduleId, $page, $pageCount);
        $ret = $this->layout->load('rank_panel_body', 'public', $data);
        echo $ret;
    }

    /**
     * 获取排行榜全部数据
     * @param $uid
     * @param $type
     * @param $scheduleId
     * @param $page
     * @param $pageCount
     * @return mixed
     */
    private function getRankPanelData($uid, $type, $scheduleId, $page, $pageCount)
    {
        if (empty($scheduleId)) {
            $scheduleId = null;
        }
        $data['score'] = $this->learning_report_model->getUserScore($uid, $type, $scheduleId);
        $data['list'] = $this->learning_report_model->scheduleRankList($scheduleId, $page, $pageCount, $type);
        $data['rank'] = $this->learning_report_model->getUserRank($uid, $type, $scheduleId);
        $data['current'] = $page;
        $totalCount = $this->learning_report_model->getTotalCount($scheduleId, $type);
        $data['total_page'] = ceil($totalCount / $pageCount);
        $data['type'] = $type;

        return $data;
    }

    /**
     * 获取排名列表
     */
    public function rankList()
    {
        $scheduleId = intval($this->input->get('schedule'));
        if (empty($scheduleId)) {
            $scheduleId = null;
        }
        $page = intval($this->input->get('page'));
        $page = empty($page) ? 1 : $page;
        $type = $this->input->get('type');
        $type = empty($type) ? 'week' : $type;
        $pageCount = 5;

        $data['list'] = $this->learning_report_model->scheduleRankList($scheduleId, $page, $pageCount, $type);
        $ret = $this->layout->loadView('rank_list', 'public', $data);
        echo $ret;
    }
}