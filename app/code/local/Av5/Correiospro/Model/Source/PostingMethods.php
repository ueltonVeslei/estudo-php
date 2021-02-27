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

class Av5_Correiospro_Model_Source_PostingMethods
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'04014', 'label'=>Mage::helper('adminhtml')->__('Sedex Sem Contrato (04014)')),
            array('value'=>'04162', 'label'=>Mage::helper('adminhtml')->__('Sedex Com Contrato (04162)')),
            array('value'=>'04154', 'label'=>Mage::helper('adminhtml')->__('Sedex Contrato Agência (04154)')),
            array('value'=>'04553', 'label'=>Mage::helper('adminhtml')->__('Sedex Contrato Agência (04553)')),
            array('value'=>'04510', 'label'=>Mage::helper('adminhtml')->__('PAC Sem Contrato (04510)')),
            array('value'=>'04669', 'label'=>Mage::helper('adminhtml')->__('PAC Com Contrato (04669)')),
            array('value'=>'04367', 'label'=>Mage::helper('adminhtml')->__('PAC Contrato Agência (04367)')),
            array('value'=>'04596', 'label'=>Mage::helper('adminhtml')->__('PAC Contrato Agência (04596)')),
            array('value'=>'04618', 'label'=>Mage::helper('adminhtml')->__('PAC Grandes Volumes (04618)')),
            array('value'=>'04693', 'label'=>Mage::helper('adminhtml')->__('PAC Grandes Volumes (04693)')),
            array('value'=>'40215', 'label'=>Mage::helper('adminhtml')->__('Sedex 10 (40215)')),
            array('value'=>'40169', 'label'=>Mage::helper('adminhtml')->__('Sedex 12 (40169)')),
            array('value'=>'40290', 'label'=>Mage::helper('adminhtml')->__('Sedex HOJE (40290)')),
            array('value'=>'40045', 'label'=>Mage::helper('adminhtml')->__('Sedex a Cobrar (40045)')),
        );
    }

    public function toOptionArray2()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('Selecione...')),
            array('value'=>'04014', 'label'=>Mage::helper('adminhtml')->__('Sedex Sem Contrato (04014)')),
            array('value'=>'04162', 'label'=>Mage::helper('adminhtml')->__('Sedex Com Contrato (04162)')),
            array('value'=>'04154', 'label'=>Mage::helper('adminhtml')->__('Sedex Contrato Agência (04154)')),
            array('value'=>'04553', 'label'=>Mage::helper('adminhtml')->__('Sedex Contrato Agência (04553)')),
            array('value'=>'04510', 'label'=>Mage::helper('adminhtml')->__('PAC Sem Contrato (04510)')),
            array('value'=>'04669', 'label'=>Mage::helper('adminhtml')->__('PAC Com Contrato (04669)')),
            array('value'=>'04367', 'label'=>Mage::helper('adminhtml')->__('PAC Contrato Agência (04367)')),
            array('value'=>'04596', 'label'=>Mage::helper('adminhtml')->__('PAC Contrato Agência (04596)')),
            array('value'=>'04618', 'label'=>Mage::helper('adminhtml')->__('PAC Grandes Volumes (04618)')),
            array('value'=>'04693', 'label'=>Mage::helper('adminhtml')->__('PAC Grandes Volumes (04693)')),
            array('value'=>'40215', 'label'=>Mage::helper('adminhtml')->__('Sedex 10 (40215)')),
            array('value'=>'40169', 'label'=>Mage::helper('adminhtml')->__('Sedex 10 (40169)')),
            array('value'=>'40290', 'label'=>Mage::helper('adminhtml')->__('Sedex HOJE (40290)')),
            array('value'=>'40045', 'label'=>Mage::helper('adminhtml')->__('Sedex a Cobrar (40045)')),
        );
    }

    public function toColumnOptionArray()
    {
    	return array(
    			'04014' => Mage::helper('adminhtml')->__('Sedex Sem Contrato (04014)'),
    	        '04162' => Mage::helper('adminhtml')->__('Sedex Com Contrato (04162)'),
    	        '04154' => Mage::helper('adminhtml')->__('Sedex Contrato Agência (04154)'),
                '04553' => Mage::helper('adminhtml')->__('Sedex Contrato Agência (04553)'),
                '04510' => Mage::helper('adminhtml')->__('PAC Sem Contrato (04510)'),
                '04669' => Mage::helper('adminhtml')->__('PAC Com Contrato (04669)'),
                '04367' => Mage::helper('adminhtml')->__('PAC Contrato Agência (04367)'),
                '04596' => Mage::helper('adminhtml')->__('PAC Contrato Agência (04596)'),
                '04693' => Mage::helper('adminhtml')->__('PAC Grandes Volumes (04693)'),
    			'40215' => Mage::helper('adminhtml')->__('Sedex 10 (40215)'),
    			'40290' => Mage::helper('adminhtml')->__('Sedex HOJE (40290)'),
    			'40045' => Mage::helper('adminhtml')->__('Sedex a Cobrar (40045)'),
    	);
    }

}