<?php

class Intelipost_Basic_Helper_Data
extends Mage_Core_Helper_Abstract
{

protected $versionControl;
protected $methods_info;

const moduleName = 'BASIC';

const XML_PATH_PRODUCT_ATTR_CATEGORIES = 'intelipost_basic/product_attributes/categories';

public function getEstimatedDeliveryDate($shippingDescription, $invoice_date, $order_id)
{
    $timestamp = strtotime($invoice_date);
    $N_date = date('Y-m-d', $timestamp);  

    $delivery_days = $this->getDeliveryDays($shippingDescription, $order_id);

    //$estimatedDeliveryDay =  date('Y-m-d', strtotime($N_date. ' + ' . $delivery_days . ' days'));    
    
    $date = $invoice_date;
    $i = 1;
    while ($i <= $delivery_days)
    {
        $date = $this->ignoreDate($date);
        
        $i++;
        $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        
        $date = $this->ignoreDate($date);
    }
    
    //$estimatedDeliveryDay = date('Y-m-d', strtotime($N_date. ' + ' . $delivery_days . ' days')); 

    $estimatedDeliveryDay = $date;//$this->checkValidDate($estimatedDeliveryDay);
    return $estimatedDeliveryDay;

}

public function ignoreDate($date)
{
    if ($this->isWeekend($date) || $this->isFeriado($date))
    {
        $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        //continue;
    }

    return $date;
}

public function getModuleName()
{
    return self::moduleName;
}

public function getModuleHandle()
{
    return 'Intelipost_Basic';
}

public function getOrderQtyVolumes($order_id)
{
    $volumes = 1;
    $calcMode       = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');
    $calcDimensions = Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc');
    if ($calcDimensions != 'no' && $calcMode != 'product')
    {
        $order = Mage::getModel('sales/order')->load($order_id);
        $dimension = Mage::getModel('basic/package_dimension');
        $dimension->calcItemsDimension($order->getAllItems());

        $volumes = count($dimension->getPackages());
    }

    return $volumes;
}

public function checkOrderQtyVolumes($packages, $order_id)
{
    $basic_order = Mage::getModel('basic/orders')->load($order_id, 'order_id');
    if ($basic_order->getQtyVolumes() == count($packages) || $basic_order->getQtyVolumes() == 0)
    {
        return $packages;
    }
    else
    {
        $new_volumes = $basic_order->getQtyVolumes();
        $tot_width = 0;
        $tot_height = 0;
        $tot_lenght = 0;
        $tot_weight = 0;
        $tot_vol    = 0;
        $tot_price  = 0;
        $tot_qty = 0;
        foreach ($packages as $id => $box)
        {
            $tot_width  += $box ['width'];
            $tot_height += $box ['height'];
            $tot_lenght += $box ['length'];
            $tot_weight += $box ['weight'];
            $tot_vol    += $box ['volume'];
            $tot_price  += $box ['price'];
            $tot_qty    += $box ['qty'];
        }
        
        $return = array();
        $used_qty = 0;

        for ($i = 1; $i <= $new_volumes; $i++)
        {            
            if ($tot_qty % $new_volumes == 0)
            {
                $package['qty'] = $tot_qty / $new_volumes;
            }
            else
            {
                $qty = floor($tot_qty / $new_volumes);
                if ($i == $new_volumes && $i > 1)
                {
                    $qty = $tot_qty - $used_qty;
                }

                $package['qty'] = $qty;
                $used_qty += $qty;
            }            
            
            $package['width'] = number_format((float)$tot_width / $new_volumes, 2, '.', '');
            $package['height'] = number_format((float)$tot_height / $new_volumes, 2, '.', '');
            $package['length'] = number_format((float)$tot_lenght / $new_volumes, 2, '.', '');
            $package['weight'] = number_format((float)$tot_weight / $new_volumes, 2, '.', '');
            $package['volume'] = number_format((float)$tot_vol / $new_volumes, 2, '.', '');
            $package['price'] = number_format((float)$tot_price / $new_volumes, 2, '.', '');

            $package['width']  = $package['width']  > 70 ? 70 : $package['width'];
            $package['height'] = $package['height'] > 60 ? 60 : $package['height'];
            $package['length'] = $package['length'] > 70 ? 70 : $package['length'];
            array_push($return, $package);
        }

       return ($return);
    }
    
}

public function isFeriado($date)
{    
     $feriados = $this->getFeriados();
     if (in_array($date, $feriados)) {        
        return true;
     }

     return false;
}

public function checkValidDate($date)
{
    $isValid = false;

    while (!$isValid) 
    {
        if ($this->isWeekend($date) || $this->isFeriado($date))
        {
            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        else
        {
            $isValid = true;
        }
    }

    return $date;
}

public function isWeekend($date) {
    return (date('N', strtotime($date)) >= 6);
}

public function getFeriados($ano = null)
{
  if ($ano === null)
  {
    $ano = intval(date('Y'));
  }
 
  $pascoa     = easter_date($ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
  $dia_pascoa = date('j', $pascoa);
  $mes_pascoa = date('n', $pascoa);
  $ano_pascoa = date('Y', $pascoa);
 
  $feriados = array(
    // Tatas Fixas dos feriados Nacionail Basileiras
    date('Y-m-d' , mktime(0, 0, 0, 1,  1,   $ano)), // Confraternização Universal - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 4,  21,  $ano)), // Tiradentes - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 5,  1,   $ano)), // Dia do Trabalhador - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 9,  7,   $ano)), // Dia da Independência - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 10,  12, $ano)), // N. S. Aparecida - Lei nº 6802, de 30/06/80
    date('Y-m-d', mktime(0, 0, 0, 11,  2,  $ano)), // Todos os santos - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 11, 15,  $ano)), // Proclamação da republica - Lei nº 662, de 06/04/49
    date('Y-m-d', mktime(0, 0, 0, 12, 25,  $ano)), // Natal - Lei nº 662, de 06/04/49
 
    // These days have a date depending on easter
    date('Y-m-d', mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2 ,  $ano_pascoa)),//6ºfeira Santa  
    date('Y-m-d', mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa)),//Corpus Cirist
  );

    if (Mage::getStoreConfig('intelipost_push/manage_ordes/feriado_carnaval'))   
    {
        array_push($feriados, date('Y-m-d', mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa)));
        array_push($feriados, date('Y-m-d', mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa)));
    }

  sort($feriados);
  
  return $feriados;
}


