<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');


/**
 *  Ucenter接口通知处理控制器
 *
 *  本类根据ucenter提供的通知处理实例代码编写，具体处理部分需要根据不同应用的逻辑自行编写处理逻辑。
 *  具体请仔细阅读ucenter自带的手册。
 *
 */

class Uc extends CI_Controller {
	const UC_CLIENT_RELEASE = '20141101';
	const UC_CLIENT_VERSION = '1.6.0';

	const API_DELETEUSER = 1;
	const API_RENAMEUSER = 1;
	const API_GETTAG = 1;
	const API_SYNLOGIN = 1;
	const API_SYNLOGOUT = 1;
	const API_UPDATEPW = 1;
	const API_UPDATEBADWORDS = 1;
	const API_UPDATEHOSTS = 1;
	const API_UPDATEAPPS = 1;
	const API_UPDATECLIENT = 1;
	const API_UPDATECREDIT = 1;
	const API_GETCREDITSETTINGS = 1;
	const API_GETCREDIT = 1;
	const API_UPDATECREDITSETTINGS = 1;

	const API_RETURN_SUCCEED = 1;
	const API_RETURN_FAILED = -1;
	const API_RETURN_FORBIDDEN = -2;

	public function index() {
		include APPPATH . 'config/ucenter.php';
		$get = $post = array();
		$code = $this -> input -> get('code', true);
		parse_str(self::authcode($code, 'DECODE', UC_KEY), $get);
		$timestamp = time();
		if ($timestamp - $get['time'] > 3600) {
			echo '授权已过期';
			return;
		}
		if (empty($get)) {
			echo '非法请求';
			return;
		}
		$post = self::unserialize(file_get_contents('php://input'));
		if (in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {

			echo $this -> $get['action']($get, $post);
			return;
		} else {
			echo self::API_RETURN_FAILED;
			return;
		}

	}

	public function logout()
    {
        include APPPATH . 'config/ucenter.php';
        require_once FCPATH . 'uc_client/client.php';
//        $this->load->library('session');
//        $this->session->sess_destroy();
//        session_write_close();
        $js = uc_user_synlogout();
        echo json_encode(['code' => 200, 'status' => $js]);
    }

    private function test($get, $post) {
		return self::API_RETURN_SUCCEED;
	}

	private function deleteuser($get, $post) {
		if (!self::API_DELETEUSER) {
			return self::API_RETURN_FORBIDDEN;
		}
		$uids = $get['ids'];
		//delete your users here
		return self::API_RETURN_SUCCEED;
	}

	private function gettag($get, $post) {
		if (!self::API_GETTAG) {
			return self::API_RETURN_FORBIDDEN;
		}
		//
		return self::API_RETURN_SUCCEED;
	}

	private function synlogin($get, $post) {
		if (!self::API_SYNLOGIN) {
			return self::API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = $get['uid'];
		//同步登录的代码在这里处理
		include APPPATH . '../uc_client/client.php';
		if ($uc_user = uc_get_user($uid, 1)) {
//			$userinfo = array(
//					'uid'		=>	$uc_user[0],
//					'username'	=>	$uc_user[1],
//					'email'		=>	$uc_user[2]
//				);

//			$this->load->model('public/member_model');
//            $userinfo['pwd_token'] = $this->member_model->getUserPwd($uc_user[0]);
//            $this->load->library('session');
//			$this->session->set_userdata('user', $userinfo);
//			session_write_close();
		}
		return self::API_RETURN_SUCCEED;
	}

	private function synlogout($get, $post) {
		if (!self::API_SYNLOGOUT) {
			return self::API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
//        $this->load->library('session');
//		$this->session ->sess_destroy();
//        session_write_close();
		return self::API_RETURN_SUCCEED;
	}

	private function updatepw($get, $post) {
		if (!self::API_UPDATEPW) {
			return self::API_RETURN_FORBIDDEN;
		}
		//这里做修改密码操作
		return self::API_RETURN_SUCCEED;
	}

	private function updatebadwords($get, $post) {
		if (!self::API_UPDATEBADWORDS) {
			return self::API_RETURN_FORBIDDEN;
		}
		$cachefile = APPPATH . '../uc_client/data/cache/badwords.php';
		@unlink($cachefile);
		return self::API_RETURN_SUCCEED;
	}

	private function updatehosts($get, $post) {
		if (!self::API_UPDATEHOSTS) {
			return self::API_RETURN_FORBIDDEN;
		}
		$cachefile = APPPATH . '../uc_client/data/cache/hosts.php';
		@unlink($cachefile);
		return self::API_RETURN_SUCCEED;
	}

	private function updateapps($get, $post) {
		if (!self::API_UPDATEAPPS) {
			return self::API_RETURN_FORBIDDEN;
		}
		$cachefile = APPPATH . '../uc_client/data/cache/apps.php';
		@unlink($cachefile);
		return self::API_RETURN_SUCCEED;
	}

	private function updateclient($get, $post) {
		if (!self::API_UPDATECLIENT) {
			return self::API_RETURN_FORBIDDEN;
		}
		$cachefile = APPPATH . '../uc_client/data/cache/settings.php';
		@unlink($cachefile);
		return self::API_RETURN_SUCCEED;
	}

	private function updatecredit($get, $post) {
		if (!self::API_UPDATECREDIT) {
			return self::API_RETURN_FORBIDDEN;
		}
		return self::API_RETURN_SUCCEED;
	}

	private function getcredit($get, $post) {
		if (!self::API_GETCREDIT) {
			return self::API_RETURN_FORBIDDEN;
		}
		return self::API_RETURN_SUCCEED;
	}

	public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key ? $key : UC_KEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

		$cryptkey = $keya . md5($keya . $keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if ($operation == 'DECODE') {
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace('=', '', base64_encode($result));
		}
	}

	public static function serialize($arr, $htmlOn = 0) {
		if (!function_exists('xml_serialize')) {
			require APPPATH . '../uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlOn);
	}

	public static function unserialize($xml, $htmlOn = 0) {
		if (!function_exists('xml_serialize')) {
			require APPPATH . '../uc_client/lib/xml.class.php';
		}
		return xml_unserialize($xml, $htmlOn);
	}

	public static function gbk2utf8($string) {
		return iconv("GB2312", "UTF-8//IGNORE", $string);
	}

	public static function utf82gbk($string) {
		return iconv("UTF-8", "GB2312//IGNORE", $string);
	}

}
