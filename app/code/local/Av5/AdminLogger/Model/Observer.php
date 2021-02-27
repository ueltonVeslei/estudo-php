<?php
class Av5_AdminLogger_Model_Observer {

    public function logConfig(Varien_Event_Observer $observer) {
        $message = [
            'TIPO: CONFIG',
            'SECAO: ' . $observer->getEvent()->getSection()
        ];

        Mage::helper('av5_adminlogger')->logMessage($message);
    }

    public function captureLog(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();
        $module = $request->getControllerModule();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $message = [
            'TIPO: FUNCAO',
            'SECAO: ' . $module."_".$controller."_".$action
        ];

        Mage::helper('av5_adminlogger')->logMessage($message);
    }

}