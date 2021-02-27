<?php
class Onestic_Edrone_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_CONFIG_PATH = 'onestic_edrone/general/';

    public function getConfig($field)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH . $field);
    }

}