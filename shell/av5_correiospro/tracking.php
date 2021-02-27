<?php
/**
 * AV5 Tecnologia
 *
 * @category  Shipping
 * @package   av5_correiospro/tracking
 * @version   4.5.0
 * @copyright Copyright (C) 2019 AV5 Tecnologia (https://www.av5.com.br/)
 */


require_once '../abstract.php';

/**
 * @category Shipping
 * @package  AV5_Correiospro
 */
class Av5_Correiospro_Shell_Tracking extends Mage_Shell_Abstract
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 360000);
        set_time_limit(360000);

        $updater = Mage::getSingleton('av5_correiospro/updater');

        if ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $updater->tracking();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f av5_correiospro/tracking.php -- [options]
                      
                      Run (update prices table)
  --help              Help

USAGE;
    }
}

$shell = new Av5_Correiospro_Shell_Tracking();
$shell->run();
