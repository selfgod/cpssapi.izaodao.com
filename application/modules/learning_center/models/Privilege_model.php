<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Privilege_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/member_model');
    }

    /**
     * 获取全部特权信息
     * @return mixed
     */
    public function getAllPrivilegeInfo()
    {
        return parent::$main_db
            ->select('id,name,big_img,big_off_img,small_img,small_off_img,description,summary')
            ->from('sty_privilege')
            ->where('is_del', 0)
            ->order_by('order', 'ASC')
            ->get()->result_array();
    }

    /**
     * 获取用户拥有的特权id
     * @param $uid
     * @return array
     */
    public function getUserPrivilegeIds($uid)
    {
        $user_privilege_ids = array();
        $result = parent::$main_db
            ->select('privilege_id')
            ->from('sty_user_privilege')
            ->where(array(
                'uid' => $uid,
                'is_del' => 0
            ))
            ->get()->result_array();
        if (!empty($result)) {
            foreach ($result as $item) {
                if(!in_array($item['privilege_id'],$user_privilege_ids)){
                    $user_privilege_ids[] = $item['privilege_id'];
                }
            }
        }
        return $user_privilege_ids;
    }

    /**
     * 特权头部信息
     * @param $uid
     * @return mixed
     */
    public function getTopMenuInfo($uid)
    {
        //用户信息
        $data['user_info'] = $this->member_model->getUserMemberInfo($uid);
        if(!empty($data['user_info'])){
            $data['user_info']['headimgurl'] = avatar($data['user_info']['uid'], 'big');
        }
        //特权信息
        $lab = $this->getUserPrivilege($uid);
        $data['user_privilege_count'] = 0;
        if (!empty($lab)) {
            $data['user_privilege_ids'] = $lab['user_privilege_ids'];
            $data['user_privilege_count'] = count($lab['user_privilege_ids']);
            $privilege_list = array();
            if(!empty($lab['privilege'])){
                $user_privilege = $privilege = array();
                foreach($lab['privilege'] as $item){
                    if(in_array($item['id'],$data['user_privilege_ids'])){
                        $user_privilege[] = $item;
                    }else{
                        $privilege[] = $item;
                    }
                }
                $privilege_list = array_merge($user_privilege,$privilege);
            }
            $data['privilege'] = $privilege_list;
        }
        //班主任信息
        $data['teacher'] = $this->member_model->getUserTeacherInfo($uid);
        if (!empty($data['teacher'])) {
            $data['teacher']['headimgurl'] = avatar($data['teacher']['uid'], 'middle');
        }
        $data['user_level'] = 0;
        $user_messge = $this->member_model->getUserMessage($uid);
        if (!empty($user_messge)) {
            $data['user_level'] = intval($user_messge['user_level']);
        }
        return $data;
    }

    /**
     * 获取用户特权信息
     * @param $uid
     * @return array
     */
    public function getUserPrivilege($uid)
    {
        $data = array();
        $privilege = $this->getAllPrivilegeInfo();
        if (!empty($privilege)) {
            $data['privilege'] = $privilege;
            $data['user_privilege_ids'] = array();
            $user_privilege_ids = $this->getUserPrivilegeIds($uid);
            if (!empty($user_privilege_ids)) {
                $data['user_privilege_ids'] = $user_privilege_ids;
            }
        }
        return $data;
    }
}