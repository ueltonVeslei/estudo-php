<?php 
class Onestic_Performa_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('onestic_performa/queue', 'id');
    }
    
    public function cleanDatabase() {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('onestic_performa/queue');
    
    	$rows = $write->delete($table, "1");
    }
    
}