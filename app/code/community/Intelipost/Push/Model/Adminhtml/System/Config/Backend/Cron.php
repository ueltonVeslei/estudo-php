<?php

class Intelipost_Push_Model_Adminhtml_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_CSO_STRING_PATH = 'crontab/jobs/push_cron_cso/schedule/cron_expr';
    const CRON_NFE_STRING_PATH = 'crontab/jobs/push_cron_nf/schedule/cron_expr';
    const CRON_RTS_STRING_PATH = 'crontab/jobs/push_cron_rts/schedule/cron_expr';
    const CRON_GS_STRING_PATH = 'crontab/jobs/push_cron_gs/schedule/cron_expr';
 
    protected function _afterSave()
    {
        $cron_cso = $this->getData('groups/push_cron_config/fields/cso_frequency/value');
 		$cron_nfe = $this->getData('groups/push_cron_config/fields/nfe_frequency/value');
        $cron_rts = $this->getData('groups/push_cron_config/fields/rts_frequency/value');
        $cron_gs  = $this->getData('groups/push_cron_config/fields/gs_frequency/value');        

        try {

            if (Mage::getStoreConfig('intelipost_push/push_cron_config/use_cso_cron')) {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_CSO_STRING_PATH, 'path')
                    ->setValue($this->getExpression($cron_cso))
                    ->setPath(self::CRON_CSO_STRING_PATH)
                    ->save();
            }
            else
            {
                $data =  Mage::getModel('core/config_data')->load(self::CRON_CSO_STRING_PATH, 'path');
                if (count($data->getData()) > 0) {
                    $data->delete();
                }
            }

            if (Mage::getStoreConfig('intelipost_push/push_cron_config/use_nfe_cron')) {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_NFE_STRING_PATH, 'path')
                    ->setValue($this->getExpression($cron_nfe))
                    ->setPath(self::CRON_NFE_STRING_PATH)
                    ->save();
            }
            else
            {
                $data =  Mage::getModel('core/config_data')->load(self::CRON_NFE_STRING_PATH, 'path');
                if (count($data->getData()) > 0) {
                    $data->delete();
                }
            }

            if (Mage::getStoreConfig('intelipost_push/push_cron_config/use_rts_cron')) {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_RTS_STRING_PATH, 'path')
                    ->setValue($this->getExpression($cron_rts))
                    ->setPath(self::CRON_RTS_STRING_PATH)
                    ->save();
            }
            else
            {
                $data =  Mage::getModel('core/config_data')->load(self::CRON_RTS_STRING_PATH, 'path');
                if (count($data->getData()) > 0) {
                    $data->delete();
                }
            }

            if (Mage::getStoreConfig('intelipost_push/push_cron_config/use_gs_cron')) {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_GS_STRING_PATH, 'path')
                    ->setValue($this->getExpression($cron_gs))
                    ->setPath(self::CRON_GS_STRING_PATH)
                    ->save();
            }
            else
            {
                $data =  Mage::getModel('core/config_data')->load(self::CRON_GS_STRING_PATH, 'path');
                if (count($data->getData()) > 0) {
                    $data->delete();
                }
            }
        }
        catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
 
        }
    }

    protected function getExpression($time)
    {
        if ($time == 1) {
            return '* * * * *';
        }
        else
        {
            $return = '*/'.$time. ' * * * *';
        }

        return $return;
    }    
}