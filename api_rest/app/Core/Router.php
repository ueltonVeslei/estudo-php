<?php
class Router {

    private $_uri;
    protected $_controller			= NULL;
    protected $_method				= NULL;
    protected $_headers             = NULL;

    public function __construct() {
        $this->_uri = $_SERVER['REQUEST_URI'];
        if (Config::BASEURI == '/') {
            $this->_uri = substr($this->_uri,1);
        } else {
            $this->_uri = str_replace(Config::BASEURI, '', $this->_uri);
        }
        $this->_uri = explode('/', $this->_uri);
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
                $this->_getController()->setData('message','MÃ©todo nÃ£o permitido');
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

    protected function _getHeaders() {
        if (!$this->_headers) {
            if (!function_exists('getallheaders')) {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                $this->_headers = $headers;
            } else {
                $this->_headers = getallheaders();
            }
        }

        return $this->_headers;
    }

    protected function _authentication() {
        $headers = $this->_getHeaders();
        $allowed = true;

        $origin = parse_url($_SERVER['HTTP_ORIGIN']);
        if (isset($origin['host'])) {
            if (!in_array($origin['host'], Config::$allowed_domains)) {
                $allowed = false;
            }
        } else {
            $allowed = false;
        }

        if (!$allowed) {
            if (array_key_exists('Pwa-Local-Key', $headers)) {
                if ($headers['Pwa-Local-Key'] == 'mWxh23QwcXa1R1MemmU3nhfL7kMnnFrh') {
                    $allowed = true;
                }
            }
        }

        if (!isset($_SERVER['HTTP_REFERER']) || !isset($_SERVER['HTTP_USER_AGENT'])) {
            $allowed = false;
        }

        if (!$allowed) {
            $this->_controller = new Controller_Erro();
            $this->_method = 'get';
            $this->_getController()->setMethod($this->_method);
            $this->_getController()->setData('message','Credenciais invÃ¡lidas');
            return false;
        }

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
                && $token == Config::HEADER_TOKEN) { // is preflight
                $this->_controller = new Controller_Preflight();
                $this->_method = 'options';
                $this->_getController()->setMethod($this->_method);
            }
        } elseif (!($headers['User-Email'] == Config::USEREMAIL && $headers['Api-Token'] == Config::TOKEN)) {
            $this->_controller = new Controller_Erro();
            $this->_method = 'get';
            $this->_getController()->setMethod($this->_method);
            $this->_getController()->setData('message','Credenciais invÃ¡lidas');
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
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH, PUT");
        header('Access-Control-Allow-Headers: api-token,content-type,user-email,' . Config::HEADER_TOKEN);
        header('Access-Control-Allow-Credentials: true');
        header("Content-Type: application/json");
        header("HTTP/1.1 " . Standard::getStatus($status));
    }

    protected function _setStore() {
        $headers = $this->_getHeaders();

        $storeCode = 'default';
        if (isset($headers['Storecode'])) {
            $storeCode = $headers['Storecode'];
        }

        Mage::app()->setCurrentStore($storeCode);
    }

    public function response(){
        $this->_authentication();
        $this->_setStore();
        $this->_getMethod();
        $response = $this->_getController()->execute();
        $this->_sendHeaders($response['status']);
        echo json_encode($response['data']);
    }

}