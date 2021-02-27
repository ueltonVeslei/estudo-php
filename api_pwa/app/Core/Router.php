<?php
class Router {
	private $_uri;
	protected $_controller			= NULL;
	protected $_method				= NULL;
	protected $nameController       = NULL;
	/**
	 * Tipo do login do usuário 
	 * 1 -> Autenticado por login, senha e outros campos para gerar o token no banco
	 * 2 -> Autenticado como usuário visitante e outros campos para gerar o token no banco
	 * */
	protected $_typeUser            = NULL;
	/**
	 * Aqui será um array com a lista de controller que cada um terá acesso
	 * Como haverá apenas 2 tipos de usuario será um array com 2 posições
	 * OBS: Caso algum tipo de usuário tiver permissão total em todas controllers
	 *        ele terá um valor ['FULL'] 
	 */
	protected $_controllersAcessUser= NULL;
	/**
	 * 
	 */
	protected $_methodsControllerAcess = NULL;


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
		//Se não existe a instância de controller
		if (!$this->_controller) {

			$this->nameController = $this->formatNameController($this->_uri[0]);

			$this->_controller = new $this->nameController;
		}
		//Se existe a instância seta o name
		else 
			$this->nameController = $this->formatNameController($this->_uri[0]);

		return $this->_controller;
	}

	private function formatNameController($controller){
		if (!$controller) {
			$controller = 'Erro';
			$this->_method = 'get';
		}

		$controller = str_replace('-',' ',$controller);
		$controller = ucwords($controller);
		$controller = str_replace(' ','',$controller);

		$classController = 'Controller_' . $controller;
		return $classController;
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

	//Carrega os dados
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

		$header = $this->_getallheaders();

		$this->_getController()->setData('token',$header['Token']);

		//LOAD BODY
		$body = $this->getBody();
		$this->_getController()->setData('body',$body);
	}

	private function getBody($headers=null){
		// return body requisition
		$body = file_get_contents('php://input');
		if ($body)
			$body = (object) json_decode($body);
		else
			$body = (object) '';
			//$body = json_decode(Criptography::encrypt_decrypt('decrypt', $body));
		if ($headers) {
			$body->ip = $_SERVER['REMOTE_ADDR'];
			$body->user_agent = $headers['User-Agent'];
			if(isset($headers['Origin']))
				$body->dns_name = $headers['Origin'];
			else
				$body->dns_name = '';
		}
		return $body;
	}

	protected function _getallheaders() { 
        $headers = []; 
       foreach ($_SERVER as $name => $value) { 
           if (substr($name, 0, 5) == 'HTTP_') { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
	} 
	
	protected function _authentication($header, $data){
		//Cria um token para ter acesso a  api
		$controller = $this->_uri[0];
		$token = new Token();
		$res = array();
		//Cria o token
		if(isset($header['Token']) && $header['Token'] == 0 && strtolower($controller) == 'login'){
			$res = $this->_createAuthorization($data, $token);
		}
		//Verifica se o Token é valido 
		else if(!isset($header['Token']) || !$token->validToken($header['Token']))
		{
			//Se o token for inválido
			$res[] = 'Credenciais inválidas';
		}
		//Seta o token de segurança da api gerado e prossegue na aplicação
		else{
			$res['token'] = $header['Token'];
		}
		return $res;
	}
	private function _createAuthorization($data, $token){
		//Verifica se existe email e senha ai ele será logado como cliente
		$res = array();
		if(isset($data->email) && isset($data->password)){
			//Aqui chama a função de criar usuario na api e cria um token 
			$user = new User();
			//echo json_encode($data);
			$res = $user->authenticate($data);
			//echo json_encode($res);
		}
		$idUser = NULL;
		if(count($res) > 0 && $res['customer']['entity_id'] >= 0) 
			$idUser = $res['customer']['entity_id'];
		//Cria o token
		$token->createToken($data, $idUser);
		$res['token'] = $token->token;
		
		//echo json_encode($res);
		return $res;
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
		header('Access-Control-Allow-Credentials: true');
        header("Content-Type: application/json");
        header("HTTP/1.1 " . Standard::getStatus($status));
	}
	//Primeiro contato da API 
	public function response(){
		//Obtem todos os cabeçalhos da requisição
		$header = $this->_getallheaders();

		$body = $this->getBody($header);
		$user = new User();
		//Verifica se o IP do usuário não foi blockeado 
		if(!$user->isBlocked($body->ip))
		{
			//Faz a autenticação
			$res = $this->_authentication($header, $body);
			//Instancia o controller
			$this->_getController();
			$token = new Token();
			
			$idUser = null;
			if(isset($res['token'])) {
				$idUser = $token->getIdUser($res['token']);
			}
			//Caso esta criando o token
			if(isset($header['Token']) && $header['Token'] == '0'){
				$response['status'] = Standard::STATUS200;
				$response['data'] = $res;
				//echo json_encode($res);
			}
			//Caso estiver querendo acessar algum metódo com um token
			else if(AcessRestriction::isAccessMethodController(AcessRestriction::getTypeUserAcess($idUser), $this->nameController, '_'.$this->_getMethod()))
			{
				//Obtem o metodo
				$this->_getMethod();
				//Obtem a controler e executa
				$response = $this->_controller->execute($header['Token'], $this->nameController, $idUser);
			}
			//Caso não entrar em nenhuma das verificações 
			else{
				$response['status'] = Standard::STATUS403;
				$response['data'] = 'Você não tem permissão para acessar esse método';
			}
		}
		//Se foi blockeado
		else{
			$response['status'] =  Standard::STATUS403;
			$response['data'] = 'Seu IP foi blockeado por favor entre em contanto com o 
			administrador do site';
		}
		//Seta status de resposta da API
		$this->_sendHeaders($response['status']);
		//Autentica e seta a resposta da requisição		
		//echo Criptography::encrypt_decrypt('encrypt',json_encode($response['data']));
		echo json_encode($response['data']);
	}


}
