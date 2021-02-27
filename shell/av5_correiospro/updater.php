<?php
/**
 * AV5 Tecnologia
 *
 * @category  Shipping
 * @package   av5_correiospro/updater
 * @version   4.5.0
 * @copyright Copyright (C) 2019 AV5 Tecnologia (https://www.av5.com.br/)
 */


require_once '../abstract.php';

/**
 * @category Shipping
 * @package  AV5_Correiospro
 */
class Av5_Correiospro_Shell_Updater extends Mage_Shell_Abstract
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
            $page = intval(date('i'));
            if ($this->getArg('offset')) {
                $offset = intval($this->getArg('offset'));
                if ($offset) {
                    $page += $offset;
                }
            }
            Mage::getSingleton('core/session')->setCorreiosPage($page);
            $start = microtime(true);
            $updater->update();
            $end = microtime(true);
            $finalTime = number_format(($end - $start),2);
            echo 'PAGINA ' . $page . ' > TEMPO: ' . $finalTime . 'seg' . PHP_EOL;
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f av5_correiospro/updater.php -- [options]
                      
                        Run (update prices table)
  --help                Help
  --offset <quantity>   Offset to pagination

USAGE;
    }
}

$shell = new Av5_Correiospro_Shell_Updater();
$shell->run();
