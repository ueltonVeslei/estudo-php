<?php

/**
 * Magaya
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@logistico.com so we can send you a copy immediately.
 *
 *
 * @category   Integration
 * @package    Magaya_Logistico
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magaya_Logistico_Block_Adminhtml_System_Config_Wsicompliance
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_template = 'magaya/logistico/system/config/wsicompliance.phtml';
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
    
    protected function isComplianceEnabled()
    {
        return Mage::getStoreConfig('api/config/compliance_wsi');
    }
}