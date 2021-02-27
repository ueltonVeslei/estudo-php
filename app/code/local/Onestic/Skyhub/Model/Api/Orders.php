<?php
class Onestic_Skyhub_Model_Api_Orders extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'orders';
    
    public function getCollection($params) {
        return $this->get($this->_endpoint,$params);
    }
    
    public function getOrder($id) {
        return $this->get($this->_endpoint . '/' . $id . '/');
    }
    
    public function create($data) {
        return $this->post($this->_endpoint,$data);
    }
    
    public function approval($order_id) {
        return $this->post($this->_endpoint . '/' . $order_id . '/approval');
    }
    
    public function cancel($order_id) {
        return $this->post($this->_endpoint . '/' . $order_id . '/cancel');
    }
    
    public function delivery($order_id) {
        return $this->post($this->_endpoint . '/' . $order_id . '/delivery');
    }
    
    public function invoice($order_id, $key) {
        $data = array('invoice' => array('key' => $key));
        return $this->post($this->_endpoint . '/' . $order_id . '/invoice',$data);
    }
    
    public function shipments($order_id, $data) {
        return $this->post($this->_endpoint . '/' . $order_id . '/shipments',$data);
    }
    
    public function exported($order_id) {
        //return $this->put($this->_endpoint . '/' . $order_id . '/exported',array('exported' => true));
        return [
            'httpCode'  => 200,
            'body'      => ''
        ];
    }
    
    public function getShipmentLabel($id) {
    	return $this->get($this->_endpoint . '/' . $id . '/shipment_labels');
    }
    
}