public function getIntelipostMethodName($methodId, $moduleId)
{
    if (!$this->methods_info)
    {
        $this->setVersionControlData(Mage::helper($moduleId)->getModuleName(), $moduleId);
        
        $intelipost_api = Mage::getModel('basic/intelipost_api');
        $intelipost_api->apiRequest(Intelipost_Basic_Model_Intelipost_Api::GET, 'info', false, $this->getVersionControlModel());
        
        $this->methods_info = $intelipost_api->apiResponseToObject();        
    }

    foreach ($this->methods_info->content->delivery_methods as $method) 
    {
        if ($method->id == $methodId)
        {
            return $method->name;
        }
        
    }

    return '';
}

public function getMethodsInfo($moduleId)
{
    if (!$this->methods_info)
    {
        $this->setVersionControlData(Mage::helper($moduleId)->getModuleName(), $moduleId);
        
        $intelipost_api = Mage::getModel('basic/intelipost_api');
        $intelipost_api->apiRequest(Intelipost_Basic_Model_Intelipost_Api::GET, 'info', false, $this->getVersionControlModel());
        
        $this->methods_info = $intelipost_api->apiResponseToObject(); 
    }

    return $this->methods_info;
}

public function getMethodName($methodId)
{
    Mage::log($methodId);
    $methods = Mage::getModel('basic/methods')->load($methodId, 'method_id');

    if (count($methods->getData()) > 0) 
    {
        $retorno = $methods->getMethodDescription();
    }
    else
    {
        $methods_info = $this->getMethodsInfo('quote');

        $data = array();
        foreach ($methods_info->content->delivery_methods as $method) 
        {
            if ($method->id == $methodId)
            {
                $retorno = $method->name;
            }
            else if (Mage::helper('quote')->getConfigData('copy_single_method')) 
            {
                $methodId -= Mage::helper('quote')->getConfigData('express_method_id_prefix');

                if ($method->id == $methodId) {
                    $retorno = $method->name;
                }
            }

            $info = array('method_id' => $method->id, 'method_description' => $method->name);
            array_push($data, $info);
        }

        foreach ($data as $dt) 
        {
            Mage::getModel('basic/methods')->load($dt['method_id'], 'method_id')->addData($dt)->save();
        }        
    }

    return $retorno;
}

protected function getDeliveryDays($shippingDescription, $order_id)
{
    $intelipost_order = Mage::getModel('basic/orders')->load ($order_id, 'order_id');
    if (count($intelipost_order->getData()) > 0)
    {
        $deliveryDays = $intelipost_order->getDeliveryBusinessDay();
    }
    else
    {
        preg_match_all('!\d+!', $shippingDescription, $matches);
        foreach ($matches as $key => $value) 
        {
            $deliveryDays = (int)$value[0];
        }
    }
    return $deliveryDays;
}

public function getLogisticProvider($shippingMethod)
{
    $provider = explode("intelipost_", $shippingMethod);
    $provider = explode(' ', $provider[1], 2);      
    
    return $provider[0];
}

public function getMageEdition()
{
    try
    {
        $edition = Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')->is('active', true) ? 'Enterprise' : 'Community';
    }
    catch(Exception $e)
    {
        $edition = 'Magento 1.6';
    }

    return $edition;
}

public function getIntelipostModules()
{
    return array(  'intelipost_basic', 'carriers', 'intelipost_export', 'intelipost_autocomplete', 'intelipost_labels','intelipost_tracking' );
}

public function getShippingMethod($shippingMethod)
{
    $provider = explode("intelipost_", $shippingMethod);
    
    return $provider[1];
}

public function cmToMeters($cm)
{
    $_cm = floatval($cm);

    return $_cm/100;
}

public function setVersionControlData($moduleName, $moduleHelper)
{
    $this->versionControl = Mage::getModel('basic/versionControl');
    $this->versionControl->setModuleName($moduleName);
    $this->versionControl->setModuleHelper($moduleHelper);
}

public function getDecriptedKey($key)
{
    $decrypt_variable = Mage::helper('core')->decrypt(Mage::getStoreConfig ('intelipost_basic/settings/' . $key));

    return $decrypt_variable;
}

public function getVersionControlModel()
{
    if ($this->versionControl)
    {
        return $this->versionControl;
    }

    return;
}

public function getProductCategories ($product_id = null)
{
    $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter();

    if (!empty ($product_id) && intval ($product_id) > 0)
    {
        $product = Mage::getModel('catalog/product')->load ($product_id);

        $category_ids = $product->getCategoryIds ();
    }

    if (empty ($category_ids))
    {
        $category_ids = Mage::getStoreConfig (self::XML_PATH_PRODUCT_ATTR_CATEGORIES);
    }

    if (!empty ($category_ids)) $categories->addIdFilter($category_ids);

    return $categories;
}

}

