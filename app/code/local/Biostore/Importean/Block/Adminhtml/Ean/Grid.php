<?php
class Biostore_Importean_Block_Adminhtml_Ean_Grid
 extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {	
        //parent::__construct();
         
        parent::__construct();
        $this->setId('importean_id')
        ->setDefaultSort('description')
        ->setDefaultDir('DESC')
        //->setDefaultFilter(array('status' => 'R'))
        ->setSaveParametersInSession(true);
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
     
    protected function _prepareCollection()
    {
    	
    	//echo 'testec';
    	//die();
    	
        // Get and set our collection for the grid
        //$collection = Mage::getResourceModel($this->_getCollectionClass());
        
    	$collection = Mage::getModel('importean/ean')->getCollection();
    	
    	//$collection = Mage::getModel('catalog/product')->getCollection();
    	
        /*
        echo '<pre>';
        var_dump($collection->getData());
       	echo '</pre>';
       	die();
        */
        
        
      	$this->setCollection($collection);

        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
    	
    	/*
    	
    	  	["entity_id"]=>
		    string(1) "1"
		    ["entity_type_id"]=>
		    string(1) "4"
		    ["attribute_set_id"]=>
		    string(1) "9"
		    ["type_id"]=>
		    string(6) "simple"
		    ["sku"]=>
		    string(4) "9163"
		    ["created_at"]=>
		    string(19) "2008-06-25 03:33:32"
		    ["updated_at"]=>
		    string(19) "2012-09-26 21:33:35"
		    ["has_options"]=>
		    string(1) "1"
		    ["required_options"]=>
		    string(1) "1"
    	 
    	 
    	 */
    	
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
    	
    	
    	$this->addColumn('sku', array(
    			'header' => 'SKU',//$this->__('ID'),
    			'index' => 'sku',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	$this->addColumn('ean', array(
    			'header' => 'EAN',//$this->__('ID'),
    			'index' => 'ean',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	$this->addColumn('descricao', array(
    			'header' => 'Descrição',//$this->__('ID'),
    			'index' => 'descricao',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	$this->addColumn('principio_ativo', array(
    			'header' => 'Principio Ativo',//$this->__('ID'),
    			'index' => 'principio_ativo',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
        
    	$this->addColumn('fabricante', array(
    			'header' => 'Fabricante',//$this->__('ID'),
    			'index' => 'fabricante',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	$this->addColumn('abc', array(
    			'header' => 'ABC',//$this->__('ID'),
    			'index' => 'abc',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	$this->addColumn('dcb', array(
    			'header' => 'DCB',//$this->__('ID'),
    			'index' => 'dcb',
    			//'width' => '100px',
    			//'filter_condition_callback' => array($this, '_filterIdCondition')
    	));
    	
    	
        //$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('sales')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
    /*
    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('id');
    	$this->getMassactionBlock()->setFormFieldName('skux');
    	$this->getMassactionBlock()->setUseSelectAll(true);
        	
    	return $this;
    }
    */ 
     
	public function getGridUrl()
    {
        return $this->getUrl('*/*/index', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
        // $this->getUrl('*/*/edit', array(
        //    'store'=>$this->getRequest()->getParam('store'),
        //    'id'=>$row->getId())
        // );
    }
}