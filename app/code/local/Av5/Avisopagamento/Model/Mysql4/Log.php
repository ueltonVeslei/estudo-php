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

/**
 * Av5_Correiospro_Model_Mysql4_Carrier_Correios
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */
class Av5_Avisopagamento_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('av5avisopagamento/log', 'id');
    }
	
    public function logOrder($order_id) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5avisopagamento/pagwarnlog');
    	$write->insert($table, array(
    			'order' => $order_id,
    			'date'	=> date('Y-m-d H:i:s')
    	));
    }
    
    public function deleteOrder($order_id) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5avisopagamento/pagwarnlog');
    
    	$rows = $write->delete($table, "order = " . $id);
    }
    
    public function cleanDatabase() {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5avisopagamento/pagwarnlog');
    
    	$rows = $write->delete($table, "1");
    }
    
    public function getOrders() {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5avisopagamento/pagwarnlog');
    	
    	$select = $read->select()->from($table,array("order"));
    	$orders = $read->fetchAll($select);
    	$result = array();
    	foreach ($orders as $order) {
    		$result[] = $order;
    	}
    	
    	return $result;
    }
}
