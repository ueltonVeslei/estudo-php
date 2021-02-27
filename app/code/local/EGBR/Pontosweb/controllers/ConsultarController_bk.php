<?php
class Egbr_Pontosweb_ConsultarController extends Mage_Core_Controller_Front_Action {

	public function estoqueAction() {
		
		ob_start();
		ob_end_clean();
		ob_start();
		
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/xml; charset=utf-8');
		header("Content-Type: text/xml");

		$usuario = $this->getRequest()->getParam('usuario');
		$token = $this->getRequest()->getParam('token');
		$de = $this->getRequest()->getParam('de');
		$ate = $this->getRequest()->getParam('ate');
		$maximo = 500;

		try {

			$cliente = new SoapClient(Mage::getBaseUrl().'/api/?wsdl');

			$session = $cliente->login($usuario, $token);

			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
				
			$pai = $doc->createElement("produtos");
			$doc->appendChild($pai);
			
			$colecao = Mage::getModel('catalog/product')
			->getCollection()
			->addFieldToFilter('entity_id',array('from'=>$de,'to'=>$ate));
			
			$totalcolecao = count($colecao);
			
			if ($de == null && $ate == null){

				$total = $doc->createAttribute("total");
				$total->appendChild($doc->createTextNode($totalcolecao));
				$pai->appendChild($total);
				
				$max = $doc->createAttribute("limitemaximo");
				$max->appendChild($doc->createTextNode($maximo));
				$pai->appendChild($max);
				
				echo $doc->saveXML();
				$cliente->endSession($session);
			
			}else{

				if ($de < $ate){

					if ($totalcolecao <= $maximo){

						
						$contando = 0;
						foreach ($colecao as $value){

							$produto = Mage::getModel('catalog/product');
							$produto->load($value->entity_id);
							
							$estoque = $produto->getStockItem()->getIsInStock();
							
							if ($estoque == 1 && $produto->getStatus() == 1)
								$status = '1';
							else
								$status = '0';

							$price = number_format($produto->getSpecialPrice(), 2);
								
							if ($price != '0.00'){
								
								$idsubcategorias = $produto->getCategoryIds();
								
								if(in_array('26', $idsubcategorias) || in_array('295', $idsubcategorias)){
								
								}else{
								
							
								$filho = $doc->createElement("produto");
							
								$codigo = $doc->createAttribute("codigo");
								$codigo->appendChild($doc->createTextNode($produto->getSku()));
								$filho->appendChild($codigo);
							
								$preco = $doc->createAttribute("preco");
								$preco->appendChild($doc->createTextNode($price));
								$filho->appendChild($preco);
							
								$habilitado = $doc->createAttribute("habilitado");
								$habilitado->appendChild($doc->createTextNode($status));
								$filho->appendChild($habilitado);
							
								$pai->appendChild($filho);
								
								$contando++;
							}

						}

						}
						
						$total = $doc->createAttribute("total");
						$total->appendChild($doc->createTextNode($contando));
						$pai->appendChild($total);
						
						echo $doc->saveXML();
						$cliente->endSession($session);

					}else{
						echo "<erro>Consulta: $totalcolecao produtos. Limite maximo por consulta: $maximo produtos.</erro>";
						die();
					}
				}else{
					echo "<erro>Consulta invalida</erro>";
					die();
				}
			}
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";
		}
		
	}
	
