<?php
// in case this is called like standalone script - we need wordpress functions available
if ( ! function_exists( 'get_option' ) ) {
	// absolute path to wp installation root
	$wordpress_path = realpath ( dirname ( __FILE__ ) . '/../../../../' );
	require_once $wordpress_path . '/wp-load.php';
}

// init SweetCaptcha instance
$sweetcaptcha_instance = new Sweetcaptcha(
	get_option( 'sweetcaptcha_app_id' ), 
	get_option( 'sweetcaptcha_key' ), 
	get_option( 'sweetcaptcha_secret' ), 
	//get_option( 'sweetcaptcha_public_url' )
	SWEETCAPTCHA_PHP_PATH
);

/*
 * Do not change below here.
 */

/**
 * Handles remote negotiation with sweetCaptcha.com
 *
 * @version 1.1
 * @updated November 12, 2013
 * 
 */

if (isset($_POST['ajax']) and $method = $_POST['ajax']) {
	echo $sweetcaptcha_instance->$method(isset($_POST['params']) ? $_POST['params'] : array());
}

class Sweetcaptcha {
	
	private $appid;
	private $key;
	private $secret;
	private $path;
	private $registered;
	
	const API_URL = SWEETCAPTCHA_SITE_URL; 
	
	function __construct($appid, $key, $secret, $path) {
		$this->appid = $appid;
		$this->key = $key;
		$this->secret = $secret;
		$this->path = $path;
		$this->registered = ( !empty($appid) && !empty($key) && !empty($secret) );
	}
	
	private function api($method, $params) {
		$basic = array(
			'method' => $method,
			'appid' => $this->appid,
			'key' => $this->key,
			'path' => $this->path,
			'user_ip' => $_SERVER['REMOTE_ADDR'],
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'platform' => 'wordpress'
		);
		
		if (is_admin()) {
			return $this->call(array_merge(isset($params[0]) ? $params[0] : $params, $basic));
		} else {
			if ( $this->registered ) {
				return $this->call(array_merge(isset($params[0]) ? $params[0] : $params, $basic));
			} else {
				//return '<span style="color: red;">'.__('Your sweetCaptcha plugin is not setup yet', 'sweetcaptcha').'</span>';
				return '';
			}
		}
	}
	
	private function call($params) {
		$param_data = "";		
		foreach ($params as $param_name => $param_value) {
			$param_data .= urlencode($param_name) .'='. urlencode($param_value) .'&'; 
		}

		$fs = fsockopen( self::API_URL, 80, $errno, $errstr, 10 /* The connection timeout, in seconds */ );
		if ( ! $fs ) {
			if ( isset($params['check']) ) {
				return '<div class="error sweetcaptcha" style="text-align: left; ">'.$this->call_error($errstr, $errno).'</div>';
			}
			return ''; //$this->call_error($errstr, $errno);
    } else
		if ( isset($params['check']) ) {
			return '';
		}
    
		$req = "POST /api.php HTTP/1.0\r\n";
		$req .= "Host: ".self::API_URL."\r\n";
		$req .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$req .= "Referer: " . $_SERVER['HTTP_HOST']. "\r\n";
		$req .= "Content-Length: " . strlen($param_data) . "\r\n\r\n";
		$req .= $param_data;		
	
		$response = '';
		fwrite($fs, $req);
		while ( !feof($fs) ) {
			$response .= fgets($fs, 1160);
		}
		fclose($fs);
	
		$response_arr = explode("\r\n\r\n", $response, 2);
		return $response_arr[1];	
	}

	private function call_error($errstr, $errno) {
		return "<p style='color:red;'>".SWEETCAPTCHA_CONNECT_ERROR."</p><a style='text-decoration:underline;' href='javascript:void(0)' onclick='javascript:jQuery(\"#sweetcaptcha-error-details\").toggle();'>Details</a><span id='sweetcaptcha-error-details' style='display: none;'><br>$errstr ($errno)</span>";
	}

	public function __call($method, $params) {
		return $this->api($method, $params);
	}
	
	public function check_access() {
		echo $this->api('get_html', array('check'=>1));
	}
}
?>