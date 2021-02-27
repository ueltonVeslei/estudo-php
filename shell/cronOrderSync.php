<?php

require_once 'abstract.php';

class Onestic_Shell_CronOrderSync extends Mage_Shell_Abstract
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 360000);
        set_time_limit(360000);

        if ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $this->_execute();
        }
    }

    protected function _execute()
    {
        $collection = Mage::getModel('onestic_apiserver/orders')->getCollection()
            ->addFieldToFilter('order_id',['null' => true]);
        //$collection->getSelect()->where('updated_at is null OR updated_at <= ?', date('Y-m-d H:i:s', strtotime('-1day')));
        $collection->getSelect()->order('updated_at ASC');
        foreach($collection as $order) {
            $updateOrder = Mage::getModel('onestic_apiserver/orders')->load($order->getId());
            $orderData = json_decode($order->getOrderData());
            try {
                $resultOrder = Mage::getModel('onestic_apiserver/order')->create($orderData);
                if ($resultOrder['IdRetornoPedido'] !== 0) {
                    $orderMage = Mage::getModel("sales/order")->loadByAttribute("marketplace_id", $order->getCode());
                    $updateOrder->setOrderId($orderMage->getId());
                    $updateOrder->setIncrementId($orderMage->getIncrementId());
                }
                Mage::log("ERRO NO PEDIDO " . $order->getId() . ': ' . $resultOrder['Mensagem'],null,'cronOrderSync.log');
            } catch (Exception $e) {
                Mage::log("EXCEPTION NO PEDIDO " . $order->getId() . ': ' . $e->getMessage(),null,'cronOrderSync.log');
            }
            $updateOrder->setUpdatedAt(date('Y-m-d H:i:s'));
            $updateOrder->save();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronOrderSync.php -- [options]
                      
                      Run (process orders that was not synced correctly)
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_CronOrderSync();
$shell->run();
