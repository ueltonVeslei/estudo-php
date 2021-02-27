<?php
class Av5_Correiospro_Adminhtml_PromotionController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function newAction() {
		$this->_forward('edit');
	}
	
	public function editAction() {
		$id = $this->getRequest()->getParam('id', null);
		$model = Mage::getModel('av5_correiospro/promos');
		if ($id) {
			$model->load((int) $id);
			if ($model->getId()) {
				$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
				if ($data) {
					$model->setData($data)->setId($id);
				}
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('av5_correiospro')->__('Regra Inexistente'));
				$this->_redirect('*/*/');
			}
		}
		Mage::register('correiospromo_data', $model);
		$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
		
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->renderLayout();
	}
	
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('av5_correiospro/promos');
			$id = $this->getRequest()->getParam('id');
			if ($id) {
				$model->load($id);
			}
			$data['conditions'] = $data['rule']['conditions'];
			unset($data['rule']);
			
			$validateResult = $model->validateData(new Varien_Object($data));
			
			$model->loadPost($data);
		
			Mage::getSingleton('adminhtml/session')->setFormData($data);
			try {
				$model->save();
		
				if (!$model->getId()) {
					Mage::throwException(Mage::helper('av5_correiospro')->__('Erro ao salvar regra'));
				}
		
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('av5_correiospro')->__('Regra salva com sucesso.'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
		
				// The following line decides if it is a "save" or "save and continue"
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
				} else {
					$this->_redirect('*/*/');
				}
		
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				if ($model && $model->getId()) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
				} else {
					$this->_redirect('*/*/');
				}
			}
		
			return;
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('av5_correiospro')->__('Nenhum dado informado'));
		$this->_redirect('*/*/');
	}
	
	public function deleteAction() {
		if ($id = $this->getRequest()->getParam('id')) {
			try {
				$model = Mage::getModel('av5_correiospro/promos');
				$model->setId($id);
				$model->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('av5_correiospro')->__('Regra excluída com sucesso.'));
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Regra não encontrada para exclusão.'));
		$this->_redirect('*/*/');
	}
	
	public function newConditionHtmlAction(){
	
		$id = $this->getRequest()->getParam('id');
		$typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
		$type = $typeArr[0];
	
		$model = Mage::getModel($type)
			->setId($id)
			->setType($type)
			->setRule(Mage::getModel('salesrule/rule'))
			->setPrefix('conditions');
	
		if( !empty($typeArr[1]) ){
			$model->setAttribute($typeArr[1]);
		}
	
		if( $model instanceof Mage_Rule_Model_Condition_Abstract ){
			$model->setJsFormObject($this->getRequest()->getParam('form'));
			$html = $model->asHtmlRecursive();
		} else {
			$html = '';
		}
	
		$this->getResponse()->setBody($html);
	}
	
	public function _isAllowed() {
	    return true;
	}
}