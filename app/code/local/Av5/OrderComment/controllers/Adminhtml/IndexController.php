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
 * @category   Sales (Order)
 * @package    Av5_OrderComment
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Av5_OrderComment_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function postAction() {
		try {
			if (!empty($_FILES['arquivo']['name'])) {
				$fname = 'Comentarios-' . date('d-m-Y-H-i') . ".csv";
				$uploader = new Varien_File_Uploader('arquivo');
		
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(false);
		
				$path = Mage::getBaseDir('var') . DS . 'import' . DS;
		
				$uploader->save($path, $fname);
				$file = $path . $fname;
				
				Mage::getModel('core/session')->setImportfile($fname);
				Mage::getModel('core/session')->setImportPointer(1);
				Mage::getModel('core/session')->setImport(true);
			} else {
				Mage::getModel('core/session')->setImport(false);
			}
			$this->_redirect('*/*/progress');
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			Mage::getModel('core/session')->setImport(false);
			$this->_redirect('*/*');
		}
	}
	
	public function processAction() {
		$model = Mage::getModel('av5_ordercomment/processor');
		$result = $model->process();
		echo json_encode($result);
	}
	
	public function progressAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function _isAllowed() {
	    return true;
	}
}