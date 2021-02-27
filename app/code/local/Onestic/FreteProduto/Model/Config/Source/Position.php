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
 * @category   Onestic
 * @package    Onestic_FreteProduto
 * @copyright  Copyright (c) 2017 Ecommerce Developer Blog (http://www.onestic.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Display in field options source model
 *
 */
class Onestic_FreteProduto_Model_Config_Source_Position
{
    /**
     * Return list of options for the system configuration field.
     * These options indicate the position of the form block on the page
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Onestic_FreteProduto_Model_Config::DISPLAY_POSITION_LEFT,
                'label' => Mage::helper('onestic_freteproduto')->__('Left Column')
            ),
            array(
                'value' => Onestic_FreteProduto_Model_Config::DISPLAY_POSITION_RIGHT,
                'label' => Mage::helper('onestic_freteproduto')->__('Right Column')
            ),
            array(
                'value' => Onestic_FreteProduto_Model_Config::DISPLAY_POSITION_CUSTOM,
                'label' => Mage::helper('onestic_freteproduto')->__('Custom Position')
            ),
        );
    }
}

