<?php
 
class Briel_ReviewPlus_Block_Adminhtml_Reviews_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('product_reviews');
        $this->setDefaultSort('posted_time');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
	}

	public function getRowUrl($row) {
		// return $this->getUrl('adminhtml/reviews/edit', array('id' => $row->getId()));
	}

    protected function _prepareCollection() {
        $collection = Mage::getModel('reviewplus/reviews')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header'    => Mage::helper('reviewplus')->__('ID'),
            'align'     =>'right',
            'width'     =>'50px',
            'index'     =>'id',
            'filter_condition_callback' => array($this, '_filterId'),
        ));
		$this->addColumn('posted_time', array(
            'header'    => Mage::helper('reviewplus')->__('Posted On'),
            'align'     =>'left',
            'index'     =>'posted_time',
            'type' 		=>'datetime',
            'width'		=>'150px',
            'renderer'  =>'Briel_ReviewPlus_Block_Adminhtml_Reviews_Renderer_PostedTime',
            'filter_condition_callback' => array($this, '_datetimeFilterPostedOn'),
        ));
		$this->addColumn('product_sku', array(
            'header'    => Mage::helper('reviewplus')->__('Ordered Product(s)'),
            'align'     =>'left',
            'index'     =>'product_sku',
        ));
		$this->addColumn('product_review', array(
            'header'    => Mage::helper('reviewplus')->__('Review'),
            'align'     =>'left',
            'index'     =>'product_review',
            'filter'    => false,
        ));
		$this->addColumn('product_rating', array(
            'header'    => Mage::helper('reviewplus')->__('Rating'),
            'align'     =>'left',
            'index'     =>'product_rating',
            'width'     =>'70px;',
            'renderer'	=>'Briel_ReviewPlus_Block_Adminhtml_Reviews_Renderer_Rating',
            'type'  	=>'options',
            'options' 	=> array('1' => '1 Star', '2' => '2 Stars', '3' => '3 Stars', '4' => '4 Stars', '5' => '5 Stars'),
        ));
		$this->addColumn('customer_name', array(
            'header'    => Mage::helper('reviewplus')->__('Customer Name'),
            'align'     =>'left',
            'index'     =>'customer_name',
            'width'     =>'200px',
        ));
		/*$this->addColumn('customer_email', array(
            'header'    => Mage::helper('reviewplus')->__('Customer Email'),
            'align'     =>'left',
            'index'     =>'customer_email',
            'width'     =>'200px',
        ));*/
		$this->addColumn('review_status', array(
            'header'    => Mage::helper('reviewplus')->__('Review status'),
            'align'     =>'left',
            'index'     =>'review_status',
            'width'     =>'120px',
            'type'  	=>'options',
            'options' 	=> array('approved' => 'Approved', 'pending' => 'Pending'),
        ));
		$this->addColumn('edit_action', array(
			'header'    =>Mage::helper('reviewplus')->__('Action'),
			'index'     =>'id',
			'width'     =>'50px',
			'align'		=>'center',
			'filter'    => false,
            'sortable'  => false,
            'renderer'  =>'Briel_ReviewPlus_Block_Adminhtml_Reviews_Renderer_EditAction',
        ));
        return parent::_prepareColumns();
    }

	protected function _prepareMassaction() {
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('review_id');
		$this->getMassactionBlock()->addItem('delete', array(
			'label'		=> Mage::helper('reviewplus')->__('Delete Review'),
			'url'  		=> $this->getUrl('*/*/massdelete', array('' => '')),
			'confirm' 	=> Mage::helper('reviewplus')->__('Are you sure?')
		));
		$this->getMassActionBlock()->addItem('approve', array(
			'label'		=> Mage::helper('reviewplus')->__('Approve Review'),
			'url'  		=> $this->getUrl('*/*/massapprove', array('' => '')),
			'confirm' 	=> Mage::helper('reviewplus')->__('Approve ratings?')
		));
		return $this;
	}
	
	protected function _filterId($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
		$this->getCollection()->addFieldToFilter('id', array('eq' => $value));
		return $this;
	}

	protected function _datetimeFilterPostedOn($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
		if (empty($value['from']) && isset($value['to'])) {
			$to = $value['to']->getTimestamp();
			$from = mktime(0, 0, 0);
			$this->getCollection()->addFieldToFilter('posted_time', array('lteq' => $to));
			return $this;
		} else if (empty($value['to']) && isset($value['from'])) {
			$from = $value['from']->getTimestamp();
			$this->getCollection()->addFieldToFilter('posted_time', array('gteq' => $from));
			return $this;
		} else {
			$from = $value['from']->getTimestamp();
			$to = $value['to']->getTimestamp();
			$this->getCollection()->addFieldToFilter('posted_time', array('from' => $from, 'to' => $to));
			return $this;
		}
	}
}
?>