<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Passport_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('public/member_model');
    }

    /**
     * 用户注册
     * @param $paramStr
     * @return array
     */
    public function register($paramStr)
    {
        $keyMap = [
            'userName' => 'username',
            'password' => 'password',
            'salt' => 'salt',
            'mobile' => 'mobile',
            'email' => 'email',
            'openId' => 'open_id',
            'version' => 'version',
            'tag' => 'tag',
            'appId' => 'appId',
            'registSourceLabel' => 'registSourceLabel',
            'requestIp' => 'ip',
            'bindWXUqId' => 'union_id',
            'appName' => 'appName',
            'resourceCat' => 'resourceCat',
            'interCode' => 'interCode'
        ];
        $params = json_decode($paramStr, true);
        $data = $ret = [];
        $uid = 0;
        if (empty($params)) {
            $ret['code'] = 500;
            $ret['msg'] = '参数错误';
        } else {
            foreach ($params as $key => $value) {
                if (isset($keyMap[$key])) {
                    $data[$keyMap[$key]] = $value;
                }
            }
            $data['cat'] = (isset($data['resourceCat']) && $data['resourceCat']) ? urldecode($data['resourceCat']) : $data['registSourceLabel'] . '-' . $data['appName'];
            $info = $this->member_model->getUserMemberInfoByName($params['userName']);
            if (!empty($info)) {
                $ret['code'] = 500;
                $ret['msg'] = '用户名重复';
            } else {
                parent::$minor_db->trans_begin();
                $uid = $this->addCommonMember($data);
                if ($uid <= 0 || parent::$minor_db->trans_status() === FALSE) {
                    parent::$minor_db->trans_rollback();
                    $ret['code'] = 500;
                    $ret['msg'] = '注册失败';
                } elseif ($this->addUcMember($uid, $data) === TRUE) {
                    parent::$minor_db->insert('jh_common_member_count', ['uid' => $uid]);
                    parent::$minor_db->insert('jh_common_member_profile', [
                        'uid' => $uid,
                        'mobile' => $data['mobile'],
                        'open_id' => $data['open_id']
                    ]);
                    $this->addCommonMemberStatus($uid);
                    $this->upsertWXBind($uid, $data);
                    $this->addMemberLog($uid, $data['username']);
                    parent::$minor_db->insert('jh_common_member_field_forum', ['uid' => $uid]);
                    parent::$minor_db->insert('jh_common_member_field_home', ['uid' => $uid]);
                    if ($data['cat'] != 'not.push') {
                        $crmData = $data;
                        $crmData['ip'] = (isset($params['ip']) && !empty($params['ip'])) ? $params['ip'] : '';
                        $this->addCrm($uid, $crmData);
                    }
                    if (parent::$minor_db->trans_status() === FALSE) {
                        parent::$minor_db->trans_rollback();
                        $ret['code'] = 500;
                        $ret['msg'] = '注册失败';
                    } else {
                        parent::$minor_db->trans_commit();
                        $this->noticeConsult($uid, $data);
                        $ret['code'] = 200;
                        $ret['msg'] = '注册成功';
                    }
                } else {
                    parent::$minor_db->trans_rollback();
                    $ret['code'] = 500;
                    $ret['msg'] = '注册失败';
                }
            }
        }

        $this->logRegister($ret, $paramStr, $uid);
        return $ret;
    }

    /**
     * 注册的时候根据手机号或者邮箱完善资询CC
     * @param $uid
     * @param $params
     */
    public function noticeConsult($uid, $params)
    {
        $email = isset($params['email']) ? $params['email'] : '';
        $url = MASTER_DOMAIN . 'main.php/User/Register/consultBind';
        $client = new GuzzleHttp\Client(['timeout' => 3.0]);
        $response = $client->request('POST', $url, [
            'form_params' => [
                'uid' => $uid,
                'mobile' => $params['mobile'],
                'email' => $email
            ]
        ]);
    }

    /**
     * 添加crm数据
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function addCrm($uid, $params)
    {
        $tag = isset($params['tag']) ? $params['tag'] : '';
        $ip = isset($params['ip']) ? $params['ip'] : '';
        $this->load->library('thrift');
        try {
            $this->thrift->service('Kafka')->producer('spread', 'addCrm', [
                'tel' => $params['mobile'],
                'uid' => $uid,
                'openId' => $params['open_id'],
                'cat' => $params['cat'],
                'source' => '注册成功',
                'tag' => $tag,
                'platform' => "",
                'lang_type' => "",
                'ip' => $ip,
                'interCode' => $params['interCode']
            ]);
            return TRUE;

        } catch (\Exception $e) {
            return FALSE;
        }
    }

    /**
     * 添加commom member
     * @param $params
     * @return bool|int uid
     */
    public function addCommonMember($params)
    {
        $data = [];
        $fields = ['username', 'password', 'email', 'mobile', 'open_id', 'version'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            $data['coding'] = '';
            $data['mail_val_code'] = '';
            $data['regdate'] = time();
            parent::$minor_db->insert('jh_common_member', $data);
            return parent::$minor_db->insert_id();
        } else {
            return 0;
        }
    }

    /**
     * 添加uc_member库
     * @param $params
     * @return mixed
     */
    public function addUcMember($uid, $params)
    {
        $data = [];
        $fields = ['username', 'password', 'email', 'salt', 'mobile', 'open_id', 'ip'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            $data['uid'] = $uid;
            $data['regdate'] = time();
            if (isset($data['ip'])) {
                $data['regip'] = $data['ip'];
                unset($data['ip']);
            }
            $ucdb = $this->load->database('uc', TRUE);
            return $ucdb->insert('uc_members', $data);
        } else {
            return TRUE;
        }
    }

    /**
     * 添加member log
     * @param $uid
     * @param $userName
     * @return mixed
     */
    public function addMemberLog($uid, $userName)
    {
        $data = [
            'uid' => $uid,
            'username' => $userName,
            'tablename' => 'zd_uc.uc_members',
            'fieldid' => 'password',
            'dateline' => time(),
            'note' => '新注册用户'
        ];

        return parent::$minor_db->insert('zd_member_log', $data);
    }

    /**
     * 添加common member status记录
     * @param $uid
     * @return mixed
     */
    public function addCommonMemberStatus($uid)
    {
        $data = [
            'uid' => $uid,
            'lastvisit' => time(),
            'lastactivity' => time(),
            'lastpost' => 0,
            'lastsendmail' => 0
        ];
        return parent::$minor_db->insert('jh_common_member_status', $data);
    }

    /**
     * 更新用户信息
     * @param $paramStr
     * @return bool
     */
    public function update($paramStr)
    {
        $keyMap = [
            'userName' => 'username',
            'password' => 'password',
            'salt' => 'salt',
            'avatar' => 'avatar',
            'realName' => 'realname',
            'identityCode' => 'idcard',
            'gender' => 'gender',
            'mobile' => 'mobile',
            'email' => 'email',
            'birthYear' => 'birthyear',
            'birthMonth' => 'birthmonth',
            'birthDay' => 'birthday',
            'version' => 'version',
            'bindWXUqId' => 'union_id',
            'openId' => 'open_id',
        ];
        $data = [];
        $params = json_decode($paramStr, true);
        if (empty($params)) {
            $ret['code'] = 500;
            $ret['msg'] = '参数错误';
        } else {
            $openId = $params['openId'];
            foreach ($params as $key => $value) {
                if (isset($keyMap[$key])) {
                    $data[$keyMap[$key]] = $value;
                }
            }

            $user = $this->member_model->getUserMemberInfoByOpenId($openId);
            if (empty($user)) {
                $ret['code'] = 500;
                $ret['msg'] = '用户不存在';
            } elseif (!empty($data)) {
                parent::$minor_db->trans_begin();
                $this->updateCommonMember($user['uid'], $data);
                $this->updateMemberProfile($user['uid'], $data);
                $this->upsertWXBind($user['uid'], $data);
                $result = $this->updateUcMember($user['uid'], $data);
                if (parent::$minor_db->trans_status() === FALSE || !$result) {
                    parent::$minor_db->trans_rollback();
                    $ret['code'] = 500;
                    $ret['msg'] = '更新失败';
                } else {
                    parent::$minor_db->trans_commit();
                    //清除用户缓存数据
                    $this->load->library('thrift');
                    $this->thrift->service('Admin')->delCache('common_member', $user['uid']);
                    $this->thrift->service('Admin')->delCache('common_member_profile', $user['uid']);
                    $this->thrift->service('Admin')->delCache('uc_member', $user['uid']);
                    $ret['code'] = 200;
                    $ret['msg'] = '更新成功';
                }
            } else {
                $ret['code'] = 200;
                $ret['msg'] = '更新成功';
            }
        }
        return $ret;
    }

    /**
     * 更新或插入微信绑定数据
     * @param $uid
     * @param $params
     * @return bool
     */
    public function upsertWXBind($uid, $params)
    {
        $data = [];
        $fields = ['union_id', 'open_id'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            if (empty($data['union_id'])) {
                $data['union_id'] = '';
                return TRUE;
            }
            parent::$main_db->update('mp_weixin_user_bind', ['uid' => '', 'open_id' => ''], ['uid' => $uid]);
            $old = parent::$main_db->select('id, union_id')->from('mp_weixin_user_bind')
                ->where(['union_id' => $data['union_id']])->get()->row_array();
            if (!empty($old) && $old['open_id'] !== $data['open_id']) {
                $data['modify_time'] = date('Y-m-d H:i:s');
                $data['uid'] = $uid;
                return parent::$main_db->update('mp_weixin_user_bind', $data, ['id' => $old['id']]);
            } elseif (empty($old) && !empty($data['union_id'])) {
                $data['uid'] = $uid;
//                $realName = empty($params['realname']) ? $params['username'] : $params['realname'];
//                $this->bindWXNotice($uid, $realName);
                return parent::$main_db->insert('mp_weixin_user_bind', $data);
            }
        }
        return TRUE;
    }

    /**
     * 微信绑定通知(暂时移除)
     * @param $uid
     * @param $realName
     */
    protected function bindWXNotice($uid, $realName)
    {
        $this->load->library('thrift');
        try {
            $ret = $this->thrift->service('User')->asend_sendWXNoticeByUid((string)$uid, 2, 25,
                'http://mp.weixin.qq.com/s/7dLoL8NkfL4HrXl0epj9XA', [
                'first' => [
                    'value' => "您好，您已成功绑定早道网校账号，\n课前1-2小时即可获得上课提醒哦~\n",
                    'color' => '#3399ff'
                ],
                'keyword1' => [
                    'value' => $realName,
                    'color' => '#5c5b5b'
                ],
                'keyword2' => [
                    'value' => '绑定成功',
                    'color' => '#5c5b5b'
                ],
                'keyword3' => [
                    'value' => date('Y-m-d H:i:s'),
                    'color' => '#5c5b5b'
                ],
                'remark' => [
                    'value' => "\n如您当前尚未选课，\n请点击这里查看选课流程\n",
                    'color' => '#3399ff'
                ]
            ]);
        } catch (\Exception $e) {

        }
    }

    /**
     * 更新common_member_profile
     * @param $uid
     * @param $params
     * @return bool
     */
    public function updateMemberProfile($uid, $params)
    {
        $data = [];
        $fields = ['realname', 'gender', 'birthyear', 'birthmonth', 'birthday', 'mobile', 'idcard'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                if ($field === 'idcard') {
                    $data['idcardtype'] = '身份证';
                }
                $data[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            return parent::$minor_db->update('jh_common_member_profile', $data, ['uid' => $uid]);
        } else {
            return TRUE;
        }
    }

    /**
     * 更新common_member
     * @param $uid
     * @param $params
     * @return bool
     */
    public function updateCommonMember($uid, $params)
    {
        $data = [];
        $fields = ['email', 'username', 'mobile', 'version', 'avatar'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }

        if (!empty($data)) {
            return parent::$minor_db->update('jh_common_member', $data, ['uid' => $uid]);
        } else {
            return TRUE;
        }
    }

    /**
     * 更新uc库members表
     * @param $uid
     * @param $params
     * @return bool
     */
    public function updateUcMember($uid, $params)
    {
        $data = [];
        $fields = ['email', 'username', 'mobile', 'password', 'salt'];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $data[$field] = $params[$field];
            }
        }
        if (!empty($data)) {
            $ucdb = $this->load->database('uc', TRUE);
            return $ucdb->update('uc_members', $data, ['uid' => $uid]);
        } else {
            return TRUE;
        }

    }

    /**
     * 记录用户注册日志
     * @param $ret
     * @param $paramStr
     * @param int $uid
     */
    public function logRegister($ret, $paramStr, $uid = 0)
    {
        if ($ret['code'] === 200) {
            $msg = '注册用户成功 ';
        } else {
            $msg = '注册用户失败 ';
        }
        $msg .= "{$ret['msg']} uid:$uid $paramStr";
        log_message('error', $msg);
    }
}