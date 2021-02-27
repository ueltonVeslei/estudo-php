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

class Magaya_Logistico_Model_Observer
{
    public function modifyOrderGrid($observer)
    { 
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
        $after = 'status';

        $block = $observer->getBlock();
        if (!isset($block) || !$block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            return $this;
        }

        $column = array(
            'header' => Mage::helper('sales')->__('Logistico Synced?'),
            'index' => 'logistico_synced',
            'width' => '70px',
            'type'  => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No'
            )
        );
        $block->addColumnAfter('logistico_synced', $column, $after);
    }
    
}