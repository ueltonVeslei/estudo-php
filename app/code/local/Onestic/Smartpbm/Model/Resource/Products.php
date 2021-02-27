<?php 
class Onestic_Smartpbm_Model_Resource_Products extends Mage_Core_Model_Mysql4_Abstract {
    
	/**
	 * Construtor da classe
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct(){
        $this->_init('smartpbm/products', 'id');
    }

    public function getPbms($productId) {
    	$read = $this->_getReadAdapter();
    	$write = $this->_getWriteAdapter();
    	
    	$table = Mage::getSingleton('core/resource')->getTableName('smartpbm/products');
    	$select = $read->select()->from($table);
    	
    	$select->where($read->quoteInto("product_id = ?", $productId));
    	
    	$return = array();
    	$rows = $read->fetchAll($select);
    	foreach($rows as $row) {
            $return[] = $row['pbm'];
    	}
    	
    	return $return;
    }
    
    public function checkProgram($productId, $pbm) {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();
         
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/products');
        $select = $read->select()->from($table);
         
        $select->where(
            $read->quoteInto("product_id = ?", $productId).
            $read->quoteInto(" AND pbm like ?", $pbm)
        );
        
        $row = $read->fetchRow($select);
        if ($row['id']) {
            return true;
        }
         
        return false;
    }
    
    public function newRelation($product, $pbm) {
        $write = $this->_getWriteAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/products');
        $write->insert($table, array(
            'pbm' 			=> $pbm,
            'product_id'    => $product,
            'updated_at'    => date('Y-m-d H:i:s')
        ));
    }

    public function truncate() {
        $this->_getWriteAdapter()->query('TRUNCATE TABLE '.$this->getMainTable());
        return $this;
    }
    
}