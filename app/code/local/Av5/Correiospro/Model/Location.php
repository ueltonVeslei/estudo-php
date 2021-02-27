<?php
class Av5_Correiospro_Model_Location extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('av5_correiospro/location');
	}
	
	public function getRegionsByState() {
		$collection = $this->getCollection()
			->addFieldToSelect('state')
			->addExpressionFieldToSelect('regions','COUNT({{city}})','city');
		$collection->getSelect()
			->group('state');
		
		return $collection;
	}
	
	public function populate() {
		Mage::helper('av5_correiospro')->log('POPULANDO TABELA DE CEPS');
		$resource = Mage::getSingleton('core/resource');
		$writeAdapter = $resource->getConnection('core_write');
		$inserts = file(dirname(__FILE__).'/data/ceps.sql');
		foreach ($inserts as $insert) {
			$writeAdapter->query($insert);
		}
		Mage::helper('av5_correiospro')->log('TABELA DE CEPS POPULADA COM SUCESSO');
	}
	
}
