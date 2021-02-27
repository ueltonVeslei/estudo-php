<?php

require_once 'abstract.php';

class Mirasvit_Shell_Cronprod extends Mage_Shell_Abstract
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 360000);
        set_time_limit(360000);

        $control = Mage::getSingleton('onestic_skyhub/products_updater');

        if ($this->getArg('export')) {
            $class  = $this->getArg('class');
            $method = $this->getArg('method');
            $args   = explode(',', $this->getArg('args'));

            $this->_execute($class, $method, $args, $this->getArg('async'));
        } elseif ($this->getArg('fill-queue')) {
            $this->_fillQueue();
        } elseif ($this->getArg('ping')) {
            echo Mirasvit_AsyncIndex_Model_Config::STATUS_OK;
        } elseif ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $control->products();
        }
    }

    protected function _execute($class, $method, $args, $async = false)
    {
        $object = new $class();

        if ($async) {
            $result = $object->execute($method, $args, false);
            if ($result) {
                Mage::helper('onestic_skyhub')->error($args[0], implode("\n", $result));
            }
        } else {
            echo call_user_func_array(array($object, $method), $args);
        }

    }

    /**
     * симулируем очередь
     * сохраняем несколько продуктов
     * сохраняем несколько категорий
     *
     * @return object
     */
    protected function _fillQueue()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->order('rand()');

        foreach ($collection as $product) {
            echo '.';
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $product->setName($product->getName())->setPrice($product->getPrice())->save();
        }

        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->getSelect()->order('rand()');

        foreach ($collection as $category) {
            echo '*';
            $category = $category->load($category->getId());
            $category->setName($category->getName())->save();
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
Usage:  php -f cronprod.php -- [options]
                      
                      Run (reindex queue, index validation (if enabled))
  --fill-queue        Generate random queue
  --help              Help

USAGE;
    }
}

$shell = new Mirasvit_Shell_Cronprod();
$shell->run();
