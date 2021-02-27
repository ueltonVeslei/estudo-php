<?php
class Av5_Correiospro_Adminhtml_UpdateController extends Mage_Adminhtml_Controller_Action {
	
    public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function postAction() {
		$state = $this->getRequest()->getParam('state',null);
		$result = Mage::getModel('av5_correiospro/updater')->update($state);
		$outdated = Mage::getModel('av5_correiospro/price')->getOutdated($state);

		$this->getResponse()->setBody(json_encode(array('total'=>$outdated,'success'=>$result['success'],'errors'=>$result['errors'])));
	}
	
	public function populateAction() {
		$last = $this->getRequest()->getParam('last');
		$model = Mage::getModel('av5_correiospro/updater');
		$result = $model->populate($last);
		$totalLocations = Mage::getModel('av5_correiospro/location')->getCollection()->getSize();

		$this->getResponse()->setBody(json_encode(array('last'=>$result,'locations'=>$totalLocations)));
	}
	
	public function cleanAction() {
		try {
			Mage::getResourceModel('av5_correiospro/location')->truncate();
			Mage::getResourceModel('av5_correiospro/price')->truncate();
			Mage::getSingleton('adminhtml/session')->addSuccess("Banco de dados limpo com sucesso!");
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}
	
	public function _isAllowed() {
	    return true;
	}
}