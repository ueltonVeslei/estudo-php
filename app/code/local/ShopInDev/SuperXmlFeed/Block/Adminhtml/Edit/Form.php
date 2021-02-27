<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	/**
	 * Init form
	 * @return void
	 */
	public function __construct(){

		parent::__construct();

		$this->setId('superxmlfeed_form');
		$this->setTitle(Mage::helper('superxmlfeed')->__('XML Feed Information'));

	}

	/**
	 * Retrieve generated editor code
	 * @param string $element
	 * @param int $height
	 * @return string
	 */
	protected function _retrieveGeneratedEditorCode($element, $height = 250){

		$editor .= '<script type="text/javascript">';
		$editor .= 'document.observe("dom:loaded", function() {';
		$editor .= 'var textarea = $("rule_xml_'. $element. '");';
		$editor .= 'var mirror = CodeMirror.fromTextArea(textarea, {lineNumbers: true, mode: "xml"});';
		$editor .= 'mirror.on("update", mirror.save);';
		$editor .= '});';
		$editor .= '</script>';

		return $editor;
	}

	/**
	 * Prepare form override
	 * @return mixed
	 */
	protected function _prepareForm(){

		$model = Mage::registry('superxmlfeed_xml');
		$form = new Varien_Data_Form(array(
			'id'        => 'edit_form',
			'action'    => $this->getData('action'),
			'method'    => 'post'
		));
		$form->setHtmlIdPrefix('rule_');

		// XML Options
		$options = $form->addFieldset('xml_form', array(
			'legend' => Mage::helper('superxmlfeed')->__('XML'),
			'class'  => 'fieldset-wide'
		));

		if( $model->getId() ){
			$options->addField('xml_id', 'hidden', array(
				'name' => 'xml_id',
			));
		}

		$options->addField('xml_filename', 'text', array(
			'label' => Mage::helper('superxmlfeed')->__('Filename'),
			'name'  => 'xml_filename',
			'required' => true,
			'note'  => Mage::helper('superxmlfeed')->__('example: feed.xml'),
			'value' => $model->getXmlFilename()
		));

		$options->addField('xml_path', 'text', array(
			'label' => Mage::helper('superxmlfeed')->__('Path'),
			'name'  => 'xml_path',
			'required' => true,
			'note'  => Mage::helper('superxmlfeed')->__('example: <b>sitemap/</b> or <b>/</b> for base path'),
			'value' => $model->getXmlPath()
		));

		$options->addField('xml_wrapper', 'textarea', array(
			'label' => Mage::helper('superxmlfeed')->__('XML Wrapper'),
			'name'  => 'xml_wrapper',
			'required' => true,
			'note'  => Mage::helper('superxmlfeed')->__("See the examples and docs on module package"),
			'value' => $model->getXmlWrapper(),
			'style' => 'height: 400px',
			'after_element_html' => $this->_retrieveGeneratedEditorCode('wrapper', 250)
		));

		$options->addField('xml_item', 'textarea', array(
			'label' => Mage::helper('superxmlfeed')->__('XML Item'),
			'name'  => 'xml_item',
			'required' => true,
			'note'  => Mage::helper('superxmlfeed')->__("See the examples and docs on module package"),
			'value' => $model->getXmlItem(),
			'style' => 'height: 400px',
			'after_element_html' => $this->_retrieveGeneratedEditorCode('item', 500)
		));

		if( !Mage::app()->isSingleStoreMode() ){

			$field = $options->addField('store_id', 'select', array(
				'label'    => Mage::helper('adminhtml')->__('Store View'),
				'title'    => Mage::helper('adminhtml')->__('Store View'),
				'name'     => 'store_id',
				'required' => true,
				'value'    => $model->getStoreId(),
				'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
			));

			$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
			$field->setRenderer($renderer);

		}else{

			$options->addField('store_id', 'hidden', array(
				'name'     => 'store_id',
				'value'    => Mage::app()->getStore(true)->getId()
			));

			$model->setStoreId(Mage::app()->getStore(true)->getId());

		}

		if( !$model->getStoreCurrency() ){
			$model->setStoreCurrency( Mage::app()->getStore(true)->getCurrentCurrencyCode() );
		}

		$currencies = Mage::app()->getStore()->getAvailableCurrencyCodes(true);

		if( is_array($currencies) AND count($currencies) > 1 ){

			$field = $options->addField('store_currency', 'select', array(
				'label'    => Mage::helper('adminhtml')->__('Store Currency'),
				'title'    => Mage::helper('adminhtml')->__('Store Currency'),
				'name'     => 'store_currency',
				'required' => true,
				'value'    => $model->getStoreCurrency(),
				'values'   => Mage::getSingleton('superxmlfeed/source_store_currency')->toOptionArray(),
			));

		}else{

			$options->addField('store_currency', 'hidden', array(
				'name'     => 'store_currency',
				'value'    => $model->getStoreCurrency()
			));

		}

		$options->addField('generate', 'hidden', array(
			'name'     => 'generate',
			'value'    => ''
		));

		// Filters
   		$filters = $form->addFieldset('conditions_fieldset', array(
			'legend' => Mage::helper('superxmlfeed')->__('XML Products Filters (leave blank for use all products)'),
			'class'  => 'fieldset-wide'
		));

        $filters->addField('conditions', 'text', array(
			'name' => 'conditions',
			'label' => Mage::helper('superxmlfeed')->__('Conditions'),
			'title' => Mage::helper('superxmlfeed')->__('Conditions'),
			'required' => true,
		))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

		$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('shopindev/superxmlfeed/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset'));
        $filters->setRenderer($renderer);

		$form->setValues($model->getData());
		$form->setUseContainer(true);
		$this->setForm($form);

		return parent::_prepareForm();
	}

}
