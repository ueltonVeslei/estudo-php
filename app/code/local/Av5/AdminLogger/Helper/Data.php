<?php
class Av5_AdminLogger_Helper_Data extends Mage_Core_Helper_Abstract {

    public function apiLog($requestId, $function, $args = array(), $response = null, Exception $exception = null) {
        $log = "METODO: {$function}";
        $log .= " - REQUEST ID: {$requestId}";
        $apiUser = $this->getApiSession()->getUser();
        if ($apiUser instanceof Mage_Api_Model_User) {
            $log .= sprintf(" - API User: %s (ID: %s)", $apiUser->getUsername(), $apiUser->getId());
        }
        $log .= " - IP: {$this->getRemoteAddr()}";
        $log .= " - User Agent: {$this->getHttpUserAgent()}";

        if ($function == 'catalogProductUpdate') {
            $xml = file_get_contents('php://input');
            $log .= " - XML: " . $xml;
            $file = fopen(Mage::getBaseDir('var') . '/log/api_data.log', 'a+');
            fwrite($file, date('Y-m-d H:i:s') . ' - ' . $xml . PHP_EOL);
            fclose($file);
        }

        if ($exception) {
            $log .= "\nException: {$exception}";
        }

        $logLevel = ($exception ? Zend_Log::WARN : Zend_Log::DEBUG);
        Mage::log($log, $logLevel, 'admin_logger_api.log');
    }

    public function logMessage($data) {
        $data[] = 'USUARIO: ' . $this->getAdminUser();
        $data[] = 'IP: ' . $this->getRemoteAddr();
        Mage::log(implode(' - ', $data),0,'admin_logger.log');
    }

    protected function getAdminUser() {
        $admin_user_session = Mage::getSingleton('admin/session');
        if($admin_user_session->getUser()) {
            $adminuserId = $admin_user_session->getUser()->getUserId();
            $admin_user = Mage::getModel('admin/user')->load($adminuserId);

            return $admin_user->getEmail();
        }
        return "";
    }

    protected function getRemoteAddr() {
        return Mage::helper('core/http')->getRemoteAddr();
    }

    protected function getHttpUserAgent() {
        return Mage::helper('core/http')->getHttpUserAgent();
    }

    public function getApiSession() {
        return Mage::getSingleton('api/session');
    }
}