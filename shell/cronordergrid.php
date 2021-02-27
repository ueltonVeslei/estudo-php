<?php

require_once 'abstract.php';

class Onestic_Shell_Cronordergrid extends Mage_Shell_Abstract
{
    protected $daysLimit = 90;
    protected $resource;
    protected $readConnection;
    protected $salesModel;
    protected $ordersToProcess = [];

    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        if ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $this->resource = Mage::getSingleton('core/resource');
            $this->readConnection = $this->resource->getConnection('core_read');
            $this->salesModel = Mage::getResourceModel('sales/order');
            $this->_execute();
        }
    }

    protected function _getOrders($sql)
    {
        $collection = $this->readConnection->fetchAll($sql);
        foreach($collection as $order) {
            $this->ordersToProcess[] = $order['entity_id'];
        }
    }

    protected function _getOrdersToImport()
    {
        $date = date('Y-m-d H:i:s', strtotime('-' . $this->daysLimit . ' days'));
        $sql = "select entity_id from sales_flat_order " .
            "where updated_at > '$date' and " .
            "entity_id not in (" .
                "select entity_id " .
                "from sales_flat_order_grid " .
                "where updated_at > '$date')";
        $this->_getOrders($sql);
    }

    protected function _getOrdersToUpdate()
    {
        $sql = "select o.entity_id " .
            "from sales_flat_order o " .
            "inner join sales_flat_order_grid g " .
            "on g.entity_id = o.entity_id and g.status <> o.status";
        $this->_getOrders($sql);
    }

    protected function _execute()
    {
        while (true) {
            try {
                $this->ordersToProcess = [];
                $this->_getOrdersToImport();
                $this->_getOrdersToUpdate();
                if ($this->ordersToProcess) {
                    Mage::log('UPDATE GRID: ' . count($this->ordersToProcess), null, 'ordersgrid.log');
                    $this->salesModel->updateGridRecords($this->ordersToProcess);
                    Mage::log('GRID UPDATED: ' . count($this->ordersToProcess), null, 'ordersgrid.log');
                }
            } catch (Exception $e) {
                Mage::log('GRID ERROR: ' . $e->getMessage(), null, 'ordersgrid.log');
            }
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronordergrid.php -- [options]
                      
                      Update order grid with orders that is not in it
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_Cronordergrid();
$shell->run();
