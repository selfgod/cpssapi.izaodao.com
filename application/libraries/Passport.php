<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Passport
{
    protected $CI;
    protected $initialized = FALSE;
    protected $info;
    protected $uid;
    protected $sessionId;
    protected $_ttl = 86400;
    protected $_prefix = 'map_openId_';
    /**
     * 默认session过期时间, 3600s
     * @var int
     */
    protected $_defaultSessionExpire = 3600;
    /**
     * 用户中心配置的session过期时间
     * @var int
     */
    protected $_sessionExpire = 0;
    /**
     * Default config
     *
     * @static
     * @var	array
     */
    protected static $_default_config = array(
        'socket_type' => 'tcp',
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => 6379,
        'timeout' => 0
    );
    /**
     * Redis connection
     *
     * @var	Redis
     */
    protected $_redis;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('public/member_model');
    }

    /**
     * 初始化
     * @param string $sessionId
     * @return bool
     */
    public function init($sessionId = '')
    {
        if (!$this->initialized) {
            $this->initRedis();
            $this->setCookieOrigin();
            $cookieKey = get_options('passport_cookie_name');
            if (empty($sessionId)) {
                $sessionId = $this->CI->input->cookie($cookieKey);
            }
            if (empty($sessionId)) {
                return FALSE;
            }
            $this->CI->load->driver('cache');
            $userInfo = $this->getRedisUserInfo($sessionId);
            if (empty($userInfo)) {
                return FALSE;
            }
            $this->refreshExpire($sessionId);
            $userInfo['pwd_token'] = isset($userInfo['password']) ? $userInfo['password'] : '';
            $this->info = $userInfo;
            $this->uid = $userInfo['uid'];
            $this->sessionId = $sessionId;
            $this->initialized = TRUE;
        }
        return TRUE;
    }

    /**
     * 更新session过期时间
     * @param $sessionId
     */
    protected function refreshExpire($sessionId)
    {
        $key = get_options('passport_key_prefix') . $sessionId;
        $expire = $this->getExpire();
        $this->_redis->expire($key, $expire);
        $this->CI->input->set_cookie([
            'name' => get_options('passport_cookie_name'),
            'value' => $sessionId,
            'expire' => $expire
        ]);
    }

    /**
     * 获取session过期时间
     * @return int
     */
    protected function getExpire()
    {
        if (empty($this->_sessionExpire)) {
            $expire = $this->_redis->get('global_session_expire');
            if ($expire !== FALSE) {
                $this->_sessionExpire = intval($expire);
            } else {
                $this->_sessionExpire = $this->_defaultSessionExpire;
            }
        }
        return $this->_sessionExpire;
    }

    /**
     * 将APPID设置到cookie中，用户中心通过他识别来源
     */
    protected function setCookieOrigin()
    {
        $expire = $this->getExpire();
        setcookie('ZDAPPID', PASSPORT_APPID, time() + $expire, '/', '.izaodao.com');
    }

    /**
     * 初始化Redis
     */
    protected function initRedis()
    {
        $this->_redis = new Redis();
        try {
            $success = $this->_redis->connect(PASSPORT_REDIS_HOST, PASSPORT_REDIS_PORT, 5);
            if (!$success) {
                log_message('error', 'Cache: Redis connection failed. Check your configuration.');
            }
            if (!empty(PASSPORT_REDIS_PWD) && !$this->_redis->auth(PASSPORT_REDIS_PWD)) {
                log_message('error', 'Cache: Redis authentication failed.');
            }
        } catch (RedisException $e) {
            log_message('error', 'Cache: Redis connection refused (' . $e->getMessage() . ')');
        }
    }

    /**
     * 退出
     * @return bool
     */
    public function logout()
    {
        return $this->delRedisUserInfo($this->sessionId);
    }

    /**
     * 删除用户中心里存储的用户信息
     * @param $key
     * @return bool
     */
    protected function delRedisUserInfo($key)
    {
        $key = get_options('passport_key_prefix') . $key;
        if ($this->_redis->delete($key) !== 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 获取用户中心里存储的用户信息
     * @param $sessionId
     * @return array|mixed
     */
    protected function getRedisUserInfo($sessionId)
    {
        $key = get_options('passport_key_prefix') . $sessionId;
        $baseInfo = $this->_redis->get($key);
        if ($baseInfo !== FALSE) {
            $baseInfo = json_decode($baseInfo, TRUE);
            if (empty($baseInfo) || !isset($baseInfo['base_user'])) {
                return [];
            }
            //重试3次，避免注册后取不到用户信息
            for ($i = 0; $i < 3; $i++) {
                $uid = $this->getUidByOpenId($baseInfo['base_user']['openId']);
                if (empty($uid)) {
                    if ($i === 2) {
                        return [];
                    }
                    sleep(3);
                } else {
                    break;
                }
            }

            $baseInfo['base_user']['uid'] = $uid;
            $baseInfo['base_user']['username'] = $baseInfo['base_user']['userName'];
            return $baseInfo['base_user'];
        } else {
            return [];
        }
    }

    /**
     * 通过openId获取uid
     * @param $openId
     * @return int
     */
    protected function getUidByOpenId($openId)
    {
        $uid = $this->CI->cache->redis->get($this->_prefix . $openId);
        if ($uid !== FALSE) {
            return $uid;
        }
        $user = $this->CI->member_model->getUserMemberInfoByOpenId($openId);
        if (empty($user)) {
            return 0;
        } else {
            $this->CI->cache->redis->save($this->_prefix . $openId, $user['uid'], $this->_ttl);
            return $user['uid'];
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function get($key = '')
    {
        $info = $this->info;
        if (empty($key)) {
            return $info;
        } elseif (isset($info[$key])) {
            return $info[$key];
        } else {
            return FALSE;
        }
    }

    /**
     * @return mixed
     */
    public function sessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 关闭redis连接
     */
    public function __destruct()
    {
        if ($this->_redis) {
            $this->_redis->close();
        }
    }
}