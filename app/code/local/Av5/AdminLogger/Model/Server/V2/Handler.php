<?php
class Av5_AdminLogger_Model_Server_V2_Handler extends Mage_Api_Model_Server_Wsi_Handler
{
    public function __call($function, $args = array())
    {
        list($sessionId) = $args;
        $requestId = $this->generateRequestId($sessionId->sessionId);
        $helper = $this->getHelper();
        try {
            $helper->apiLog($requestId, $function, $args);
            if ($this->_canProcess()) {
                $response = parent::__call($function, $args);
                $helper->apiLog($requestId, $function, $args, $response);
                return $response;
            } else {
                Mage::throwException('Limite de requisicoes atingido.');
            }
        } catch (Exception $e) {
            $helper->apiLog($requestId, $function, $args, isset($response) ? $response : null, $e);
            throw $e;
        }
    }

    protected function getHelper()
    {
        return Mage::helper('av5_adminlogger');
    }

    protected function generateRequestId($sessionId)
    {
        return uniqid($sessionId . '_', true);
    }

    protected function _isProcfit() {
        $apiUser = $this->getHelper()->getApiSession()->getUser();
        if ($apiUser instanceof Mage_Api_Model_User) {
            if ($apiUser->getUsername() == 'procfit') {
                return true;
            }
        }

        return false;
    }

    protected function _canProcess() {
        if ($this->_isProcfit()) {
            $stat1 = file('/proc/stat');
            sleep(1);
            $stat2 = file('/proc/stat');
            $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
            $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
            $dif = array();
            $dif['user'] = $info2[0] - $info1[0];
            $dif['nice'] = $info2[1] - $info1[1];
            $dif['sys'] = $info2[2] - $info1[2];
            $dif['idle'] = $info2[3] - $info1[3];
            $total = array_sum($dif);
            $cpu = array();
            foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
            if ($cpu['sys'] >= 15) {
                return false;
            }
        }
        return true;
    }

}