<?php
class Model_Customer {
    private $_excludes = [];

    public function authenticate($request) {
        // Mage::getSingleton("core/session");
        $session = Mage::getSingleton('customer/session');
        $data = null;
        //echo json_encode($request);

        try {
            $session->login($request->email, $request->password);
            $session->setCustomerAsLoggedIn($session->getCustomer());
            // $this->setResponse('status',Standard::STATUS200);
            $customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
            //echo json_encode($customer->getData());
            $data = [
                'customer' => $customer->toArray(),
                'token' => $session->getCustomer()->getId()
            ];
        }
        catch(Exception $e)
        {
            $data = ['erro' => $e->getMessage()];
        }
        //echo json_encode($data);
        return $data;
    }
     /**
     * Envia o email e grava no banco
     */
    public function sendRecovery($request){
        try{
            $res= false;
            //Obtem a model de recuperação de senha
            $registryRecovery = new Model_RegistryRecovery();
            //Verifica se não tem nenhuma chave de verificação ja ativa para este email
            $chavesAtiva = $registryRecovery->getCodeActive($request->email);
            //verifica se o usuario possui chaves ativas
            if(count($chavesAtiva) > 0){
                //Desabilita a chave
                $registryRecovery->disableCode($chavesAtiva[0]['chave']);
            }
            //Gera uma nova chave
            $chave = $registryRecovery->generateCodeRecovery($request);
            //Se a chave foi gerada e gravada com sucesso
            if($chave != ''){
                //Envia um email para o usuário com a nova chave
                $res = $this->sendMailMagento($request->email, $chave);
            }
            return $res;
        }
        catch(Exception $e){
            return false;
        }
    }
    /**
     * Verifica o código, altera a senha e altera o status do código
     */
    public function updatePassword($request){
        $registryRecovery = new Model_RegistryRecovery();
        $valid = $registryRecovery->validCode($request->email, $request->chave);

        if($valid){
            //Altera a senha do usuario no magento
            $alt = $registryRecovery->updatePassword($request->email, $request->password);

            if($alt){
                //Desabilita a chave
                $registryRecovery->disableCode($request->chave);       
            }
        }
        return $valid;
    }


    public function sendEmail($email, $chave){
        $to = $email;
        $subject = "Redefinição de senha";
        
        $headers = 'From: '. Config::EMAILSALE  . "\r\n" .
            'Reply-To: '. Config::EMAILSALE . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $message = "A sua chave para redefinir senha é: $chave";

        mail($to, $subject, $message, $headers);
    }

    public function sendMailMagento($email, $chave) 
    {   
        /**
         * Obtem o nome do cliente
         */
        $customer = Mage::getModel('customer/customer')->setWebsiteId(Config::WEBSITE);
        $customer->loadByEmail($email);
        $nome = $customer->getFirstname() . ' ' . $customer->getLastname();

        $html="<h2>A sua chave para redefinir senha: $chave</h2>";

        $mail = Mage::getModel('core/email');
        $mail->setToName($nome);
        $mail->setToEmail($email);
        $mail->setBody("A sua chave para redefinir senha: $chave");
        $mail->setSubject('Redefinir a senha');
        $mail->setFromEmail(Config::EMAILSALE);
        $mail->setFromName(Config::NAMESALE);
        //$mail->setType('html');// You can use Html or text as Mail format
        //$mail->setBodyHTML($html);  // your content or message

        try {
            $mail->send();
            return true;
        }
        catch (Exception $e) {
        }
        return false;
    }
}
