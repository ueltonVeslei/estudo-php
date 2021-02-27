<?php

class Intelipost_Push_Adminhtml_Push_TrackingsController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("push/trackings")->_addBreadcrumb(Mage::helper("adminhtml")->__("Trackings Manager"),Mage::helper("adminhtml")->__("Trackings Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Push"));
			    $this->_title($this->__("Manage Trackings"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Push"));
				$this->_title($this->__("Trackings"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("basic/trackings")->load($id);
				if ($model->getId()) {
					Mage::register("push_trackings_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("push/trackings");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Trackings Manager"), Mage::helper("adminhtml")->__("Trackings Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Trackings Description"), Mage::helper("adminhtml")->__("Trackings Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("push/adminhtml_trackings_edit"))->_addLeft($this->getLayout()->createBlock("push/adminhtml_trackings_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("push")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Push"));
		$this->_title($this->__("Trackings"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("basic/trackings")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("push_trackings_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("push/trackings");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Trackings Manager"), Mage::helper("adminhtml")->__("Trackings Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Trackings Description"), Mage::helper("adminhtml")->__("Trackings Description"));


		$this->_addContent($this->getLayout()->createBlock("push/adminhtml_trackings_edit"))->_addLeft($this->getLayout()->createBlock("push/adminhtml_trackings_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();
			Mage::log($post_data);	

				if ($post_data) {

					try {

						

						$model = Mage::getModel("basic/trackings")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Trackings were successfully saved"));
						Mage::getSingleton("adminhtml/session")->setPushTrackingsData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setPushTrackingsData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("basic/trackings");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		protected function _isAllowed()
		{
			$session = Mage::getSingleton('admin/session');
			$resourceId = $session->getData('acl')->get('admin/system/config/trackings')->getResourceId();
			return Mage::getSingleton('admin/session')->isAllowed($resourceId);
		}
}

