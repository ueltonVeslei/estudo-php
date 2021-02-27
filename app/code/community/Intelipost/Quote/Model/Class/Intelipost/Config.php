<?php

class Intelipost_Quote_Model_Class_Intelipost_Config
// extends Varien_Object
{

protected $isFreeShipping = false;
protected $isPremiumCustomerFreeShipping = false;
protected $rules;
protected $helper;

public function setFreeShipping($freeShipping)
{
    $this->isFreeShipping = $freeShipping;
}

public function getFreeShipping()
{
    return $this->isFreeShipping;
}

public function setRules()
{
    if ($this->getHelper()->getConfigData('has_customer_freeshipping') && $this->premiumCostumerGroupFreeShipping())
    {
        $this->isPremiumCustomerFreeShipping = true;
    }

    $this->rules = array('methodsQty' => $this->getMethodQty(), 'ordering_method' => $this->getMethodsOrdering());  
}

public function getRules()
{
    return $this->rules;
}

public function getHelper()
{
    if (!$this->helper) $this->helper = Mage::helper('quote');

    return $this->helper;

}

public function getMethodQty()
{
    $Qty = $this->getHelper()->getConfigData('methods_qty');

    if ($this->isPremiumCustomerFreeShipping)
    {
        $Qty = $this->getDefaultQty('premiumCostumerGroupFreeShipping');
    } 
    else if ($this->isFreeShipping)
    {
        $Qty = $this->getHelper()->getConfigData('methods_qty');
    }      
    else if ($this->getHelper()->getConfigData('order_methods') && strpos($this->getHelper()->getConfigData('order_methods'), ','))
    {
        $Qty = $this->getDefaultQty('lower_price_delivery');
    }        

    return $Qty;
}

public function getMethodsOrdering()
{
    $ordering = "";

    if ($this->isPremiumCustomerFreeShipping)
    {
        $ordering = $this->getDefaultOrdering('premiumCostumerGroupFreeShipping');
    }
    else if ($this->isFreeShipping)
    {
        $ordering = $this->getDefaultOrdering('free_shipping_method');
    }
    else if ($this->getHelper()->getConfigData('order_methods'))
    {
        $ordering = $this->getDefaultOrdering('order_methods');
    }

    return $ordering;
}

public function hasConfigRule()
{
    if (array_key_exists ('ordering_method', $this->rules)) return;
    
    $configRule = false;
    foreach ($this->rules['ordering_method'] as $rule) 
    {            
        if (is_array($rule))
        {
            if (in_array('matched', $rule))
            {
                if ($rule['matched'] == 0)
                {
                    $configRule = true;
                }
            }
        }
    }

    return $configRule;
}

public function updateMethodRuleQty($action)
{
    if ($action == 'added')
    {
        $qty = $this->rules['methodsQty'];   
        $this->rules['methodsQty'] -= $qty;
    }

    return $this;
}

public function getPremiumCustomerFreeShipping()
{
    return $this->isPremiumCustomerFreeShipping;
}

public function premiumCostumerGroupFreeShipping()
{
    $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
   
    if ($roleId)
    {
        $groupsId = $this->getHelper()->getConfigData('premium_customer_freeshipping');
        
        if (strpos($groupsId, ','))
        {
            $groupsId = explode(',', $groupsId);
        }
        
        if (is_array($groupsId))
        {
            if (in_array($roleId, $groupsId))
            {
                return true;
            }
        }
        else if ($groupsId == $roleId)
        {
            return true;
        }
    }
    
    return false;
}

public function getDefaultOrdering($type)
{
    $typeOrdering = array();
     switch ($type) {
        case 'premiumCostumerGroupFreeShipping':
            $typeOrdering['lower_delivery_date'] = array('matched' => 0);            
            break;
        case 'order_methods':
            if (strpos($this->getHelper()->getConfigData('order_methods'), ','))
            {                    
                $typeOrdering['lower_delivery_date'] = array('matched' => 0);                   
                $typeOrdering['lower-price'] = array('matched' => 0);
            }
            else
            {
                $typeOrdering[$this->getHelper()->getConfigData('order_methods')] = array('matched' => 0);                  
            }
            break;
        case 'free_shipping_method':
            $typeOrdering[$this->getHelper()->getConfigData('free_shipping_method')] = array('matched' => 0);
            break;
    } 

    return $typeOrdering;       
}

public function getDefaultQty($type)
{
    switch ($type) {
        case 'premiumCostumerGroupFreeShipping':
            return 1;            
        case 'lower_price_delivery':
            return 2;
    }
}

public function setMatchedRule($ruleId, $methodId)
{   
    $this->rules['ordering_method'][$ruleId]['matched'] = $methodId;        
}

public function duplicityRule($ruleId, $methodId)
{
    $duplicity = false;

    foreach ($this->rules['ordering_method'] as $key => $value) 
    {
        if ($key != $ruleId)
        {
            foreach ($value as $val) 
            {
                if ($val == $methodId)
                {
                    unset($this->rules['ordering_method'][$ruleId]);
                    $this->updateMethodRuleQty('added');
                    $duplicity = true;
                }
            }                            
        }
    }                     

    return $duplicity;
}



}

