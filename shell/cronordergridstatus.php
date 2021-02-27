<?php

require_once 'abstract.php';

class Onestic_Shell_Cronordergridstatus extends Mage_Shell_Abstract
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
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $sql = 'select o.entity_id from sales_flat_order o inner join sales_flat_order_grid g on g.entity_id = o.entity_id and g.status <> o.status';
        $ordersAll = $readConnection->fetchAll($sql);
        $orders = [];
        $count = 0;
        foreach($ordersAll as $order) {
            $orders[] = $order['entity_id'];
            $count++;
            if ($count == 50) break;
        }
        if ($orders) {
	    echo 'UPDATE ORDERS: ' . implode($orders, ',') . PHP_EOL;
            $model = Mage::getResourceModel('sales/order');
            $model->updateGridRecords($orders);
	    echo 'UPDATED' . PHP_EOL;
        }
    }

    protected function _validate()
    {

    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronordergridstatus.php -- [options]
                      
                      Update order status in grid
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_Cronordergridstatus();
$shell->run();
