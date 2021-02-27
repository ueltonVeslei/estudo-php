<?php 
class Onestic_Skyhub_Model_Mysql4_Products extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('onestic_skyhub/products', 'id');
    }
    
    public function cleanDatabase() {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('onestic_skyhub/products');
    
    	$rows = $write->delete($table, "1");
    }
    
}