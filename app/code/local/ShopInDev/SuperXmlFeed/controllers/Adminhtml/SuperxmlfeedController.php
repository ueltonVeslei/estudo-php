<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Adminhtml_SuperxmlfeedController extends Mage_Adminhtml_Controller_Action{

	/**
	 * Check the permission to run it
	 * @return boolean
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('catalog/superxmlfeed');
	}

	/**
	 * Init actions
	 * @return object
	 */
	protected function _initAction(){

		$this->loadLayout()
			->_setActiveMenu('catalog/superxmlfeed')
			->_addBreadcrumb(
				Mage::helper('catalog')->__('Catalog'),
				Mage::helper('catalog')->__('Catalog'))
			->_addBreadcrumb(
				Mage::helper('superxmlfeed')->__('XML Feeds'),
				Mage::helper('superxmlfeed')->__('XML Feeds'));

		return $this;
	}

	/**
	 * Index action
	 * @return void
	 */
	public function indexAction(){

		$this->_title($this->__('Catalog'))->_title($this->__('XML Feeds'));

		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('superxmlfeed/adminhtml_index'))
			->renderLayout();
	}

	/**
	 * Create new XML
	 * @return void
	 */
	public function newAction(){
		$this->_forward('edit');
	}

	/**
	 * Edit XML
	 * @return void
	 */
	public function editAction(){

		$this->_title($this->__('Catalog'))->_title($this->__('XML Feeds'));

		$id = $this->getRequest()->getParam('xml_id');
		$model = Mage::getModel('superxmlfeed/xml');

		if( $id ){
			$model->load($id);

			if( !$model->getId() ){
				Mage::getSingleton('adminhtml/session')->addError(
					Mage::helper('superxmlfeed')->__('This XML no longer exists.'));
				$this->_redirect('*/*/');
				return;
			}
		}

		$this->_title($model->getId() ? $model->getXmlFilename() : $this->__('New XML Feed'));

		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);

		if( !empty($data) ){
			$model->setData($data);
		}

		$model->getConditions()->setJsFormObject('rule_conditions_fieldset');

		Mage::register('superxmlfeed_xml', $model);

		$breadcrumb = $id ? Mage::helper('superxmlfeed')->__('Edit XML Feed') : Mage::helper('superxmlfeed')->__('New XML Feed');

		$this->_initAction()
			->_addBreadcrumb($breadcrumb, $breadcrumb)
			->_addContent($this->getLayout()->createBlock('superxmlfeed/adminhtml_edit'))
			->renderLayout();
	}

	/**
	 * New conditions HTML action
	 * @return mixed
	 */
	public function newConditionHtmlAction(){

		$id = $this->getRequest()->getParam('id');
		$typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
		$type = $typeArr[0];

		$model = Mage::getModel($type)
			->setId($id)
			->setType($type)
			->setRule(Mage::getModel('catalogrule/rule'))
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

	/**
	 * Save action
	 * @return void
	 */
	public function saveAction(){

		$data = $this->getRequest()->getPost();

		if( !$data ){
			$this->_redirect('*/*/');
			return;
		}

		$model = Mage::getModel('superxmlfeed/xml');

		// Validate path to generate
		if( !empty($data['xml_filename']) && !empty($data['xml_path']) ){

			$path = rtrim($data['xml_path'], '\\/'). DS. $data['xml_filename'];

			$validator = Mage::getModel('core/file_validator_availablePath');
			$helper = Mage::helper('core');
			$validator->setPaths($helper->getPublicFilesValidPath());

			if( !$validator->isValid($path) ){

				foreach ($validator->getMessages() as $message) {
					Mage::getSingleton('adminhtml/session')->addError($message);
				}

				Mage::getSingleton('adminhtml/session')->setFormData($data);

				$this->_redirect('*/*/edit', array(
					'xml_id' => $this->getRequest()->getParam('xml_id'))
				);

				return;
			}

		}

		if( $this->getRequest()->getParam('xml_id') ){

			$model->load( $this->getRequest()->getParam('xml_id') );

			if( $model->getXmlFilename() && file_exists($model->getPreparedFilename()) ){
				unlink($model->getPreparedFilename());
			}

		}

		try {

			$data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);

            $model->loadPost($data);
			// $model->setData($data);
			$model->save();

			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('superxmlfeed')->__('The XML has been saved.'));

			Mage::getSingleton('adminhtml/session')->setFormData(false);

			if( $this->getRequest()->getParam('back') ){
				$this->_redirect('*/*/edit', array('xml_id' => $model->getId()));
				return;
			}

			if( $this->getRequest()->getParam('generate') ){
				$this->getRequest()->setParam('xml_id', $model->getId());
				$this->_forward('generate');
				return;
			}

			$this->_redirect('*/*/');
			return;

		} catch (Exception $e) {

			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			Mage::getSingleton('adminhtml/session')->setFormData($data);

			$this->_redirect('*/*/edit', array(
				'xml_id' => $this->getRequest()->getParam('xml_id'))
			);

			return;
		}

		$this->_redirect('*/*/');

	}

	/**
	 * Delete action
	 * @return void
	 */
	public function deleteAction(){

		$id = $this->getRequest()->getParam('xml_id');

		if( !$id ){

			Mage::getSingleton('adminhtml/session')->addError(
			Mage::helper('superxmlfeed')->__('Unable to find a XML to delete.'));

			$this->_redirect('*/*/');
			return;

		}

		try {

			$model = Mage::getModel('superxmlfeed/xml');
			$model->setId($id);
			$model->load($id);

			if( $model->getXmlFilename() && file_exists($model->getPreparedFilename()) ){
				unlink($model->getPreparedFilename());
			}

			$model->delete();

			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('superxmlfeed')->__('The XML has been deleted.'));

			$this->_redirect('*/*/');
			return;

		} catch (Exception $e) {

			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

			$this->_redirect('*/*/edit', array('xml_id' => $id));
			return;
		}

	}

	/**
	 * Generate XML feed
	 * @return void
	 */
	public function generateAction(){

		$id = $this->getRequest()->getParam('xml_id');
		$xml = Mage::getModel('superxmlfeed/xml');
		$xml->load($id);

		if( $xml->getId() ){

			try {
				$xml->generateXml();
				$this->_getSession()->addSuccess(
					Mage::helper('superxmlfeed')->__("The XML '%s' has been generated.", $xml->getXmlFilename()));

			}catch(Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());

			}catch(Exception $e) {
				$this->_getSession()->addError($e->getMessage());
				$this->_getSession()->addException($e,
					Mage::helper('superxmlfeed')->__('Unable to generate the XML.'));
			}

		} else {
			$this->_getSession()->addError(
				Mage::helper('superxmlfeed')->__('Unable to find a XML to generate.'));
		}

		$this->_redirect('*/*/');
	}

}