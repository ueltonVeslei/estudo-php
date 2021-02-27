<?php

class Dmg_Guru_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'guru_payment';

    protected $_infoBlockType = 'dmg_guru/info_payment';

    protected $_canUseCheckout = false;

    public function assignData($data)
    {
        $data = ($data instanceof Varien_Object ? $data->getData() : $data);

        if (!isset($data['cc_type'])) {
            return $this;
        }

        $data['additional_data'] = $data['cc_type'];
        unset($data['cc_type']);

        $this->getInfoInstance()->addData($data);

        return $this;
    }
}
