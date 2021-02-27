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

class Av5_Correiospro_Model_Source_Yesno
{

    public function toOptionArray()
    {
        return array(
            array('value'=> 0, 'label'=>'Desativada'),
        	array('value'=> 1, 'label'=>'Ativada'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
   			0 => 'Desativada',
   			1 => 'Ativada',
    	);
    }

}