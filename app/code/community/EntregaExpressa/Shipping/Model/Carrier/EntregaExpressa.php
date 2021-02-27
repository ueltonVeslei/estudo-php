<?php
/**
 * Entrega via EntregaExpressa
 *
 * @category   EntregaExpressa
 * @package    EntregaExpressa_Shipping
 * @author     Igor Pfeilsticker <igorsop@gmail.com>
 */
 
class EntregaExpressa_Shipping_Model_Carrier_EntregaExpressa
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'EntregaExpressa';
	
	protected $_result = null;

	public function entregaViaEntregaExpressa($cep) {
		$cep = str_replace("-", "", $cep);
		$faixa_cep = $this->getConfigData('faixa_cep');
		if ($faixa_cep == "")  { return false; }
		else {
			$faixa_cep = explode("\n", $faixa_cep);
			
			foreach($faixa_cep as $cep_permitido) {
				$ceps = explode("-", $cep_permitido);
				$cep_ini = $ceps[0];
				$cep_fim = $ceps[1];
				
				if ($cep >= $cep_ini && $cep <= $cep_fim)
				 return true; 
			}
		}
		return false;
	}
	
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
		
    	if (!$this->getConfigFlag('active'))
		{
			//Disabled
			return false;
		}
		
		
		
		$result = Mage::getModel('shipping/rate_result');
		
		$error = Mage::getModel('shipping/rate_result_error');
		$error->setCarrier($this->_code);
		$error->setCarrierTitle($this->getConfigData('name'));


		$packagevalue = $request->getBaseCurrency()->convert($request->getPackageValue(), $request->getPackageCurrency());
		$minorderval = $this->getConfigData('min_order_value');
		$maxorderval = $this->getConfigData('max_order_value');
		if($packagevalue <= $minorderval || $packagevalue >= $maxorderval){
			//Value limits
			$error->setErrorMessage("nao disponível por causa do valor minimo");
			$result->append($error);
			return $result;
		}

		$frompcode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
		$topcode = $request->getDestPostcode();
		
		
		if (!$this->entregaViaEntregaExpressa($topcode)) {
			return false;
		}
		
		//Fix Zip Code
		$frompcode = str_replace('-', '', trim($frompcode));
		$topcode = str_replace('-', '', trim($topcode));

		if(!preg_match("/^[0-9]{8}$/", $topcode))
		{
			//Invalid Zip Code
			$error->setErrorMessage("cep invalido");
			$result->append($error);
			Mage::helper('customer')->__('Invalid ZIP CODE');
			return $result;
		}
		
		
		$sweight = $request->getPackageWeight();

		if ($sweight > $this->getConfigData('maxweight')){
			//Weight exceeded limit
			$error->setErrorMessage("peso maximo excedido");
			$result->append($error);
			return $result;
		}
		
		$shipping_methods = array();
		
		$postmethods = explode(",", $this->getConfigData('postmethods'));
		
		foreach($postmethods as $methods)
		{
			switch ($methods){
					case 0:
						$shipping_methods["Entrega Propria"] = array ("Transportadora - 1 a 2 dias úteis", "1");
						break;
			}			
		}
		foreach($shipping_methods as $shipping_method => $shipping_values){
        
		
			$method = Mage::getModel('shipping/rate_result_method');
			
			$method->setCarrier($this->_code);
	        $method->setCarrierTitle($this->getConfigData('name'));
			
	   	    $method->setMethod($shipping_method);
	    	
			$method->setMethodTitle($shipping_values[0]);
			
			$method->setPrice($this->getConfigData('handling_fee'));
		    
			$method->setCost($this->getConfigData('handling_fee'));
	
	        $result->append($method);
        }
		
		$this->_result = $result;
		
		$this->_updateFreeMethodQuote($request);
		
		return $this->_result;
    }

	public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

}
