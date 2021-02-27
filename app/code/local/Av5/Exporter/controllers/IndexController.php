<?php
class Av5_Exporter_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$model = Mage::getModel('av5_exporter/processor');
		$model->show($this->getRequest()->getParam('product'));
	}

}