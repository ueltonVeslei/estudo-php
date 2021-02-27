<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Xml extends Mage_Rule_Model_Rule {

	/**
	 * Real file path
	 * @var string
	 */
	protected $_filePath;

	/**
	 * Init model
	 * @return void
	 */
	protected function _construct(){
		$this->_init('superxmlfeed/xml');
	}

    /**
     * Getter for rule conditions collection
     * @return object
     */
    public function getConditionsInstance(){
        return Mage::getModel('superxmlfeed/rule_condition_combine');
    }

	/**
	 * Before save override
	 * @return mixed
	 */
	protected function _beforeSave(){

		$io = new Varien_Io_File();
		$realPath = $io->getCleanPath(Mage::getBaseDir(). '/'. $this->getXmlPath());

		// Check path is allow
		if( !$io->allowedPath($realPath, Mage::getBaseDir()) ){
			Mage::throwException(Mage::helper('superxmlfeed')->__('Please define correct path'));
		}

		// Check exists and writeable path
		if( !$io->fileExists($realPath, false) ){
			Mage::throwException(Mage::helper('superxmlfeed')->__('Please create the specified folder "%s" before saving the xml.', Mage::helper('core')->escapeHtml($this->getXmlPath())));
		}

		if( !$io->isWriteable($realPath) ){
			Mage::throwException(Mage::helper('superxmlfeed')->__('Please make sure that "%s" is writable by web-server.', $this->getXmlPath()));
		}

		// Check allow filename
		if( !preg_match('#^[a-zA-Z0-9_\-\.]+$#', $this->getXmlFilename()) ){
			Mage::throwException(Mage::helper('superxmlfeed')->__('Please use only letters (a-z or A-Z), numbers (0-9), dash (-) or underscore (_) in the filename. No spaces or other characters are allowed.'));
		}

		if( !preg_match('#\.xml$#', $this->getXmlFilename()) ){
			$this->setXmlFilename($this->getXmlFilename(). '.xml');
		}

		$this->setXmlPath(rtrim(str_replace(str_replace('\\', '/', Mage::getBaseDir()), '', $realPath), '/') . '/');

		parent::_beforeSave();
        return $this;
	}

	/**
	 * Return real file path
	 * @return string
	 */
	protected function getPath(){

		if( is_null($this->_filePath) ){
			$this->_filePath = str_replace('//', '/', Mage::getBaseDir().
				$this->getXmlPath());
		}

		return $this->_filePath;
	}

	/**
	 * Return full file name with path
	 * @return string
	 */
	public function getPreparedFilename(){
		return $this->getPath(). $this->getXmlFilename();
	}

	/**
	 * Generate XML file
	 * @return ShopInDev_SuperXmlFeed_Model_Xml
	 */
	public function generateXml(){

		ini_set('max_execution_time', -1);
		
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $this->getPath()));

		if( $io->fileExists($this->getXmlFilename())
			AND !$io->isWriteable($this->getXmlFilename()) ){

			Mage::throwException(Mage::helper('superxmlfeed')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getXmlFilename(), $this->getPath()));

		}

		$io->streamOpen($this->getXmlFilename());

		$defaultStoreId = Mage::app()->getStore()->getId();
		$defaultCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$storeId = $this->getStoreId();
		$storeCurrency = $this->getStoreCurrency();
		$shouldEmulate = ( $storeId AND $storeId > 1 ) ? TRUE : FALSE;
		
		if( $shouldEmulate ){
			$appEmulation = Mage::getSingleton('core/app_emulation');
			$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		}

		if( $storeCurrency ){
			Mage::app()->getStore()->setCurrentCurrencyCode($storeCurrency);
		}

		$template = Mage::getModel('superxmlfeed/template');
		$template->setTemplateXml($this);
		$template->setTemplateStore($storeId, $storeCurrency);

		// Products
		$productsCollection = Mage::getModel('catalog/product')->getCollection();
		$productsCollection->setStore($storeId);
		$productsCollection->addStoreFilter($storeId);
		$productsCollection->addAttributeToFilter('status', 1);
		$productsCollection->addAttributeToSelect('*');
		$productsCollection->setPageSize(40);
		$productsCollection->setFlag('require_stock_items', true);

		$pages = $productsCollection->getLastPageNumber();
		$currentPage = 1;
		$itemsXML = '';
		$fullXML = '';

		do {

			$productsCollection->setCurPage($currentPage);
			$productsCollection->load();

			foreach( $productsCollection as $_product ){

				// Check for rules
				if( !$this->getConditions()->validate($_product) ){
					continue;
				}

				$template->setTemplateProduct($_product);
				$itemsXML .= $template->createXmlItem();

			}

			$currentPage++;
			$productsCollection->clear();

		} while ($currentPage <= $pages);

		// Wrapper
		$template->getTemplateXml()->setXmlItems($itemsXML);
		$fullXML = $template->createXmlWrapper();

		// Compress Whitespace & Remove Comments
		$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s', '/<!\-{2}[\s\S]*?\-{2}>/');
    	$replace = array('>','<','\\1', '');
		$fullXML = preg_replace($search, $replace, $fullXML);

		$io->streamWrite($fullXML);
		$io->streamClose();

		$this->setXmlTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
		$this->save();
		
		if( $storeCurrency ){
			Mage::app()->getStore()->setCurrentCurrencyCode($defaultCurrency);
		}

		if( $shouldEmulate ){
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		}

		return $this;
	}

}