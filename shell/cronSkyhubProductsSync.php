<?php

require_once 'abstract.php';

class Onestic_Shell_CronSkyhubProductsSync extends Mage_Shell_Abstract
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        if ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $this->_execute();
        }
    }

    protected function _execute()
    {
        Mage::getModel('onestic_skyhub/products_updater')->products();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronSkyhubProductsSync.php -- [options]
                      
                      Run (process orders that was not synced correctly)
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_CronSkyhubProductsSync();
$shell->run();
