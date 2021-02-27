<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Base-v1.x.x
@copyright	Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

class Plumrocket_Base_Helper_Base extends Mage_Core_Helper_Abstract
{
    /**
     * @return string
     */
    protected function getMktpKey()
    {
        return implode('', array_map('ch'.
            'r', explode('.', '53.51.50.52.49.54.52.56.54.98.53.52.48.101.97.50.97.49.101.53.48.99.52.48.55.48.98.54.55.49.54.49.49.98.52.52.102.53.50.55.49.56')
        ));
    }

    /**
     * @return mixed
     */
    protected function getCurentConfig()
    {
        return $this->getConfig($this->getModName() . '/module/data');
    }

    /**
     * @param $customerKey
     * @return bool|mixed
     */
    protected function getTrueCustomerKey($customerKey)
    {
        $trueKey = false;

        if ($customerKey == $this->getMktpKey()) {
            $trueKey = $this->getCurentConfig();
        }

        return $trueKey ? $trueKey : $customerKey;
    }

    /**
     * @return string
     */
    private function getModName()
    {
        $data = explode("_", $this->_getModuleName());

        return isset($data[1]) ? $data[1] : '';
    }

    /**
     * @param $customerKey
     * @return bool
     */
    public function isMarketplace($customerKey)
    {
        if ($customerKey == $this->getMktpKey()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function preparedData()
    {
        $data = array(
            'magento_version' => Mage::getVersion()
        );

        return $data;
    }

    /**
     * @param string $title
     * @return string
     */
    public static function backtrace($title = 'Debug Backtrace:')
    {
        $output = "";
        $output .= "<hr /><div>" . $title . '<br /><table border="1" cellpadding="2" cellspacing="2">';
        $stacks = debug_backtrace();

        $output .= "<thead><tr><th><strong>File</strong></th><th><strong>Line</strong></th><th><strong>Function</strong></th>".
            "</tr></thead>";

        foreach($stacks as $_stack) {

            if (!isset($_stack['file'])) {
                $_stack['file'] = '[PHP Kernel]';
            }

            if (!isset($_stack['line'])) {
                $_stack['line'] = '';
            }

            $output .=  "<tr><td>{$_stack["file"]}</td><td>{$_stack["line"]}</td>".
                "<td>{$_stack["function"]}</td></tr>";
        }

        $output .=  "</table></div><hr /></p>";

        return $output;
    }

    /**
     * @return string
     */
    public function getConfigSectionId()
    {
        return $this->_configSectionId;
    }

    /**
     * @param null $moduleName
     * @return bool|int
     */
    public function moduleExists($moduleName = null)
    {
        $hasModule = $this->isModuleEnabled($moduleName);

        if ($hasModule) {
            return $this->getModuleHelper($moduleName)->moduleEnabled() ? 2 : 1;
        }

        return false;
    }

    /**
     * @param      $path
     * @param null $store
     * @return mixed
     */
    public function getConfig($path, $store = null)
    {
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * @param null $moduleName
     * @return Mage_Core_Helper_Abstract|null
     */
    public function getModuleHelper($moduleName = null)
    {
        $nodes = Mage::getConfig()->getNode('global/helpers')->asArray();

        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }

        foreach ($nodes as $key => $item) {
            if (isset($item['class'])) {
                if (stripos($item['class'], $moduleName) !== false) {
                    return Mage::helper($key);
                }
            }
        }

        return null;
    }
}