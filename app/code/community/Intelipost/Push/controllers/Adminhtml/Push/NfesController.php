<?php

class Intelipost_Push_Adminhtml_Push_NfesController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("push/nfes")->_addBreadcrumb(Mage::helper("adminhtml")->__("NFEs Manager"),Mage::helper("adminhtml")->__("NFEs Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Push"));
			    $this->_title($this->__("Manage NFEs"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Push"));
				$this->_title($this->__("NFE"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("basic/nfes")->load($id);
				if ($model->getId()) {
					Mage::register("push_nfes_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("push/nfes");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NFEs Manager"), Mage::helper("adminhtml")->__("NFEs Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NFEs Description"), Mage::helper("adminhtml")->__("NFEs Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("push/adminhtml_nfes_edit"));
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
		$this->_title($this->__("NFE"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("basic/nfes")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("push_nfes_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("push/nfes");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NFEs Manager"), Mage::helper("adminhtml")->__("NFEs Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NFEs Description"), Mage::helper("adminhtml")->__("NFEs Description"));


		$this->_addContent($this->getLayout()->createBlock("push/adminhtml_nfes_add"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();

				if ($post_data) {

					try {
							if (array_key_exists('push_nfes', $post_data))
							{
								foreach ($post_data['push_nfes'] as $data) 
								{									
									$model = Mage::getModel("basic/nfes")
									->addData($data)
									->setId($this->getRequest()->getParam("id"))
									->save();
								}
							}
							else
							{
								$model = Mage::getModel("basic/nfes")
									->addData($post_data)
									->setId($this->getRequest()->getParam("id"))
									->save();
							}
							

							Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("NFEs were successfully saved"));
							Mage::getSingleton("adminhtml/session")->setPushNfesData(false);

							if ($this->getRequest()->getParam("back")) {
								$this->_redirect("*/*/edit", array("id" => $model->getId()));
								return;
							}
							$this->_redirect("*/*/");
							return;
						} 
						catch (Exception $e) {
							Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
							Mage::getSingleton("adminhtml/session")->setPushNfesData($this->getRequest()->getPost());
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
						$model = Mage::getModel("basic/nfes");
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
			$resourceId = $session->getData('acl')->get('admin/system/config/nfes')->getResourceId();
			return Mage::getSingleton('admin/session')->isAllowed($resourceId);
		}
}

