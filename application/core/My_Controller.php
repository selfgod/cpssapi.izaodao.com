<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 控制器基类
 *
 */		
abstract class My_Controller extends CI_Controller
{
	public $_uid = null;
	public $_login = array();

    /**
     * 构造函数
     *
     * @access  public
     */
	public function __construct()
	{
		parent::__construct();

        if ($this->passport->init()) {
            $this->_uid = $this->passport->getUid();
            $this->_login = $this->passport->get();
            return;
        }
        //用户未登录或过期
        $class = $this->router->fetch_class();
        $method = $this->router->fetch_method();
        if (!($class === 'user' && $method === 'baseInfo')) {
            $redirect = '//' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
            $this->input->set_cookie([
                'name' => 'referrer_url',
                'value' => $redirect,
                'expire' => 300
            ]);
            $autoLoginPage = MASTER_DOMAIN . 'main.php/User/Login/auto_login';
        } else {
            $autoLoginPage = BASE_URL;
        }

        $login_url = PASSPORT_DOMAIN . 'login.do?redirect=' . $autoLoginPage;
        $isjsonp = $this->input->get('callback');
        if ($this->input->is_ajax_request()) {
            $this->response->formatJson(401, ['url' => $login_url], '用户未登录');
        } elseif ($isjsonp) {
            $this->response->formatJsonp(401, ['url' => $login_url], '用户未登录');
        } else {
            redirect($login_url);
        }
	}

	/**
	 * logged
	 */
	function _logged(){
	    return !empty($this->_uid);
	}
}
