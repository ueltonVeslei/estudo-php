<?php

class Intelipost_Basic_Model_Intelipost_Api
// extends Varien_Object
{

const POST = 'POST';
const GET  = 'GET';

protected $_apiUrl;
protected $_apiKey;
protected $_apiResponse;
private $_postData;
public $_hasErrors = false;
public $_curlError = false;
public $_arrErrors = array();

public function apiRequest($httpMethod, $apiMethod, $postData = false, $versionControl)
{
    // var_dump ($postData); // die;
	$this->setCredentials();	
	$moduleVersion = Mage::getConfig()->getModuleConfig("Intelipost_Basic")->version  . "_" . $versionControl->getModuleName() . ":" . $versionControl->getModuleVersion();			

	if (Mage::helper('quote')->getConfigData('debug')) {
			$this->_postData = json_encode($postData);
			$this->_postData = Mage::helper('quote')->prettyPrint($this->_postData);
		}
    $mgedition = Mage::helper('basic')->getMageEdition();
    $mgversion = $mgedition." ".Mage::getVersion();
    $s = curl_init();

    $time = strpos($apiMethod, 'quote') !== false ? 3 : 10;

    //curl_setopt($s, CURLOPT_TIMEOUT_MS, 1);
    curl_setopt($s, CURLOPT_TIMEOUT, $time);
    curl_setopt($s, CURLOPT_URL, $this->_apiUrl.$apiMethod);
    curl_setopt($s, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json", "api_key: $this->_apiKey", "platform: Magento $mgversion", "plugin: $moduleVersion"));        
    curl_setopt($s, CURLOPT_ENCODING , "");
    curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);

    //curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);

    if ($httpMethod === 'POST') 
    {
    	$postData = $this->encodeJsonRequest($postData);
    	
    	curl_setopt($s, CURLOPT_POST, true);
    	curl_setopt($s, CURLOPT_POSTFIELDS, $postData);
    }

    $this->_apiResponse = curl_exec($s);

    if (curl_errno($s) != 0)
	{
		Mage::log('erro curl ' . curl_errno($s));
		$this->_curlError = true;
	}	

    curl_close($s);

    $this->checkResponseErrors();

    return $this;
}

public function setCredentials()
{
	if (!is_string($this->_apiKey))
	{
		$this->_apiKey = Mage::helper('basic')->getDecriptedKey('apikey');
		$this->_apiUrl = Mage::getStoreConfig('intelipost_basic/settings/test_fallback') ? 'http://www.google.com.br' : Mage::helper('basic')->getDecriptedKey('apiurl');
	}

}

public function apiResponseToArray($assoc = false)
{
	$decodedResponse = $this->decodeJsonResponse($assoc);
	$returnArray = array();
	foreach ($decodedResponse->content as $key => $value) 
	{
		$returnArray[$key] = $value;
	}

	return $returnArray;
}

public function apiResponseToObject($assoc = false)
{
	$decodedResponse = $this->decodeJsonResponse($assoc);
	return $decodedResponse;
}

public function decodeJsonResponse($assoc = false)
{	
	return json_decode($this->_apiResponse, $assoc);
}

public function encodeJsonRequest($dataToEncode)
{
	return str_replace('\\/', '/', json_encode($dataToEncode));
}

protected function checkResponseErrors()
{
	if ($this->_curlError)
	{
		$this->_hasErrors = true;
		return;
	}
		
	$decodedResponse = $this->decodeJsonResponse();		

	if (!is_null($this->_postData)) 
	{
		if (Mage::getSingleton('core/session')->getIntelipostDebug()) {
			Mage::getSingleton('core/session')->unsIntelipostDebug();
		}
		$encode = json_encode($decodedResponse);
		$encode = Mage::helper('quote')->prettyPrint($encode);
		$debug = array('request' => $this->_postData, 'return' => $encode);		
		Mage::getSingleton('core/session')->setIntelipostDebug($debug);
		//Mage::register('intelipost_quote', $debug);
	}
	if ($decodedResponse->status === 'ERROR')
	{			
		$this->_hasErrors = true;
		foreach($decodedResponse->messages as $key => $value)
		{
			$this->_arrErrors[$key] = $value;
		}

		Mage::helper('quote')->log($this->_arrErrors);
	}

	return $this;
}


}

