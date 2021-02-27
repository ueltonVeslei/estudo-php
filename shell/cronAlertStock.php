<?php

require_once 'abstract.php';

class Onesti_Shell_CronProductAlert extends Mage_Shell_Abstract
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
        Mage::getModel('productalert/observer')->process();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronAlertStock.php -- [options]
                      
                      Run (process orders that was not synced correctly)
  --help              Help

USAGE;
    }
}

$shell = new Onesti_Shell_CronProductAlert();
$shell->run();
