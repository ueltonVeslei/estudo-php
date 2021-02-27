 <?php

class Intelipost_Push_Block_Adminhtml_Orders_Romaneio_Details extends Mage_Adminhtml_Block_Template
{

	protected $methods_info;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('intelipost/push/romaneio/details.phtml');

        $this->methods_info = Mage::registry('methods');
    }

    public function getMethodsInfo()
    {
    	return $this->methods_info;
    }

    public function getShippingMethodsQty()
    {
    	return count($this->methods_info);
    }
}