<?php

class Dmg_Guru_Block_Info_Payment extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dmg_guru/payment/info/default.phtml');
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();

        if ($additionalData = $this->getInfo()->getAdditionalData()) {
            $additionalData = unserialize($additionalData);

            foreach ($additionalData as $key => $item) {
                $data[$key] = $item;
            }
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
