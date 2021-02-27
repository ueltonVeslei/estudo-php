<?php
class Controller {

	protected $_method				= '';	// Nome do método
	protected $_data				= NULL; // Dados gerais do Controller
	protected $_response			= NULL; // Resposta de dados

	public function __construct() {
		$this->_data = array();
	}

	public function setMethod($name) {
		$this->_method = $name;
	}

	protected function _callMethod() {
		$this->{'_' . $this->_method}();
		return $this->_response;
	}

	protected function _get() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','Método GET',true);
	}

	protected function _options() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','Método OPTIONS',true);
	}

	protected function _post() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','Método POST',true);
	}

	protected function _put() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','Método PUT',true);
	}

	protected function _delete() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','Método DELETE',false);
	}

	protected function setResponse($field, $value, $encode=false) {
		if ($encode) {
			$value = json_encode($value, JSON_PRETTY_PRINT);
		}
		$this->_response[$field] = $value;
	}

	public function execute() {
        return $this->_callMethod();
	}

	public function setData($field, $value) {
		$this->_data[$field] = $value;
	}

	public function getData($field) {
		if (isset($this->_data[$field])) {
			return $this->_data[$field];
		}

		return false;
	}

}

