<?php 
class Onestic_Smartpbm_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract {
    
	/**
	 * Construtor da classe
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct(){
        $this->_init('smartpbm/order', 'id');
    }
    
    public function newOrder($data) {
        $write = $this->_getWriteAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/order');

        $orderItems = $this->getItems($data['order_id']);
        $found = false;
        foreach ($orderItems as $item) {
            if ($item['product_id'] == $data['product_id']) {
                $found = $item['id'];
                break;
            }
        }

        if (!$found) {
            $write->insert($table, $data);
        } else {
            $write->update($table, $data, "id = " . $found);
        }
    }
    
    public function getItems($orderId) {
        $read = $this->_getReadAdapter();

        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/order');
        $select = $read->select()->from($table);
         
        $select->where(
            $read->quoteInto("order_id = ?", $orderId)
        );
         
        $rows = $read->fetchAll($select);
         
        return $rows;
    }

    public function getPbms($orderId) {
        $read = $this->_getReadAdapter();

        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/order');
        $select = $read->select()->from($table, 'distinct(pbm)');

        $select->where(
            $read->quoteInto("order_id = ?", $orderId)
        );

        $rows = $read->fetchAll($select);

        return $rows;
    }

    public function saveTransactionInfo($orderId, $pbm, $transactionInfo) {
        $write = $this->_getWriteAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('smartpbm/order');
        $write->update($table, ['transaction_info' => json_encode($transactionInfo)], "order_id = " . $orderId . " AND pbm = '" . $pbm . "'");
    }

}