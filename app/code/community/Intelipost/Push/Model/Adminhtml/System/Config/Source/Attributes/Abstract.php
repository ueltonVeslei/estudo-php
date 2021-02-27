<?php

class Intelipost_Push_Model_Adminhtml_System_Config_Source_Attributes_Abstract
{

protected $_entityType = null;

public function toOptionArray ()
{
    $collection = Mage::getSingleton ('eav/config')
        ->getEntityType ($this->_entityType)
        ->getAttributeCollection ()
        ->setOrder ('frontend_label','ASC');

    $result = array ();

    foreach ($collection as $child)
    {
        $result [$child->getAttributeCode ()] = $child->getFrontendLabel () . ' ( ' . $child->getAttributeCode () . ' ) ';
    }

    return $result;
}

}

