<?php

class Intelipost_Quote_Helper_Data
extends Mage_Core_Helper_Abstract
{

const LOG_FILENAME = 'intelipost_quote.log';
const moduleName = 'QUOTE';

public function isEnabled()
{
    return true;

    if(!$this->isModuleEnabled())  return;

    return $this->getConfigData('active') ? true : false;
}    	

public function getModuleName($isRequote = false)
{
    if ($isRequote)
    {
        return 'RE' . self::moduleName;
    }

    return self::moduleName;
}

public function getFallbackMethod()
{
    return 'intelipost_Fallback';
}

public function isFallBack($orderDescription)
{
    $isFallBack = false;
    if ($orderDescription == $this->getFallbackMethod())
    {
         $isFallBack = true;
    }
    return $isFallBack;
}

public function isRequoteAllowed($orderStatus)
{
    $allowed = false;
    if (!$this->getConfigData('use_requote')) {
        return $allowed;
    }

    $allowedStatus = $this->getConfigData('requote_order_status');

    if ($allowedStatus)
    {
        if (strpos($allowedStatus, ','))
        {
            $allStatus = explode(',', $allowedStatus);

            if (in_array($orderStatus, $allStatus))
            {

                $allowed = true;
            }
        }
        else
        {
            if ($orderStatus == $allowedStatus)
            {
                $allowed = true;
            }
        }
    }

    return $allowed;
}

public function getConfigData($key = null)
{
    $path = 'carriers/' . Intelipost_Quote_Model_Carrier_Intelipost::CODE;
    
    if(!is_null($key)) $path .= '/' . $key;

    return Mage::getStoreConfig($path);
}   

public function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

public function getOriginZipCode()
{
    return Mage::getStoreConfig('shipping/origin/postcode');
}

public function getDecriptedKey($key)
{
    $decrypt_variable = Mage::helper('core')->decrypt($this->getConfigData($key));

    return $decrypt_variable;
}

public function log($message, $code = null, $level = null)
{           
    
    if(!is_null($code) && !empty($code))
    {
        $message = sprintf('%s: %s', $code, $message);
    }

    if (is_array($message))
    {
        $message = print_r($message, true);
    }

    Mage::log($message, $level, self::LOG_FILENAME);

    return true;
}

public function getModuleHandle()
{
    return 'Intelipost_Quote';
}

public function getCustomizeCarrierTitle($deliveryoption_description, $deliveryoption_estimated_delivery, $method_id = null)
{
    //para WT

    
    if ($deliveryoption_estimated_delivery == 0)
    {
        return sprintf($this->getConfigData('same_day_delivery_title'), $deliveryoption_description);       
    }

    if (strpos($deliveryoption_description, '- Agendada') !== false)
    {
        return $deliveryoption_description;
    }
    else
    {
        if ($this->getConfigData('extra_time_showed'))
        {
            $extra_time = (int)$deliveryoption_estimated_delivery + (int)$this->getConfigData('extra_time_showed');
            return sprintf($this->getConfigData('customizetitle'), $deliveryoption_description, (int)$deliveryoption_estimated_delivery, $extra_time);
        }
        else {            
            return sprintf($this->getConfigData('customizetitle'), $deliveryoption_description, (int)$deliveryoption_estimated_delivery);
        }
    }
}

public function getItemsCount($items)
{
    $countItems = 0;

    if ($items)
    {
        foreach ($items as $item)
        {
            $countItems += $item->getQty() ? $item->getQty() : $item->getData('qty_ordered');
        }         
    }

    return $countItems;
}

public function getSameDayTexts()
{
    $arr = array(   '1' => '10:00',
                    '2' => '20:00',
                    '3' => '23:59');

    date_default_timezone_set('America/Sao_Paulo');
    $date = date('H:i');
    $date = strtotime($date);

    if ($date >= strtotime($arr['1']) && $date < strtotime($arr['2'])) {
        return 'Entrega até o próximo dia útil';
    }
    else if ($date >= strtotime($arr['2'] && $date <= strtotime($arr['3']))) {
        return 'Entrega em 24 horas';
    }
    else {
        'Entrega no mesmo dia';
    }
}

}

