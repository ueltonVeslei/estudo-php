<?php
 /**
* Onestic Automatic Cross & Up sells Module
*
* @category    Onestic
* @package     Onestic_Autocrossupsells
* @copyright   Copyright (c) 2017 Onestic (http://onestic.com.br/)
* @link        http://onestic.com.br/
*/

class Onestic_Autocrossupsells_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED          = 'autocrossupsells/autocrossupsellssettings/enable_action';
    const XML_PATH_ENABLE_UPSELL    = 'autocrossupsells/autocrossupsellssettings/enable_upsell';
    const XML_PATH_CATEGORY_FILTER  = 'autocrossupsells/autocrossupsellssettings/category_filter';
    const XML_PATH_ENABLE_CROSSELL  = 'autocrossupsells/autocrossupsellssettings/enable_crossell';


    public function isEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }
     public function isCrossellEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLE_CROSSELL);
    }
    public function isUpsellEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLE_UPSELL);
    }
     public function isCategoryFilterEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_CATEGORY_FILTER);
    }

}
