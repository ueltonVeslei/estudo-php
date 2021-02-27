<?php
class Biostore_Importean_Block_Index_Grid
 extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
    	echo 'contruindo grid';
    	die();
    	
        //parent::__construct();
         
        parent::__construct();
        $this->setId('importean_id')
        ->setDefaultSort('sku')
        ->setDefaultDir('DESC');
        //->setDefaultFilter(array('status' => 'R'))
        //->setSaveParametersInSession(true)
        //->setUseAjax(true);
        
        /*
        // Set some defaults for our grid
        $this->setDefaultSort('ean_id');
        $this->setId('importean_ean_grid');
        //$this->setUseAjax(true);
        //$this->setDefaultSort('sku');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setSubReportSize(true);
        */
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'importean/ean_collection';
    }
     
    protected function _prepareCollection()
    {
    	
    	//echo 'testec';
    	//die();
    	
        // Get and set our collection for the grid
        //$collection = Mage::getResourceModel($this->_getCollectionClass());
        
    	$collection = Mage::getModel('importean/ean')->getCollection();
    	
        
        echo '<pre>';
        var_dump($collection->getData());
       	echo '</pre>';
       	die();
        
        
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
    	/*
    	 	["ean_id"]=> string(5) "33410"
		    ["sku"]=> string(5) "16424"
		    ["ean"]=> string(10) "2147483647"
		    ["descricao"]=> string(39) "1 BETACAROTENO SUNDOWN CAPS 6000UI C/60"
		    ["principio_ativo"]=> string(37) "BETACAROTENO SUNDOWN CAPS 6000UI C/60"
		    ["fabricante"]=> string(16) "SUNDOWN NATURALS"
		    ["abc"]=> string(7) "NAO TEM"
		    ["dcb"]=> string(0) ""
    	*/
    	
    	$this->addColumn('ean_id', array(
    			'header' => $this->__('ID'),
    			'index' => 'ean_id',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
        
        //$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('sales')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('id');
    	$this->getMassactionBlock()->setFormFieldName('skux');
    	$this->getMassactionBlock()->setUseSelectAll(true);
        	
    	return $this;
    }
     
    public function getRowUrl($row)
    {
    	
    	//var_dump($row->getOrderId());
    	//die();
    	
    	$retorno = $taxa_default = Mage::helper('maisplics')->pegarPedido($row->getOrderId());
    	return $this->getUrl('*/sales_order/view', array('order_id' => $retorno));
    	//return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}