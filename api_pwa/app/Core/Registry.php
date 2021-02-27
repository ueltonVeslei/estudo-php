<?php
class Registry extends Db {
    //Os atributos devem ficar em ordem para seram inseridos no banco corretamente
    //(Não é necessário o nome do atributo ser igual ao do banco)
    protected $_tableName = 'Registry';

    public function __construct(){
        parent::__construct();
    }

    //grava registro requisição no banco de dados
    public function create($data = []){
        if($this->verifyRegistryDOS($data['token'])){
            $sql = 'INSERT INTO Registry(id_user, token, date_time_now, method_controller, data_json) VALUES(:id_user, :token, :date_time_now, :method_controller, :data_json)';
            $stmt = $this->_conn->prepare($sql);
            $stmt->bindValue (':id_user', $data['id_user']);
            $stmt->bindValue (':token', $data['token']);
            $stmt->bindValue (':date_time_now', $data['date_time_now']);
            $stmt->bindValue (':method_controller', $data['method_controller']);
            $stmt->bindValue (':data_json', $data['data_json']);
            $stmt->execute();
            return true;
        }
        return false;
    }


    public function verifyRegistryDOS($tokenHash){
        $select = $this->_conn->select()->from(
            ['registry' => 'Registry']
        )->where('token = ?', $tokenHash)
        ->where('method_controller LIKE \'%_post%\'')
        ->where('DATE(date_time_now) = ?', date('Y-m-d'));
        $res = $this->_conn->fetchAll($select);

        if(count($res) > 100)
        {
            $token = new Token();
            $ip = $token->getIp($tokenHash);
            // Bloquear ip 
            $sql = 'INSERT INTO Blocked(ip, token) VALUES(:ip, :token)';
            $stmt = $this->_conn->prepare($sql);
            $stmt->bindValue (':ip', $ip);
            $stmt->bindValue (':token', $tokenHash);
            $stmt->execute();
            return false;
        }
        return true;
    }   
}

