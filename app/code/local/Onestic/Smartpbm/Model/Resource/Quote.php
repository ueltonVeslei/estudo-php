<?php 
class Onestic_Smartpbm_Model_Resource_Quote extends Mage_Core_Model_Mysql4_Abstract {

    const QUOTE_STATUS_PENDING = 0;
    const QUOTE_STATUS_ADDED = 1;

	/**
	 * Construtor da classe
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct(){
        $this->_init('smartpbm/quote', 'id');
    }
    
    public function newQuote($data) {
        $write = $this->_getWriteAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/quote');
        $write->insert($table, $data);
    }
    
    public function getItems($quoteId) {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();
         
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/quote');
        $select = $read->select()->from($table);
         
        $select->where(
            $read->quoteInto("quote_id = ?", $quoteId)
        );
         
        $rows = $read->fetchAll($select);
         
        return $rows;
    }

    public function getPendingItems($quoteId) {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/quote');
        $select = $read->select()->from($table);

        $select->where(
            $read->quoteInto("quote_id = ?", $quoteId)
        );

        $select->where(
            $read->quoteInto("status = ?", self::QUOTE_STATUS_PENDING)
        );

        $rows = $read->fetchAll($select);

        return $rows;
    }

}