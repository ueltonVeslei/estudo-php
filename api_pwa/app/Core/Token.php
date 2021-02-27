<?php
class Token extends Db {
    //Os atributos devem ficar em ordem para seram inseridos no banco corretamente
    //(Não é necessário o nome do atributo ser igual ao do banco)
    protected $_tableName = 'Token';
    public $token = '';

    public function __construct(){
        parent::__construct();
    }

    public function createToken($request, $idUser){
        $token = $this->gerateToken($request->ip);
        
        if(is_numeric($idUser) && $idUser > 0){
            $sql = 'INSERT INTO Token(token, data_expiracao, ip, dns_name, user_agent, id_user, type_login_permission)'
                        .' VALUES(:token, :data_expiracao, :ip, :dns_name, :user_agent, :id_user, :type_login_permission)';
            $stmt = $this->_conn->prepare($sql);
            $stmt->bindValue(':id_user', $idUser);
        }
        else{
            $sql = 'INSERT INTO Token(token, data_expiracao, ip, dns_name, user_agent, type_login_permission)
                        VALUES(:token, :data_expiracao, :ip, :dns_name, :user_agent, :type_login_permission)';
            $stmt = $this->_conn->prepare($sql);
        }
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':data_expiracao', strval(Utils::plusDayNow(Config::QTDDAYSPIRETOKEN)));
        $stmt->bindValue(':ip', $request->ip);
        $stmt->bindValue(':dns_name', $request->dns_name);
        $stmt->bindValue(':user_agent', $request->user_agent);
        $stmt->bindValue(':type_login_permission', AcessRestriction::getTypeUserAcess($idUser));
        $stmt->execute();

        $this->token = $token;
    }

    public function getIp($tokenHash){
        $select = $this->_conn->select()->from(
            ['token' => 'Token'],
            ['ip']
        )->where('token = ?', $tokenHash);
        $res = $this->_conn->fetchAll($select);
        if($res){
            return $res[0]['ip'];
        }
        return '';
    }
    
    public function getIdUser($tokenHash){
        $select = $this->_conn->select()->from(
            ['token' => 'Token'],
            ['id_user']
        )->where('token = ?', $tokenHash);
        $res = $this->_conn->fetchAll($select);
        if($res){
            // var_dump($res);
            return $res[0]['id_user'];
        }
        return '';
    }

    public function validToken($token){
        $select = $this->_conn->select()->from(
            ['token' => 'Token']
        )->where('token = ?', $token);
        $res = $this->_conn->fetchAll($select);
        $valid = false;
        if(count($res) > 0){
            //Valida se o token não expirou
            $select = $this->_conn->select()->from(
                ['token' => 'Token']
            )->where('token = ?', $token)
            ->where('data_expiracao <= ?', strval(date('Y-m-d')));
            $res = $this->_conn->fetchAll($select);
            $valid = count($res) == 0;
        }
        return $valid;
    }

    //Token de autenticação diferente do JWT que é para confiabilidade
    private function gerateToken($ip){
        $token = strval($ip) . strval(date("Y-m-d H:i:s"));
        $token = Criptography::sha1($token);
        return $token;
    }


}