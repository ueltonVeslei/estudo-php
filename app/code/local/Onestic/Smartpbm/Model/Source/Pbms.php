<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Shipping (Frete)
 * @package    Av5_Correiospro
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Onestic_Smartpbm_Model_Source_Pbms
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'funcional', 'label'=>Mage::helper('adminhtml')->__('Funcional')),
            array('value'=>'sevenpdv', 'label'=>Mage::helper('adminhtml')->__('Seven PDV')),
        	array('value'=>'vidalink', 'label'=>Mage::helper('adminhtml')->__('Vidalink')),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	        'funcional'    => Mage::helper('adminhtml')->__('Funcional'),
    	        'sevenpdv'     => Mage::helper('adminhtml')->__('Seven PDV'),
    	        'vidalink'     => Mage::helper('adminhtml')->__('Vidalink'),
    	);
    } 

}