<?php
class Onestic_Skyhub_Model_Api_Abstract {
    
    protected static $API_ROOT_URL      = "https://api.skyhub.com.br";
    private $_email                     = null;
    private $_token                     = null;
    
    public static $CURL_OPTS = array(
        CURLOPT_USERAGENT => "ONESTIC.MAGENTO.SKYHUB.MODULE",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 60
    );
    
    public function get($path, $params = null) {
        $exec = $this->execute($path, null, $params);
    
        return $exec;
    }
    
    public function post($path, $body = null, $params = array()) {
        $body = json_encode($body);
        $opts = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }
    
    public function put($path, $body = null, $params=array()) {
        $body = json_encode($body);
        $opts = array(
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $body
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }
    
    public function delete($path, $params=array()) {
        $opts = array(
            CURLOPT_CUSTOMREQUEST => "DELETE"
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }
    
    public function execute($path, $opts = array(), $params = array()) {
        $helper = Mage::helper('onestic_skyhub');
        $uri = $this->make_path($path, $params);
    
        $ch = curl_init($uri);
        curl_setopt_array($ch, self::$CURL_OPTS);
        
        $opts[CURLOPT_HTTPHEADER] = array(
            'X-User-Email: ' . $helper->getEmail(),
            'x-Api-Key: ' . $helper->getToken(),
        	'x-accountmanager-key: ' . $helper->getAccountManager(),
            'Accept: application/json',
            'Content-Type: application/json'
        );
        
        if(!empty($opts))
            curl_setopt_array($ch, $opts);
    
        $return["body"] = json_decode(curl_exec($ch));
        $return["httpCode"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (Mage::helper('onestic_skyhub')->getConfig('debug')) {
            Mage::log('====== EXECUTE ENDPOINT ======',null,'onestic_skyhub.log');
            Mage::log('PATH: ' . $uri,null,'onestic_skyhub.log');
            Mage::log('OPTS: ' . var_export($opts,true),null,'onestic_skyhub.log');
            Mage::log('RETURN: ' . var_export($return['body'],true),null,'onestic_skyhub.log');
            Mage::log('HTTP CODE: ' . $return['httpCode'],null,'onestic_skyhub.log');
            Mage::log('====== END EXECUTION ENDPOINT ======',null,'onestic_skyhub.log');
        }
        
        curl_close($ch);
        
        $this->checkResponseErrors($return['httpCode']);
    
        return $return;
    }
    
    public function make_path($path, $params = array()) {
        if (!preg_match("/^http/", $path)) {
            if (!preg_match("/^\//", $path)) {
                $path = '/'.$path;
            }
            $uri = self::$API_ROOT_URL.$path;
        } else {
            $uri = $path;
        }
    
        if(!empty($params)) {
            $paramsJoined = array();
    
            foreach($params as $param => $value) {
                $paramsJoined[] = "$param=$value";
            }
            $params = '?'.implode('&', $paramsJoined);
            $uri = $uri.$params;
        }
    
        return $uri;
    }
    
    public function checkResponseErrors($httpCode)
    {
        $message = '';
        switch ($httpCode) {
            case 400: // Requisição mal-formada
                $message = 'Requisição mal-formada';
                break;
            case 401: // Erro de autenticação
                $message = 'Erro de autenticação';
                break;
            case 403: // Erro de autorização
                $message = 'Erro de autorização';
                break;
            case 404: // Recurso não encontrado
                $message = 'Recurso não encontrado';
                break;
            case 405: // Metodo não suportado
                $message = 'Método não suportado';
                break;
            case 422: // Erro semântico
                $message = 'Erro semântico';
                break;
            case 500: // Erro na API
                $message = 'Erro na API';
                break;
        }
        
        if ($message) {
            Mage::log('ERRO: ' . $message,null,'onestic_skyhub.log');
        }
        
        return $message;
    }
    
}