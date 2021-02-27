<?php
class User extends Db {
    //Os atributos devem ficar em ordem para seram inseridos no banco corretamente
    //(Não é necessário o nome do atributo ser igual ao do banco)
    protected $_tableName = 'User';

    public function __construct(){
        parent::__construct();
    }
    
    //grava registro requisição no banco de dados
    public function create($data = []){
        
        //cadastra usuario na API
        $sql = 'INSERT INTO User(id_user, active) VALUES(:id_user, :active)';
        $stmt = $this->_conn->prepare($sql);
        $stmt->bindValue (':id_user', $data['customer']['entity_id']);
        $stmt->bindValue (':active', $data['customer']['is_active']);
        $stmt->execute();
    }

    public function authenticate($request){
        $customer = new Model_Customer();

        //Verifica se os dados de autenticação do usuario é valido
        $cust = $customer->authenticate($request);

        //caso for retornará um array com os dados e entrará no if
        if(is_array($cust)){
            $select = $this->_conn->select()->from(
                ['user' => 'User']
            )->where('id_user = ?', $cust['customer']['entity_id']);
            
            $res = $this->_conn->fetchAll($select);
            //Verifica se o cliente esta cadastrado na api
            if(count($res) == 0 && is_numeric($cust['customer']['entity_id'])){
                $this->create($cust);
            }
            return $cust;
        }
        return null;
    }
    public function isBlocked($ip){

        $select = $this->_conn->select()->from(
            ['blocked' => 'Blocked']
        )->where('ip = ?', $ip);
        $res = $this->_conn->fetchAll($select);
        if(count($res) > 0)
            return true;
        return false;
    }

    public function addUserNotExists($id, $status){
        $select = $this->_conn->select()->from(
            ['user' => 'User']
        )->where('id_user = ?', $id);
        
        $res = $this->_conn->fetchAll($select);
        //Verifica se o cliente esta cadastrado na api
        if(count($res) == 0){
            $this->create(['entity_id'=> $id, 'is_active' => $status]);
        }
    }
}

 