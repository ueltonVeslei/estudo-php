<?php

class Intelipost_Quote_Model_Adminhtml_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_REQUOTE_STRING_PATH = 'crontab/jobs/quote_cron_requote/schedule/cron_expr';
 
    protected function _afterSave()
    {
        $cron_requote = $this->getData('groups/intelipost/fields/requote_frequency/value');       

        try 
        {

            if (Mage::getStoreConfig('carriers/intelipost/use_requote_cron')) {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_REQUOTE_STRING_PATH, 'path')
                    ->setValue($this->getExpression($cron_requote))
                    ->setPath(self::CRON_REQUOTE_STRING_PATH)
                    ->save();
            }
            else
            {
                $data =  Mage::getModel('core/config_data')->load(self::CRON_REQUOTE_STRING_PATH, 'path');
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