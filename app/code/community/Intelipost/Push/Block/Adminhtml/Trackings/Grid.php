<?php

class Intelipost_Push_Block_Adminhtml_Trackings_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("pushTrackingsGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
			    $collection = Mage::getModel("basic/trackings")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}

		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("push")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));

                $this->addColumn('increment_id', array(
                    'header' => $this->__('Order #'),
                    'index'  => 'increment_id'
                ));
         
                $this->addColumn('code', array(
                    'header' => $this->__('Code'),
                    'index'  => 'code'
                ));
				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}
}

