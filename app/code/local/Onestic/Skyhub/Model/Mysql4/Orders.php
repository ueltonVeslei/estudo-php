<?php 
class Onestic_Skyhub_Model_Mysql4_Orders extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('onestic_skyhub/orders', 'id');
    }
    
    public function cleanDatabase() {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('onestic_skyhub/orders');
    
    	$rows = $write->delete($table, "1");
    }
    
    public function statusChange() {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('onestic_skyhub/orders');
    	
    	$select = $read->select()->from($table);
    	$select->where("status_skyhub = 'Pagamento Pendente'");
    	
    	return $read->fetchAll($select);
    }
    
}