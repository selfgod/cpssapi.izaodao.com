<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zdtalk extends My_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('layout');
    }

    public static $_zdTalkConf = [];

    /**
     * 主方法
     * @param $action
     */
    public function main($action)
    {
        $zdTalkConf = $this->getZdTalkConf();
        if (isset($zdTalkConf[$action]) && !empty($zdTalkConf[$action])) {
            $href = NULL;
            if (isset($zdTalkConf[$action]['lesson'])) {
                $href = $this->getZdTalkUrl($action);
            }
            $title = $this->getTitle($action);
            $this->load->model('public/member_model');
            $pwd = $this->member_model->getUserPwd($this->_uid);
            $this->response->html($this->layout->loadView('zdtalk_test', 'learning_system', [
                'title' => $title,
                'href' => $href,
                'action' => $action,
                'is_pwd' => empty($pwd) ? 0 : 1
            ]));
        }
    }

    /**
     * 教室url
     */
    public function classroomUrl()
    {
        $result = ['code' => 500];
        $action = $this->input->get('action', TRUE);
        $href = $this->getZdTalkUrl($action);
        if (!empty($href)) {
            $result['code'] = 200;
            $result['href'] = $href;
        }
        $this->response->json($result);
    }

    /**
     * zdtalk软提醒页面
     * @param $lesson_id
     */
    public function lessonClassroom()
    {
        $result = $this->layout->load('zdtalk_lesson', 'learning_center', [
            'talk_download' => '//service.izaodao.com/download.do?appId=7b198387d5714e75894500a14ec5ac5e&platform=1',
            'browser_download' => '//download.izaodao.com/software/61.0.3159.5_chrome_installer.exe',
            'qq_link' => '//wpa.qq.com/msgrd?v=3&uin=800118811&site=qq&menu=yes'
        ]);
        $this->response->html($result);
    }

    /**
     * 更新密码
     */
    public function updatePwd()
    {
        $result = ['code' => 500, 'msg' => ''];
        $pwd = $this->input->post('pwd', TRUE);
        if (!preg_match("/^[A-Za-z0-9!@#$%^&*()]+$/", $pwd) || strlen($pwd) < 6 || strlen($pwd) > 16) {
            $result['msg'] = '密码格式不正确';
        } else {
            $this->load->model('public/member_model');
            $password = $this->member_model->getUserPwd($this->_uid);
            if (!empty($password)) {
                $result['msg'] = '已存在登录密码';
            } else {
                $this->load->library('thrift');
                $setPwd = $this->thrift->service('User')->updatePassword($this->_login['openId'], $pwd, []);
                if ($setPwd === TRUE) {
                    $result['code'] = 200;
                }
            }
        }
        $this->response->json($result);
    }

    /**
     * 获取zdTalk配置
     * @return array
     */
    protected function getZdTalkConf()
    {

        if (!empty(self::$_zdTalkConf)) {
            return self::$_zdTalkConf;
        }
        $zdtalkConf = [];
        $this->load->model('public/common_model');
        $group = $this->common_model->getGroupCategory('ZdTalk启动设置');
        if (!empty($group)) {
            foreach ($group as $item) {
                $key = NULL;
                $data = [];
                if (!empty($item['values'])) {
                    foreach ($item['values'] as $val) {
                        if (strpos($val['name'], '-')) {
                            $arr = explode('-', $val['name']);
                            if (!empty($arr)) {
                                if ($arr[0] === 'key') {
                                    $key = $arr[1];
                                } else {
                                    $data[$arr[0]] = $arr[1];
                                }
                            }
                        }
                    }
                }
                if (!empty($key)) {
                    $data['title'] = $item['title'];
                    $zdtalkConf[$key] = $data;
                }
            }
        }
        if (!empty($zdtalkConf)) {
            $this->setZdTalkConf($zdtalkConf);
        }
        return $zdtalkConf;
    }

    /**
     * 设置zdTalk配置
     * @param $data
     */
    protected function setZdTalkConf($data)
    {
        self::$_zdTalkConf = $data;
    }

    /**
     * 获取zdTalk网启链接
     * @param $method
     * @return null|string
     */
    protected function getZdTalkUrl($method)
    {
        $href = NULL;
        $zdTalkConf = $this->getZdTalkConf();
        if (isset($zdTalkConf[$method])) {
            $lesson_id = $schedle_id = 0;
            if (!empty($zdTalkConf[$method]['lesson'])) {
                $lesson_id = intval($zdTalkConf[$method]['lesson']);
            } elseif (!empty($zdTalkConf[$method]['schedule'])) {
                $this->load->model('public/lesson_model');
                $schedle_id = intval($zdTalkConf[$method]['schedule']);
                $lesson_val = $this->lesson_model->getScheduleLessonDetail([
                    'ssl.schedule_id' => $schedle_id,
                    'ssl.end_time>' => date('Y-m-d H:i:s')
                ], [], ['ssl.start_time ASC'], [1]);
                if (!empty($lesson_val)) {
                    $lesson_id = $lesson_val['id'];
                }
            }
            if ($lesson_id) {
                $this->load->model('public/schedule_model');
                $href = $this->schedule_model->getZDTalkLink($this->_uid, $lesson_id);
            }
        }
        return $href;
    }

    /**
     * zdTalk Title
     * @param $method
     * @return null
     */
    protected function getTitle($method)
    {
        $title = NULL;
        $zdTalkConf = $this->getZdTalkConf();
        if (isset($zdTalkConf[$method]) && !empty($zdTalkConf[$method]['title'])) {
            $title = $zdTalkConf[$method]['title'];
        }
        return $title;
    }
}
