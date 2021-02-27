<?php

class Intelipost_Basic_Model_VersionControl
extends Varien_Object
{

protected $_moduleName;
protected $_moduleHelper;
protected $_moduleVersion;

public function getModuleVersion()
{
if ($this->_moduleHelper)
{
    $this->_moduleVersion = Mage::getConfig()->getModuleConfig($this->getModuleHandle())->version;
}

return $this->_moduleVersion;
}

public function getModuleHandle()
{
return Mage::helper($this->_moduleHelper)->getModuleHandle();
}

public function setModuleName($moduleName)
{
$this->_moduleName = $moduleName;
}

public function setModuleHelper($moduleHelper)
{
$this->_moduleHelper = $moduleHelper;
}

public function getModuleName()
{
return $this->_moduleName;
}

}

