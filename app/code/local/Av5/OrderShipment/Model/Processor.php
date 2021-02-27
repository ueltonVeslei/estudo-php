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
 * @category   Sale (Orders)
 * @package    Av5_OrderShipment
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Av5_OrderShipment_Model_Processor
 *
 * @category   Sale
 * @package    Av5_OrderShipment
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_OrderShipment_Model_Processor extends Varien_Object {
	
	protected $_separator;
	protected $_file;
	protected $_pointer;
	protected $_success;
	protected $_errors;
	protected $_blockSize;
	
	protected function _init() {
		$helper = Mage::helper('av5_ordershipment');
		$this->_separator= $helper->getConfigData('separator');
		$this->_file = $helper->loadFile();
		$this->_blockSize = $helper->getConfigData('block_size');
		$this->_pointer = $helper->getPointer();
		$this->_success = 0;
		$this->_errors = 0;
	}
	
	public function process() {
		$this->_init();
		$first = true;
		$columns = array();
		$fullCounter = $counter = 0;
		if ($this->_pointer < count($this->_file)) {
			foreach ($this->_file as $line) {
				$data = explode($this->_separator, $line);
				/*if ($first) {
					$columns = $data;
					$columns[0] = 'sku';
					$columns[count($columns)-1] = rtrim($columns[count($columns)-1]);
					$first = false;
					continue;
				}*/
				$fullCounter++;
				if ($fullCounter < $this->_pointer) continue;
				$this->_processItem($data);
				$counter++;
				if ($counter == $this->_blockSize) break;
			}
			Mage::helper('av5_ordershipment')->setPointer($fullCounter+1);
		}
		return array('success' => $this->_success,'errors' => $this->_errors,'count'=>$fullCounter,'total'=>count($this->_file));
	}
	
	protected function _processItem($data) {
		$order = Mage::getModel('sales/order')->loadByIncrementId($data[0]);
		
		if ($order->getId()) {
		    try {
				if($order->canShip()) {
					if ($data[1] == 'SEM RASTREAMENTO') {
						$_code = 'custom';
					} else {
						$_code = 'tracking';
					}
					
					$items = array();
					foreach($order->getAllItems() as $item) {
						$items[$item->getItemId()] = $item->getQtyOrdered();
					}
					$shipmentId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $items ,'ENTREGA CRIADA' ,false,1);
					$trackmodel = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId,$_code,'ENTREGA',$data[1]);
			    	$this->_success++;
				} else {
					$this->_errors++;
				}
		    } catch(Exception $e) {
				Mage::log('ERRO ENTREGAS: ' . $order->getId() . ' = ' . $e, null, 'av5_ordershipment.log');
		    	$this->_errors++;	
		    }
		} else {
			$this->_errors++;
		}
   }	
}