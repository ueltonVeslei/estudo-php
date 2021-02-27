<?php
class Router {

	private $_uri;
	protected $_controller			= NULL;
	protected $_method				= NULL;

	public function __construct() {
		$this->_uri = $_SERVER['REQUEST_URI'];
		if (Config::BASEURI == '/') {
			$this->_uri = substr($this->_uri,1);
		} else {
			$this->_uri = str_replace(Config::BASEURI, '', $this->_uri);
		}
		$this->_uri = explode(DS, $this->_uri);
	}

	protected function _getController() {
		if (!$this->_controller) {
			$controller = $this->_uri[0];
			if (!$controller) {
				$controller = 'Erro';
				$this->_method = 'get';
			}

			$controller = str_replace('-',' ',$controller);
			$controller = ucwords($controller);
			$controller = str_replace(' ','',$controller);

			$classController = 'Controller_' . $controller;

			$this->_controller = new $classController();
		}

		return $this->_controller;
	}

	protected function _getMethod() {
		if (!$this->_method) {
			if (!Standard::isAllowed($_SERVER['REQUEST_METHOD'])) {
				$this->_controller = new Controller_Erro();
				$this->_method = 'put';
				$this->_getController()->setMethod($this->_method);
				$this->_getController()->setData('message','Método não permitido');
			} else {
				$this->_method = strtolower($_SERVER['REQUEST_METHOD']);
				$this->_getController()->setMethod($this->_method);
				$this->_loadParams();
			}
		}

		return $this->_method;
	}

	protected function _loadParams() {
		// LOAD PARAMETROS GET
		if (count($this->_uri) > 2) {
			$i = 1;
			while ($i < count($this->_uri)) {
				$this->_getController()->setData($this->_uri[$i],$this->_uri[$i+1]);
				$i += 2;
			}
		}

		// LOAD PARAMETROS POST
		if (isset($_POST)) {
			$this->_getController()->setData('post',$_POST);
		}

		// LOAD BODY
		$body = file_get_contents('php://input');
		if ($body) {
			$this->_getController()->setData('body',json_decode($body));
		}
	}

	protected function _authentication() {
		$headers = getallheaders();
		if (!isset($headers['User-Email']) && !isset($headers['Api-Token'])) {
			$preflight = explode(',',$headers['Access-Control-Request-Headers']);
			$token = '';
			foreach ($preflight as $item) {
				if (!in_array($item,['api-token','content-type','user-email'])) {
					$token = $item;
					break;
				}
			}
			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'
				&& $token == base64_encode(Config::USEREMAIL . ':' . Config::TOKEN)) { // is preflight
				$this->_controller = new Controller_Preflight();
				$this->_method = 'options';
				$this->_getController()->setMethod($this->_method);
			}
		} elseif (!($headers['User-Email'] == Config::USEREMAIL && $headers['Api-Token'] == Config::TOKEN)) {
			$this->_controller = new Controller_Erro();
			$this->_method = 'get';
			$this->_getController()->setMethod($this->_method);
			$this->_getController()->setData('message','Credenciais inválidas');
		}
	}

	protected function _sendHeaders($status) {
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			header("Access-Control-Max-Age: 604800");
			header("Connection: Keep-Alive");
			header("Cache-Control: max-age=604800");
			header("Pragma: cache");
		} else if ($_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header('Access-Control-Allow-Headers: api-token,content-type,user-email,' . base64_encode(Config::USEREMAIL . ':' . Config::TOKEN));
		header('Access-Control-Allow-Credentials: true');
        header("Content-Type: application/json");
        header("HTTP/1.1 " . Standard::getStatus($status));
	}

	public function response(){
		$this->_authentication();
		$this->_getMethod();
		$response = $this->_getController()->execute();
		$this->_sendHeaders($response['status']);
		echo json_encode($response['data']);
	}

}