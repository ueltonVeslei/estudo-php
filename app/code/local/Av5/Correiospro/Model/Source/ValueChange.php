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

class Av5_Correiospro_Model_Source_ValueChange
{

    public function toOptionArray()
    {
        return array(
        	array('value'=>'0', 'label'=>'Nenhuma'),
            array('value'=>'1', 'label'=>'Desconto Fixo'),
        	array('value'=>'2', 'label'=>'Desconto Percentual'),
        	array('value'=>'5', 'label'=>'Desconto Percentual (Pedido)'),
        	array('value'=>'3', 'label'=>'Acréscimo Fixo'),
        	array('value'=>'4', 'label'=>'Acréscimo Percentual'),
        	array('value'=>'6', 'label'=>'Acréscimo Percentual (Pedido)'),
            array('value'=>'7', 'label'=>'Valor Fixo do Serviço'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    		'0'	=>	'Nenhuma',
    		'1'	=>	'Desconto Fixo',
    		'2'	=>	'Desconto Percentual',
    		'5' =>  'Desconto Percentual (Pedido)',
   			'3'	=>	'Acréscimo Fixo',
    		'4'	=>	'Acréscimo Percentual',
    		'6'	=>	'Acréscimo Percentual (Pedido)',
    	    '7'	=>	'Valor Fixo do Serviço',
    	);
    }

}