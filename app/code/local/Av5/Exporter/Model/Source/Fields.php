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
 * @package    Av5_Xml
 * @copyright  Copyright (c) 2010 Ecommerce Developer Blog (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Display in field options source model
 *
 */
class Av5_Exporter_Model_Source_Fields
{
    public function toOptionArray()
    {
        $return = [];
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        foreach ($attributes as $attribute){
            if ($attribute->getData('frontend_label')) {
                $return[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getData('frontend_label')
                ];
            }
        }
        $return[] = [
            'value' => 'qty',
            'label' => 'Estoque'
        ];
        return $return;
    }
}
