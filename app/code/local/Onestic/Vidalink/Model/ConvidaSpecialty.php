<?php
class Onestic_Vidalink_Model_ConvidaSpecialty {
	
	public function confirmOrder($orderId) {
		Mage::helper('onestic_vidalink')->log('Chegou no confirmOrder - Pedido: ' . $orderId);
		$order = Mage::getModel('sales/order')->load($orderId);
		if (!$order->getOvConfirmed()) {
			Mage::helper('onestic_vidalink')->log('Chegou no confirmOrder - Primeiro IF - Pedido: ' . $orderId);
			$nfKey = null;
			foreach ($order->getStatusHistoryCollection() as $status) {
				if (strpos($status->getComment(), 'NF ') !== false) {
					$nfKey = str_replace('NF ','',$status->getComment());
					break;
				}
			}
			 
			if ($nfKey) {
				Mage::helper('onestic_vidalink')->log('Carregou NF - Pedido: ' . $orderId);
				Mage::helper('onestic_vidalink')->log('NF: ' . $nfKey);
				$client = new SoapClient('https://www.vidalink.com.br/ConvidaSpecialtyWS/ConvidaSpecialtyWS.asmx?WSDL');
				$params = array(
					'pedidoConfirmacaoDTO' => array(
						'IdPedido'					=> $order->getId(),
						'AutorizacaoVidalink'		=> $order->getData('ov_autorizacao'),
						'DataVenda'                 => str_replace(' ','T',$order->getCreatedAt()),
						'NotaFiscal'	            => 1,
						'CnpjEstabelecimento'		=> Mage::helper('onestic_vidalink')->getCnpjEmissorNf(),
						'NrPDV'                     => Mage::helper('onestic_vidalink')->getPdv(),
						'ChaveNotaFiscal'			=> $nfKey
					)
				);
				try {
					Mage::helper('onestic_vidalink')->log($params);
					$result = $client->EnviarConfirmacaoPedido((object)$params);
					Mage::helper('onestic_vidalink')->log('Retorno da VidaLink - Pedido: ' . $orderId);
					Mage::helper('onestic_vidalink')->log($result);
					if ($result->EnviarConfirmacaoPedidoResult->Demonstrativo) {
						$order->setOvConfirmed('1');
						$order->addStatusHistoryComment($result->EnviarConfirmacaoPedidoResult->Demonstrativo);
						$order->save();
					}
				} catch(Exception $e) {
					Mage::helper('onestic_vidalink')->log('ERRO: ' . $e->getMessage());
				}
			}
		}
	}
	
}