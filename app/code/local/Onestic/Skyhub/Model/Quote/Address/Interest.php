<?php
class Onestic_Skyhub_Model_Quote_Address_Interest extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('interest');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        if ($address->getData('address_type')=='billing')
            return $this;

        $paymentMethodOK = (strpos($address->getQuote()->getPayment()->getMethod(), 'skyhub_') !== false);
        $amount         = $address->getQuote()->getInterest();

        if($amount > 0 && $amount != null && $paymentMethodOK)
        {
            $address->setInterest($amount);
            $address->setBaseInterest($amount);

            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $amount);
            $address->setGrandTotal($address->getGrandTotal() + $amount);
        }

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if($address->getInterest() != 0) {
            $address->addTotal([
                'code' => $this->getCode(),
                'title' => 'Juros',
                'value' => $address->getInterest()
            ]);
        }
    }
}
