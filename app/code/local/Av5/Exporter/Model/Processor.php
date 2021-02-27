<?php
class Av5_Exporter_Model_Processor extends Mage_Core_Model_Abstract
{

	protected $_data;
	protected $_cdata = array();
	protected $_prefix = "";
	protected $_suffix = "";
	protected $_nodePrefix = "";
	protected $_nodeSuffix = "";

	protected function _process($productIds) {
		$fields = Mage::helper('av5_exporter')->getConfigData('fields');
		$lines[] = str_replace(',', ';', $fields);
		$fields = explode(',', $fields);

		$collection = Mage::getModel('catalog/product')->getCollection();
		foreach ($fields as $field) {
			$collection->addAttributeToSelect($field);
		}

		if (!is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		$collection->addFieldToFilter('entity_id',array('in' => $productIds));

		foreach ($collection as $product) {
			$line = [];
			foreach ($fields as $field) {
			    if (in_array($field, ['image', 'small_image', 'base_image'])) {
                    $line[] = Mage::helper('catalog/image')->init($product, 'image');
                } elseif($field == 'media_gallery') {
                    $_product = Mage::getModel('catalog/product')->load($product->getId());
                    $images = [];
                    foreach ($_product->getMediaGalleryImages() as $image) {
                        $images[] = $image->getUrl();
                    }
                    $line[] = implode(',', $images);
                } elseif ($field == 'qty') {
                    $stockItem = Mage::getModel('cataloginventory/stock_item');
                    $stockItem->getResource()->loadByProductId($stockItem, $product->getId());
                    $data = $stockItem->setOrigData();
                    $line[] = $data->getQty();
                } else {
                    $line[] = $product->getData($field);
                }
			}
			$lines[] = implode(';', $line);
		}

		return implode(PHP_EOL,$lines);
	}

	public function show($productIds) {
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=produtos_".date('d-m-Y').".csv");
		echo $this->_process($productIds);
	}
}