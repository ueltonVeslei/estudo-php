<?php
/**
 * Order Statuses source model
 */
class Saffira_Koin_Model_System_Config_Source_Order_Status_Koin extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    const STATE_KOIN_ACCREDITED = 'koin_aprovado';

    protected $_stateStatuses = Saffira_Koin_Model_System_Config_Source_Order_Status_Koin::STATE_KOIN_ACCREDITED;


}


