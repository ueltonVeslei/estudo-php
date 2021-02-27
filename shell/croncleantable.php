<?php

require_once 'abstract.php';

class Onestic_Shell_Croncleantable extends Mage_Shell_Abstract
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
        $readConnection = $resource->getConnection('core_write');
        $sql = 'delete from mundipagg_card_on_file';
        $cleanTable = $readConnection->query($sql);
       
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f croncleantable.php -- [options]
                      
                      Clean card table
  --help              Help

USAGE;
    }
}

$shell = new Onestic_Shell_Cronordergrid();
$shell->run();
