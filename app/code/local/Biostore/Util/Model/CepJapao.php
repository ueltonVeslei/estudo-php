<?php
class Biostore_util_Model_CepJapao
{
	public function consultaCep($cep)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if ($cep == '') {
			return array();
		} else {
            $query = "SELECT * FROM shipping_japao WHERE cep = '$cep'";
			//$this->la($query);
            $frete = $connection->fetchAll($query);
            
			if (isset($frete[0])) {
				//$this->la($frete);die;
				return $frete[0];
			}
			return array();
        } 
        exit;
    } // eof sendProductTotradepar
}