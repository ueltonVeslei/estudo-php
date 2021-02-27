<?php
class Db {

	protected $_conn 		= NULL;
	protected $_tableName	= NULL;
	protected $_insertId	= NULL;
	protected $_map			= NULL;
	protected $_primaryKey	= NULL;

	/**
	** Construtor da Classe
	**/
	public function __construct() {
		$this->map();
	}

	/**
	** Mapeamento de colunas da tabela
	**/
	protected function map(){
		$sql = "select * from information_schema.columns where table_schema = '".Config::DB_NAME."' and table_name = '".$this->_tableName."'";
		$fields = $this->fetchCustomQuery($sql);
		foreach ($fields as $field) {
			$this->_map[] = $field->COLUMN_NAME;
			if ($field->COLUMN_KEY == 'PRI') {
				$this->_primaryKey[] = $field->COLUMN_NAME;
			}
		}
	}

	/**
	**
	**/
	protected function _keyWhere($key) {
		$where = NULL;
		if (is_array($key)) {
			$where = array();
			foreach ($key as $field => $value) {
				$where[] = $field . " = '" . $value . "'";
			}
			$where = implode(' AND ',$where);
		} else {
			$where = $this->_primaryKey[0] . " = '" . $key . "'";
		}

		return $where;
	}

	/**
	** Abre conexão com o banco de dados
	**/
	protected function getConnection() {
		if (!$this->_conn) {
			$this->_conn = mysqli_connect(Config::DB_HOST,Config::DB_USER,Config::DB_PASS,Config::DB_NAME);
			if (!$this->_conn) {
				Session::setData('message',(object)array('text' => mysqli_error(), 'type' => 'error'));
				App::redirect('erro');
			}
		}

		return $this->_conn;
	}

	/**
	** Fecha conexão com o banco de dados
	**/
	protected function closeConnection() {
		mysqli_close($this->getConnection());
		$this->_conn = NULL;
	}

	/**
	** TRATA A SENTENÇA WHERE E RETORNA
	**/
	protected function _prepareWhere($where) {
		$result = '';
		if($where) {
			$result =  ' WHERE ' . $where;
		}

		return $result;
	}

	/**
	** PREPARA OS DADOS PARA INSERT/UPDATE
	**/
	protected function _prepareData($params) {
		$fields = array();
		foreach ($params as $column => $value) {
			if ($value) {
				$fields[] = $column . " = '" . $value . "'";
			}
		}
		return implode(',', $fields);
	}

	/**
	** PREPARA A SENTENÇA ORDER
	**/
	protected function _prepareOrder($order) {
		$result = '';
		if ($order) {
			$result = 'ORDER BY ' . $order;
		}
		return $result;
	}

	/**
	** Recupera um array de registros do banco de dados
	**/
	public function fetchAll($where=null,$order=null) {
		$sql = 'SELECT * FROM ' . $this->_tableName . $this->_prepareWhere($where) . $this->_prepareOrder($order);
		$query = mysqli_query($this->getConnection(), $sql);
		$result = array();
		while ($res = mysqli_fetch_assoc($query)) {
			$result[] = $res;
		}
		$this->closeConnection();
		return $result;
	}

	/**
	** Recupera um único registro do banco de dados
	**/
	public function fetchRow($where=null) {
		$sql = 'SELECT * FROM ' . $this->_tableName . $this->_prepareWhere($where) . ' LIMIT 1';
		$query = mysqli_query($this->getConnection(), $sql);
		$result = mysqli_fetch_object($query);
		$this->closeConnection();
		return $result;
	}

	/**
	** Insere um novo registro no banco de dados
	**/
	public function insert($params) {
		$sql = 'INSERT INTO ' . $this->_tableName . ' SET ' . $this->_prepareData($params);
		$query = mysqli_query($this->getConnection(), $sql);
		$this->_insertId = $this->getConnection()->insert_id;
		$this->closeConnection();
	}

	/**
	** Atualiza um registro no banco de dados
	**/
	public function update($params, $where) {
		$sql = 'UPDATE ' . $this->_tableName . ' SET ' . $this->_prepareData($params) . $this->_prepareWhere($where);
		$query = mysqli_query($this->getConnection(), $sql);
		$this->closeConnection();
	}

	/**
	** Exclui um registro do banco de dados
	**/
	public function delete($where=NULL) {
		$sql = 'DELETE FROM ' . $this->_tableName . $this->_prepareWhere($where);
		$query = mysqli_query($this->getConnection(), $sql);
		$this->_insertId = $this->getConnection()->insert_id;
		$this->closeConnection();
	}

	/**
	** Recupera o último ID inserido na tabela
	**/
	public function getLastInsertId() {
		return $this->_insertId;
	}

	/**
	** Executa uma query personalizada
	**/
	public function fetchCustomQuery($sql) {
		$query = mysqli_query($this->getConnection(), $sql);
		$result = array();
		while ($res = mysqli_fetch_object($query)) {
			$result[] = $res;
		}
		$this->closeConnection();
		return $result;
	}

	/**
	** Verifica se um registro existe
	**/
	public function exists($key) {
		$result = $this->fetchRow($this->_keyWhere($key));

		if ($result) {
			return true;
		}

		return false;
	}

	/**
	** Salva um registro no banco de dados
	**/
	public function save($key, $data) {
		$params = array();
		foreach ( $this->_map as $column){
			if(isset($data[$column])){
				$params[$column] = $data[$column];
			}
		}

		if (in_array('updated_at',$this->_map)) {
			$params['updated_at'] = date('Y-m-d H:i:s');
		}

		if ($this->exists($key)) {
			$this->update($params, $this->_keyWhere($key));
		} else {
			if (in_array('created_at',$this->_map)) {
				$params['created_at'] = date('Y-m-d H:i:s');
			}
			$this->insert($params);
		}
	}

}