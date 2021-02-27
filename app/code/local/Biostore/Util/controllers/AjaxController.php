<?php		

ini_set('display_errors',1);
		
class Biostore_Util_AjaxController extends Mage_Core_Controller_Front_Action {
	
    public function consultaCepJapaoAction() {
	
		$cep = urlencode(trim($this->getRequest()->getParam('cep', false)));
		$tipo = str_replace('%3A', ':', urlencode(trim($this->getRequest()->getParam('tipo', false))));
		
		$frete = Mage::getModel('util/cepJapao')->consultaCep($cep);
		
		$js = '';
		if (count($frete) > 0) {
			/*Array
			(
				[id] => 90
				[cep] => 10000
				[provincia] => hokkaido
				[cidade] => sapporoshikitaku
				[distrito] => ikanikesaiganaibai
			)*/
			$js .= "$('".$tipo."street1').value = '" . $frete['distrito'] . "';";
			$js .= "$('".$tipo."city').value = '" . $frete['cidade'] . "';";
			$js .= "$('".$tipo."region').value = '" . $frete['provincia'] . "';";
		} else {
			$js .= "$('".$tipo."street1').value = '';";
			$js .= "$('".$tipo."city').value = '';";
			$js .= "$('".$tipo."region').value = '';";
		}
		echo $js;
	}
	
	private function la($array) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
} 