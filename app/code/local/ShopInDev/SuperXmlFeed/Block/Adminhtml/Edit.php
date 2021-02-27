<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	/**
	 * Init container
	 * @return void
	 */
	public function __construct(){

		$this->_objectId = 'xml_id';
		$this->_blockGroup = 'superxmlfeed';
		$this->_controller = 'adminhtml';

		parent::__construct();

		$this->_addButton('generate', array(
			'label'   => Mage::helper('superxmlfeed')->__('Save & Generate'),
			'onclick' => "$('rule_generate').value=1; editForm.submit();",
			'class'   => 'add',
		));
	}

	/**
	 * Get edit form container header text
	 * @return string
	 */
	public function getHeaderText(){

		if( Mage::registry('superxmlfeed_xml')->getId() ){
			return Mage::helper('superxmlfeed')->__('Edit XML Feed');
		}

		return Mage::helper('superxmlfeed')->__('New XML Feed');
	}

	/**
     * Get form action URL
     * @return string
     */
    public function getFormActionUrl(){

        if( $this->hasFormActionUrl() ){
            return $this->getData('form_action_url');
        }

        return $this->getUrl('*/superxmlfeed/save');
    }

}
