<?php
class Onestic_Skyhub_Block_Adminhtml_Sales_Order_Totals extends Uecommerce_Mundipagg_Block_Adminhtml_Sales_Order_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();

        $source = $this->getSource();
        
        if ($this->getSource()->getInterest() > 0) {
            $this->addTotalBefore(new Varien_Object(array
            (
                    'code'  => 'skyhub_interest',
                    'field' => 'interest',
                    'value' => $this->getSource()->getInterest(),
                    'label' => $this->__('Interest')
            )), 'grand_total');
        }
        
        return $this;
    }
}
