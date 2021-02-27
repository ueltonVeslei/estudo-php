<?php

class Intelipost_Push_Block_Adminhtml_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

        public function __construct()
        {
                parent::__construct();
                $this->setId("pushOrdersGrid");
                $this->setDefaultSort("id");
                $this->setDefaultDir("DESC");
                $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection()
        {
                $allowedStatus = Mage::getStoreConfig('intelipost_push/manage_ordes/order_status');
                if (strpos($allowedStatus, ',') > 0) {
                    $allowedStatus = explode(',', $allowedStatus);
                }

                $collection = Mage::getModel("basic/orders")->getCollection()
                ->join(array('sfo' => 'sales/order'), 'main_table.order_id = sfo.entity_id', array(
                    'customer_group_id',
                    'sfo.created_at' => 'sfo.created_at',
                    'sfo.increment_id' => 'sfo.increment_id',
                    'grand_total',
                    'shipping_description',
                    'sfo.status' => 'sfo.status'
                ))
                ->join(array('cg' => 'customer/customer_group'), 'sfo.customer_group_id = cg.customer_group_id', array('cg.customer_group_code' => 'cg.customer_group_code'))
                ->join(array('sfoa' => 'sales/order_address'), 'sfo.entity_id = sfoa.parent_id', array(
                    'city'       => 'city',
                    'country_id' => 'country_id'
                ));

                if (Mage::getStoreConfig('intelipost_push/manage_ordes/nfe_required'))
                {
                    $collection->join(array('nfe' => 'basic/nfes'), 'sfo.increment_id = nfe.increment_id', 
                    array( 'number' => 'number')
                    );
                }

                $collection->getSelect()->where("sfoa.address_type = 'shipping'");

                $expression = "sfo.status";
                //$condition = $collection->getConditionSql($expression, array("in"=>$allowedStatus));
                $collection->addFieldToFilter($expression, array("in"=>$allowedStatus));
                $this->setCollection($collection);
                return parent::_prepareCollection();
        }

        protected function _prepareColumns()
        {           
                $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
                foreach($orderStatusCollection as $orderStatus) 
                {
                    $status[] = array (
                        'value' => $orderStatus['status'], 'label' => $orderStatus['label']
                    );
                }

                $this->addColumn("id", array(
                "header" => Mage::helper("push")->__("id"),
                "align" =>"right",
                "width" => "20px",
                "type" => "number",
                "index" => "id",
                ));

                $this->addColumn('increment_id', array(
                    'header' => $this->__('Order #'),
                    'index'  => 'sfo.increment_id',
                    'filter_index' => 'sfo.increment_id'

                ));
         
                $this->addColumn('purchased_on', array(
                    'header' => $this->__('Purchased On'),
                    'type'   => 'datetime',
                    'index'  => 'sfo.created_at',
                    'filter_index' => 'sfo.created_at'
                ));
         
               /* $this->addColumn('city', array(
                    'header' => $this->__('City'),
                    'index'  => 'city'
                ));                    
                
                
                $this->addColumn('customer_group', array(
                    'header' => $this->__('Customer Group'),
                    'index'  => 'cg.customer_group_code',
                    'filter_index' => 'cg.customer_group_code'
                ));*/
         
                $this->addColumn('grand_total', array(
                    'header'        => $this->__('Grand Total'),
                    'index'         => 'grand_total',
                    'type'          => 'currency'
                    // 'currency_code' => $currency
                ));
         
                $this->addColumn('shipping_description', array(
                    'header' => $this->__('Shipping Description'),
                    'index'  => 'shipping_description'
                ));

                $this->addColumn('delivery_method_id', array(
                    'header'       => $this->__('Delivery Method Id'),
                    'index'        => 'delivery_method_id'            
                ));
         
                $this->addColumn('delivery_quote_id', array(
                    'header'       => $this->__('Delivery Quote Id'),
                    'index'        => 'delivery_quote_id'            
                ));
                
                 $this->addColumn('status_magento', array(
                    'header'   => $this->__('Status Magento'),
                    'type'    => 'options',
                    'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
                    'filter_index' => 'sfo.status',
                    'index'    => 'sfo.status'
                ));
                 
                $this->addColumn('status', array(
                    'header'  => $this->__('Status Intelipost'),
                    "width" => "60px",
                    'index'   => 'status',
                    'filter_index' => 'main_table.status',
                    'type'    => 'options',
                    'options' => array(
                        'waiting'        => $this->__('Waiting'),
                        'created'        => $this->__('Created'),
                        'shipment ready' => $this->__('Ready For Shipment'),
                        'shipped'        => $this->__('Shipped')
                    ),
                ));

                $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getOrderId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'adminhtml/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
                                
             // $this->addRssList('push/adminhtml_rss_rss/orders', Mage::helper('push')->__('RSS'));
            // $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
            // $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

                return parent::_prepareColumns();
        }

        public function _getRowUrl($row)
        {
               return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

        
        protected function _prepareMassaction()
        {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('order_id');
            $this->getMassactionBlock()->setUseSelectAll(true);

            $this->getMassactionBlock()->addItem('send', array(
                     'label'=> Mage::helper('push')->__('Create Orders'),
                     'url'  => $this->getUrl('*/push_orders/send'),
                ));          
               
            $this->getMassactionBlock()->addItem('readyForShipment', array(
                     'label'=> Mage::helper('push')->__('Ready For Shipment'),
                     'url'  => $this->getUrl('*/push_orders/readyForShipment'),
                ));

            $this->getMassactionBlock()->addItem('shipment', array(
                     'label'=> Mage::helper('push')->__('Generate Shipments'),
                     'url'  => $this->getUrl('*/push_orders/shipment'),
                ));   

            $this->getMassactionBlock()->addItem('romaneio', array(
                     'label'=> Mage::helper('push')->__('Generate Romaneio'),
                     'url'  => $this->getUrl('*/push_orders/romaneio'),
                ));    
            return $this;
        }
            

}

