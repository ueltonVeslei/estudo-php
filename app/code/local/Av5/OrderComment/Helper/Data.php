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
 * @package    Av5_OrderComment
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Av5_OrderComment_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getConfigData($node)
	{
		return Mage::getStoreConfig('av5_ordercomment/general/' . $node);
	}
	
	public function loadFile() {
		$file = Mage::getModel('core/session')->getImportfile();
		$result = false;
		if ($file) {
			$path = Mage::getBaseDir('var') . DS . 'import' . DS;
			$result = file($path . $file);
		}
	
		return $result;
	}
	
	public function getPointer() {
		$pointer = Mage::getModel('core/session')->getImportPointer();
		return (($pointer) ? $pointer : 1);
	}
	
	public function setPointer($current) {
		Mage::getModel('core/session')->setImportPointer($current);
	}
		
}