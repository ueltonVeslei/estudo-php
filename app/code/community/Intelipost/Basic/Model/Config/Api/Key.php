<?php

class Intelipost_Basic_Model_Config_Api_Key
extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{

public function save()
{
    $api_key    = $this->getValue();

    $responseJson = $this->testQuote($api_key);

    if (!isset($responseJson->status) || $responseJson->status == 'ERROR')
    {
        $messages = $responseJson->messages[0];
        $helper = Mage::helper('basic');

        Mage::throwException($helper->__($messages->text));
    }

    $this->_saveApi ($api_key);

    $this->_saveClient ($responseJson->content->client_id);

    parent::save();
}

private function testQuote($api_key)
{
    $api_url    = $this->getData('groups/settings/fields/apiurl/value');
    $zipcode    = $this->getData('groups/settings/fields/zipcode/value');

    $volume = Mage::getModel ('basic/request_volume');
    $volume->weight        = 10.2;
    $volume->volume_type   = 'BOX';
    $volume->cost_of_goods = "10";
    $volume->width         = 20.5;
    $volume->height        = 10.25;
    $volume->length        = 5.0;
    
    $quote = Mage::getModel ('quote/request_dimension');
    $quote->origin_zip_code      = $zipcode;
    $quote->destination_zip_code = $zipcode;

    array_push($quote->volumes, $volume);
    $quote->additional_information = array("lead_time_business_days" => 0);
    $quote->identification = array(  'session'   => '',  
                                    'ip'        => '',
                                    'page_name' => '',
                                    'url'       => '');
    $request = json_encode($quote);

    Mage::log("\nREQUEST: ".$request, null, "intelipost.log", true);

    $response = $this->intelipostRequest($api_url, $api_key, "/quote", $request);

    Mage::log("\nRESPONSE: ".$response, null, "intelipost.log", true);

    return json_decode($response);
}

private function intelipostRequest($api_url, $api_key, $entity_action, $request=false)
{

    $mgedition = Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')->is('active', true) ? 'Enterprise' : 'Community';
    $moduleVersion = Mage::getConfig()->getModuleConfig("Intelipost_Basic")->version;
    $mgversion = $mgedition." ".Mage::getVersion();

    $s = curl_init();

    curl_setopt($s, CURLOPT_TIMEOUT, 5000);
    curl_setopt($s, CURLOPT_URL, $api_url.$entity_action);
    curl_setopt($s, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json", "api_key: $api_key", "platform: $mgversion", "plugin: $moduleVersion"));
    curl_setopt($s, CURLOPT_POST, true);
    curl_setopt($s, CURLOPT_ENCODING , "");
    curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($s, CURLOPT_POSTFIELDS, $request);

    //curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
    

    $response = curl_exec($s);

    curl_close($s);
    
    return $response;
}

private function _saveApi ($api_key)
{
    $role = Mage::getModel('api/roles')->load('Intelipost','role_name');
    $role->setName('Intelipost')
        ->setRoleType('G')
        ->save();

    $rule = Mage::getModel('api/rules')
        ->setRoleId($role->getId())
        ->setResources(array('all'))
        ->saveRel();

    $user = Mage::getModel('api/user')->load('intelipost', 'username');
    $user->addData (array(
        'username' => 'intelipost',
        'firstname' => 'Intelipost',
        'lastname' => 'API',
        'email' => 'api@intelipost.com.br',
        'api_key' => $api_key,
        'api_key_confirmation' => $api_key,
        'is_active' => 1,
        'user_roles' => '',
        'assigned_user_role' => '',
        'role_name' => '',
        'roles' => array($role->getId())
    ));
    $user->save();
    $user->setRoleIds(array($role->getId()))
        ->setRoleUserId($user->getId())
        ->saveRelations();
}

private function _saveClient ($id)
{
    $params = Mage::app()->getRequest()->getParams();
    //var_dump ($params); die;
    
    $scope = 'default';
    $scope_id = 0;
    
    if (array_key_exists ('store', $params)) $scope = 'store';
    elseif (array_key_exists ('website', $params)) $scope = 'website';
    
    if (strcmp ($scope, 'default'))
    {
        $scope_id = Mage::getModel('core/' . $scope)->load($params [$scope])->getId ();
    }
    
    $config = Mage::getModel ('core/config');
    $config->saveConfig ('intelipost_basic/settings/client_id', $id, $scope, $scope_id);
}

}

