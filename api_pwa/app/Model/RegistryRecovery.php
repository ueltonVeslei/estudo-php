<?php
class Model_RegistryRecovery extends Db{
    private $_excludes = [];
    protected $_tableName = 'RecoveryPassword';

    public function __construct(){
        parent::__construct();
    }
    /**
     * Gera o código de recuperação
     * deve ser unico e incrmental 
     * para nao se repetir
     */
    public function generateCodeRecovery($request){
        $chave = rand(100000, 999999);
        $customer = Mage::getModel('customer/customer')->setWebsiteId(Config::WEBSITE);
        $customer->loadByEmail($request->email);
        $customerData = $customer->getData();
        if(isset($customerData['entity_id']) && $customerData['entity_id'] > 0){
            $data = array('email' => $request->email, 'chave' => $chave, 'status' => 1, 'id_user' => $customerData['entity_id'], 'is_active' => $customerData['is_active']);
            //Salva a chave
            $this->createRecovery($data);
            return $chave;
        }
        //Retorna null se o email do cliente não existir
        return '';        
    }   

    public function createRecovery($data = []){
        //Adiciona o usuario caso ele não existir na tabela User
        $user = new User();
        $user->addUserNotExists($data['id_user'], $data['is_active']);
        $sql = 'INSERT INTO RecoveryPassword(chave, status, id_user, email) VALUES(:chave, :status, :id_user, :email)';
        $stmt = $this->_conn->prepare($sql);
        $stmt->bindValue (':chave', $data['chave']);
        $stmt->bindValue (':status', $data['status']);
        $stmt->bindValue (':id_user', $data['id_user']);
        $stmt->bindValue (':email', $data['email']);
        $stmt->execute();
        return true;
    }

    public function getRecovery($chave){
        $select = $this->_conn->select()->from(
            ['recoveryPassword' => 'RecoveryPassword']
        )->where('chave = ?', $chave);
        $res = $this->_conn->fetchAll($select);
        return $res;
    }
    /**
     * Obtem se existe um codigo da chave de recuperação ativo
     */
    public function getCodeActive($email){
        $select = $this->_conn->select()->from(
            ['recoveryPassword' => 'RecoveryPassword']
        )->where('email = ?', $email)
        ->where('status = 1');
        
        $res = $this->_conn->fetchAll($select);

        return $res;
    }

    /**
     * Desabilita a chave
     */
    public function disableCode($chave){
        $sql = 'UPDATE RecoveryPassword SET status = 0 WHERE chave = :chave';
        $stmt = $this->_conn->prepare($sql);
        $stmt->bindValue (':chave', $chave);
        $stmt->execute();
        return true;
    }

    /**
     * Verifica o código de recuperação
     */
    public function validCode($email, $chave){

        $select = $this->_conn->select()->from(
            ['recoveryPassword' => 'RecoveryPassword']
        )->where('email = ?', $email)
        ->where('chave = ?', $chave)
        ->where('status = 1');
        $res = $this->_conn->fetchAll($select);
    
        //Caso o código existir e for ativo
        if(count($res) > 0)
            return true;

        return false;
    }

    /**
     * Update password customer magento
     */
    public function updatePassword($email, $newPassword){
        try{
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Config::WEBSITE);
            $customer->loadByEmail($email);
            if($customer->getId() > 0){
                $sql = "update customer_entity_varchar 
                    set value = md5(:newPassword) 
                    where entity_id=:id
                        and attribute_id 
                        in (select attribute_id 
                                                from eav_attribute 
                                                where attribute_code = 'password_hash' and entity_type_id = 1);";
                $stmt = $this->_conn->prepare($sql);
                $stmt->bindValue(':id', $customer->getId());
                $stmt->bindValue(':newPassword', $newPassword);
                $stmt->execute();
                return true;
            }
            return false;
        }
        catch(Exception $e){
            return false;
        }
    }
}