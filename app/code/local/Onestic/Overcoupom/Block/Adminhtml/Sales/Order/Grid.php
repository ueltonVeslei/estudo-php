<?php
 
class Onestic_Overcoupom_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('onestic_overcoupom_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        //$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('onestic_overcoupom/coupon')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
 
    protected function _prepareColumns()
    {
        $helper = Mage::helper('onestic_overcoupom');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
 
        $this->addColumn('increment_id', array(
            'header' => $helper->__('Pedido #'),
            'index'  => 'increment_id'
        ));

        $this->addColumn('couponcode', array(
            'header' => $helper->__('Cupom #'),
            'index'  => 'couponcode'
        ));
 
        $this->addColumn('created_at', array(
            'header' => $helper->__('Utilizado em'),
            'type'   => 'datetime',
            'index'  => 'created_at'
        ));
 
        $this->addExportType('*/*/exportOnesticCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportOnesticExcel', $helper->__('Excel XML'));
 
        return parent::_prepareColumns();
    }
 
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}