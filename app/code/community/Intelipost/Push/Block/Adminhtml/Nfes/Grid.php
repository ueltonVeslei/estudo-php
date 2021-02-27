<?php

class Intelipost_Push_Block_Adminhtml_Nfes_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("pushNfesGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("basic/nfes")->getCollection();
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
         
                $this->addColumn('series', array(
                    'header' => $this->__('Series'),
                    'index'  => 'series'
                ));
         
                $this->addColumn('number', array(
                    'header' => $this->__('Number'),
                    'index'  => 'number'
                ));
         
                $this->addColumn('total', array(
                    'header'   => $this->__('Total'),
                    'index'    => 'total',
                    'type'  => 'currency',
                    'currency_code'     => Mage::app()->getStore()->getBaseCurrency()->getCode()            		
                ));
         
                $this->addColumn('cfop', array(
                    'header' => $this->__('CFOP'),
                    'index'  => 'cfop'
                ));
         
                $this->addColumn('created_at', array(
                    'header'        => $this->__('Created At'),
                    'index'         => 'created_at',
                    'type'          => 'date'
                ));
                /*
                $this->addColumn('key', array(
                    'header' => $this->__('Key'),
                    'index'  => 'key'
                ));
                */
			// $this->addRssList('push/adminhtml_rss_rss/nfes', Mage::helper('push')->__('RSS'));
			// $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			// $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction_()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_nfes', array(
					 'label'=> Mage::helper('push')->__('Remove NFEs'),
					 'url'  => $this->getUrl('*/adminhtml_nfes/massRemove'),
					 'confirm' => Mage::helper('push')->__('Are you sure?')
				));
			return $this;
		}
			

}

