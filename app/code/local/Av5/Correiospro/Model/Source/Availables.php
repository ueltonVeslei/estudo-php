<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Shipping (Frete)
 * @package    Av5_Correiospro
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Av5_Correiospro_Model_Source_Availables extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
	private $_methodNames = array(
			'04014' => 'Sedex Sem Contrato (04014)',
            '40010' => 'Sedex Sem Contrato (40010)',
            '40096' => 'Sedex Com Contrato (40096)',
        	'40436' => 'Sedex Com Contrato (40436)',
        	'40444' => 'Sedex Com Contrato (40444)',
	        '04162' => 'Sedex Com Contrato (04162)',
            '81019' => 'E-Sedex Com Contrato (81019)',
			'04510' => 'PAC Sem Contrato (04510)',
            '41106' => 'PAC Sem Contrato (41106)',
            '41068' => 'PAC Com Contrato (41068)',
	        '04669' => 'PAC Com Contrato (04669)',
			'04693' => 'PAC Grandes Volumes (04693)',
            '40215' => 'Sedex 10 (40215)',
            '40290' => 'Sedex HOJE (40290)',
            '40045' => 'Sedex a Cobrar (40045)',
        );	
	
	public function getAllOptions()
    {
    	$_methods = explode(",", Mage::helper('av5_correiospro')->getConfigData('posting_methods'));
    	$_options = array();
    	foreach ($_methods as $method) {
    		$_options[] = array('value' => $method, 'label' => $this->_methodNames[$method]);
    	}
        
        return $_options;
    }
    
    public function toOptionArray()
    {
    	return $this->getAllOptions();
    }
    
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
    	$adminStore  = Mage_Core_Model_App::ADMIN_STORE_ID;
    	$valueTable1 = $this->getAttribute()->getAttributeCode() . '_t1';
    	$valueTable2 = $this->getAttribute()->getAttributeCode() . '_t2';
    
    	$collection->getSelect()->joinLeft(
    		array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
    		"`e`.`entity_id`=`{$valueTable1}`.`entity_id`"
    		. " AND `{$valueTable1}`.`attribute_id`='{$this->getAttribute()->getId()}'"
    		. " AND `{$valueTable1}`.`store_id`='{$adminStore}'",
    		array()
    	);
    
    	if ($collection->getStoreId() != $adminStore) {
    		$collection->getSelect()->joinLeft(
    			array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
    			"`e`.`entity_id`=`{$valueTable2}`.`entity_id`"
    			. " AND `{$valueTable2}`.`attribute_id`='{$this->getAttribute()->getId()}'"
    			. " AND `{$valueTable2}`.`store_id`='{$collection->getStoreId()}'",
    			array()
    		);
    		$valueExpr = new Zend_Db_Expr("IF(`{$valueTable2}`.`value_id`>0, `{$valueTable2}`.`value`, `{$valueTable1}`.`value`)");
    	} else {
    		$valueExpr = new Zend_Db_Expr("`{$valueTable1}`.`value`");
    	}
    
    	$collection->getSelect()->order($valueExpr, $dir);
    
    	return $this;
    }
    
	public function getFlatColums()
	{
    	$columns = array(
    		$this->getAttribute()->getAttributeCode() => array(
	    		'type'      => 'int',
	    		'unsigned'  => false,
	    		'is_null'   => true,
	    		'default'   => null,
	    		'extra'     => null
	    	)
    	);
    	return $columns;
    }
    
    
    public function getFlatUpdateSelect($store)
    {
    	return Mage::getResourceModel('eav/entity_attribute')->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}