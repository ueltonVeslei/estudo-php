<?php

require_once 'abstract.php';

class Onestic_Shell_CronSkyhubSyncInvoice extends Mage_Shell_Abstract
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
        Mage::getModel('onestic_skyhub/updater')->syncInvoice();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronSkyhubSyncInvoice.php -- [options]
                      
                      Run (sync invoices in Skyhub)
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_CronSkyhubSyncInvoice();
$shell->run();
