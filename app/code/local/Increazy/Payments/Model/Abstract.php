<?php
class Increazy_Payments_Model_Abstract extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'increazy_payments';
    protected $_infoBlockType = 'increazy_payments/info';

    public function assignData($data)
    {
        $data['method'] = $this->getConfigData('title');
        $this->getInfoInstance()->setAdditionalInformation($data->toArray());
    }

}