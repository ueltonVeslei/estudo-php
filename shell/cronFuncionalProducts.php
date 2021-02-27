<?php

require_once 'abstract.php';

class Onestic_Shell_CronFuncionalProducts extends Mage_Shell_Abstract
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
        Mage::getModel('smartpbm/pbms_funcional')->updateProducts();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronFuncionalProducts.php -- [options]
                      
                      Run (sync discounts of Funcional)
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_CronFuncionalProducts();
$shell->run();