	public function produtoAction(){
		
		ob_start();
		ob_end_clean();
		ob_start();
		
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/xml; charset=utf-8');
		header("Content-Type: text/xml");
		
		$sku = $this->getRequest()->getParam('sku');
		$usuario = $this->getRequest()->getParam('usuario');
		$token = $this->getRequest()->getParam('token');
		
		try {
			
			$cliente = new SoapClient(Mage::getBaseUrl().'api/?wsdl');
			
			$session = $cliente->login($usuario, $token);
			
			$produto = $cliente->call($session, 'product.info', $sku.' ');
			
			$produtox = Mage::getModel('catalog/product');
			$produtox->load($produto['product_id']);
			
			$estoque = $produtox->getStockItem()->getIsInStock();
			
			if ($estoque == 1 && $produtox->getStatus() == 1)
				$status = '1';
			else
				$status = '0';
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
				
			$pai = $doc->createElement("produtos");
			$doc->appendChild($pai);
			
			$filho = $doc->createElement("produto");
			
			$codigo = $doc->createAttribute("codigo");
			$codigo->appendChild($doc->createTextNode($sku));
			$filho->appendChild($codigo);
			
			$attNome = $doc->createAttribute("nome");
			$attNome->appendChild($doc->createTextNode($produto['name']));
			$filho->appendChild($attNome);
			
			$attPreco = $doc->createAttribute("preco");
			$attPreco->appendChild($doc->createTextNode(number_format($produto['special_price'], 2)));
			$filho->appendChild($attPreco);
			
			$attHabilitado = $doc->createAttribute("habilitado");
			$attHabilitado->appendChild($doc->createTextNode($status));
			$filho->appendChild($attHabilitado);
			
			$pai->appendChild($filho);
			
			echo $doc->saveXML();
			$cliente->endSession($session);
			
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";
		}
	}

	public function freteAction(){
		
		ob_start();
		ob_end_clean();
		ob_start();
		
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/xml; charset=utf-8');
		header("Content-Type: text/xml");
		
		try {
				
			$usuario = $this->getRequest()->getParam('usuario');
			$token = $this->getRequest()->getParam('token');
			$cep = $this->getRequest()->getParam('cep');
			
			$contarSku = ((count($this->getRequest()->getParams()) -3)/2)+1;
			
			for($i = 1; $i < $contarSku; $i++){
				
				$sku = $this->getRequest()->getParam('sku'.$i);
				$qty = $this->getRequest()->getParam('qty'.$i);
				
				if (!$sku || !$qty){
					echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<erro>SKU ou quantidade invalida</erro>";
					die();
				}
				
				$arrayProdutos[] = array( 'sku' =>  $sku,
										  'qty' =>  $qty );
			}
				
			$cliente = new SoapClient(Mage::getBaseUrl().'api/?wsdl');
			
			$session = $cliente->login($usuario, $token);
			
			$store_id = 1;
			$website_id = 1;
			
			$idCarrinho = $cliente->call($session, 'cart.create', array($store_id));
			
			$compradorGuest = array(
					'mode'       => 'guest',
					'firstname'  => 'teste',
					'lastname'   => 'teste',
					'email'      => 'teste@teste.com.br',
					'dob'        => 'teste',
					'taxvat'     => 'teste'
						
			);
			
			$resultadoSetandoComprador = $cliente->call($session, 'cart_customer.set', array($idCarrinho, $compradorGuest));
			
			$arrEndereco = array(
					array(
							'mode'              => 'billing',
							'use_for_shipping'  => 1,
							'firstname'         => 'teste',
							'lastname'          => 'teste',
							'email'             => 'teste@teste.com.br',
							'street'            => 'teste',
							'region'            => 'Rio Grande do Sul',
							'region_id'         => '339',
							'city'              => 'Porto Alegre',
							'country_id'        => 'BR',
							'postcode'          => $cep,
							'telephone'         => '0000000000',
							'is_default_billing'  => true,
							'is_default_shipping' => true,
					)
			);
			
			$resultadoEnderecoComprador = $cliente->call($session, "cart_customer.addresses", array($idCarrinho, $arrEndereco));
			
			$resultadoAdicionandoProduto = $cliente->call($session, "cart_product.add", array($idCarrinho, $arrayProdutos));
			
			$formasPagamento = $cliente->call($session, "cart_shipping.list", $idCarrinho);
			
			$frete = '';
			foreach ($formasPagamento as $pagamento){
				
				$titulopagamento = explode(" ", $pagamento["method_title"]);
				
				$titulopagamento = str_replace("-", "", $titulopagamento[0]);
				
				$precopagamento = $pagamento['price'];
				
				$frete .= ' '.strtolower($titulopagamento).'="'.number_format($precopagamento,2).'"';
				
			}
			
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
			echo "<frete $frete />";
			$cliente->endSession($session);
					
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";
		}
	}
}