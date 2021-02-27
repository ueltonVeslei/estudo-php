<?php
class Av5_Correiospro_Model_Rule_Condition_Product_Found extends Av5_Correiospro_Model_Rule_Condition_Product_Combine {
    
	public function __construct()
    {
        parent::__construct();
        $this->setType('av5_correiospro/rule_condition_product_found');
    }

    /**
     * Load value options
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Found
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(
            1 => Mage::helper('av5_correiospro')->__('ENCONTRADO'),
            0 => Mage::helper('av5_correiospro')->__('NÃO ENCONTRADO')
        ));
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . Mage::helper('av5_correiospro')->__("Se um item for %s no carrinho com %s destas condições for verdadeira:", $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml());
        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;
        foreach ($object->getAllItems() as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
            	$product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
            	$validated = $cond->validate($product);
            	Mage::helper('av5_correiospro')->log('CONDITION: ' . get_class($cond));
            	Mage::helper('av5_correiospro')->log('PRODUCT: ' . $product->getSku());
            	Mage::helper('av5_correiospro')->log('VALIDATED: ' . var_export($validated,true));
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        }
        // not found and we're making sure it doesn't exist
        elseif (!$found && !$true) {
            return true;
        }
        return false;
    }
}
