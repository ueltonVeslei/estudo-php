<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   AV5
 * @package    Av5_FreteProduto
 * @copyright  Copyright (c) 2010 Ecommerce Developer Blog (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Module helper for translations
 *
 */
class Av5_Exporter_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function getConfigData($field) {
        $path = 'av5_exporter/settings/';
        return Mage::getStoreConfig($path . $field);
    }
    
}
