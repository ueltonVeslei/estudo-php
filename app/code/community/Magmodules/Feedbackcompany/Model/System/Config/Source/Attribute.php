<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Feedbackcompany
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magmodules_Feedbackcompany_Model_System_Config_Source_Attribute
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray[] = array(
            'label' => Mage::helper('feedbackcompany')->__('-- Select Yes/No Attribute'),
            'value' => ''
        );

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('frontend_input', array('boolean'));

        foreach ($attributes as $attribute) {
            $optionArray[] = array(
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code')
            );
        }

        return $optionArray;
    }

}