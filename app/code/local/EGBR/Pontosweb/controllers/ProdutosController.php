<?php
class Egbr_Pontosweb_ProdutosController extends Mage_Core_Controller_Front_Action {
	
	public function listarAction(){
		
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
			
			$colecao = Mage::getModel('catalog/product')->getCollection()
			->setStoreId('1')
			->addFieldToFilter('entity_id',array('from'=>$de,'to'=>$ate));
			
			$totalcolecao = count($colecao);
			
			if ($de == null || $ate == null){
				
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
							$produto->load($value['entity_id']);
							 
							$estoque = $produto->getStockItem()->getIsInStock();
								
							if ($estoque == 1 && $produto->getStatus() == 1)
								$status = '1';
							else
								$status = '0';
							
							$price = number_format($produto->getSpecialPrice(), 2);
							
							if ($price != '0.00'){

								$categoria = Mage::getModel('catalog/category');
								$subcategoria = Mage::getModel('catalog/category');
									
								$idsubcategorias = $produto->getCategoryIds();

								if(in_array('28', $idsubcategorias) || in_array('295', $idsubcategorias)){
										
								}else{
									
									
									
								
								$contarsub = count($idsubcategorias) - 1;
								
								if ($contarsub >= 0){
								
									$subcategoria->load($idsubcategorias[$contarsub]);
										
									$categoria->load($subcategoria->getParentId());
										
									$nomesubcategoria = $subcategoria->getName();
									$nomecategoria = $categoria->getName();

									
									if ($nomecategoria == 'Root Catalog'){
										continue;
									}
									
									if ($nomecategoria == 'Accu-Check Delivery'){
										continue;
									}
									
									if ($nomecategoria == 'Accu-Check'){
										continue;
									}
									
									if ($nomecategoria == 'Sol Brasil'){
										continue;
									}
									
									if ($nomecategoria == 'Loja Heel'){
										continue;
									}
									
									if ($nomecategoria == 'Heel'){
										continue;
									}
									
									if ($nomesubcategoria == 'Root Catalog'){
										continue;
									}
									
									if ($nomecategoria == 'Accu-Check Delivery'){
										continue;
									}
									
									if ($nomecategoria == 'Accu-Check'){
										continue;
									}
									
									if ($nomesubcategoria == 'Sol Brasil'){
										continue;
									}
									
									if ($nomesubcategoria == 'Loja Heel'){
										continue;
									}
									
									if ($nomecategoria == 'Heel'){
										continue;
									}
									

								}else{

									$nomesubcategoria = " ";
									$nomecategoria = " ";
										
								}
									
								$descricaocurta = str_replace('"', "'", $produto->getShortDescription());
								$descricaolonga = str_replace('"', "'", $produto->getDescription());
								
								$filho = $doc->createElement("produto");

								$codigo = $doc->createAttribute("codigo");
								$codigo->appendChild($doc->createTextNode($produto->getSku()));
								$filho->appendChild($codigo);

								$attCategoria = $doc->createAttribute("categoria");
								$attCategoria->appendChild($doc->createTextNode($nomecategoria));
								$filho->appendChild($attCategoria);

								$attSubCategoria = $doc->createAttribute("subcategoria");
								$attSubCategoria->appendChild($doc->createTextNode($nomesubcategoria));
								$filho->appendChild($attSubCategoria);

								$preco = $doc->createAttribute("preco");
								$preco->appendChild($doc->createTextNode($price));
								$filho->appendChild($preco);
								
								$habilitado = $doc->createAttribute("habilitado");
								$habilitado->appendChild($doc->createTextNode($status));
								$filho->appendChild($habilitado);
								
								$attDescricaoCurta = $doc->createAttribute("descricaocurta");
								$attDescricaoCurta->appendChild($doc->createTextNode($produto->getName()));
								$filho->appendChild($attDescricaoCurta);
								
								$attDescricaoLonga = $doc->createAttribute("descricaolonga");
								$attDescricaoLonga->appendChild($doc->createTextNode($descricaolonga));
								$filho->appendChild($attDescricaoLonga);
								
								$attImagem = $doc->createAttribute("imagem");
								$attImagem->appendChild($doc->createTextNode($produto->getImageUrl()));
								$filho->appendChild($attImagem);
								
								$pai->appendChild($filho);
							
							$contando++;
							}
							/* termina a consulta do array */

						}
						
						}
						
						$total = $doc->createAttribute("total");
						$total->appendChild($doc->createTextNode($contando));
						$pai->appendChild($total);
						
						print $doc->saveXML();
						$cliente->endSession($session);

					}else{
						echo "<erro>Consulta: $totalcolecao produtos. Limite maximo por consulta: $maximo produtos.</erro>";
						$cliente->endSession($session);				
					}
		  }else{
		  	echo "<erro>Consulta invalida</erro>";
		  	$cliente->endSession($session);
		  }
			}
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";
			$cliente->endSession($session);
		}
	}
	
	public function  criarpedidoAction() {
		
		ob_start();
		ob_end_clean();
		ob_start();
		
		try {
		
			$response = $this->getResponse();
			$response->setHeader('Content-Type', 'text/xml; charset=utf-8');
			header("Content-Type: text/xml");
				
			$usuario = $this->getRequest()->getParam('usuario');
			$token = $this->getRequest()->getParam('token');
			$codpw = $this->getRequest()->getParam('codpw');
			$codcliente = $this->getRequest()->getParam('codcliente');
			$tipo = $this->getRequest()->getParam('tipo');
				
			//dados do usuario
			$nome = $this->getRequest()->getParam('nome');
			$sobrenome = $this->getRequest()->getParam('sobrenome');
			$email = $this->getRequest()->getParam('email');
		
			//dados de endereco
			$endereco = $this->getRequest()->getParam('endereco');
			$cidade = $this->getRequest()->getParam('cidade');
			$estado = $this->getRequest()->getParam('estado');
			$cep = $this->getRequest()->getParam('cep');
			$telefone = $this->getRequest()->getParam('telefone');
			$dtnasc = $this->getRequest()->getParam('dtnasc');
			$cpf = $this->getRequest()->getParam('cpf');
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			
			$contarSku = ((count($this->getRequest()->getParams()) -15)/2)+1;
				
			for($i = 1; $i < $contarSku; $i++){
		
				$sku = $this->getRequest()->getParam('sku'.$i);
				$qty = $this->getRequest()->getParam('qty'.$i);
		
				if (!$sku || !$qty){
					
					$pai = $doc->createElement("erro");
					$pai->appendChild(
							$doc->createTextNode('SKU ou quantidade invalida')
					);
					$doc->appendChild($pai);
					
					echo $doc->saveXML();
					die();
				}
		
				$arrayProdutos[] = array( 'sku' =>  $sku,
									 'qty' =>  $qty );
			}
			
			$cliente = new SoapClient(Mage::getBaseUrl().'/api/?wsdl');
			
			$session = $cliente->login($usuario, $token);
				
			$store_id = 1;
			$website_id = 1;
				
			$idCarrinho = $cliente->call($session, 'cart.create', array($store_id));
				
			$compradorGuest = array(
					'mode'       => 'guest',
					'firstname'  => $nome,
					'lastname'   => $sobrenome,
					'email'      => $email,
					'dob'        => $dtnasc,
					'taxvat'     => $cpf
			);
				
			$resultadoSetandoComprador = $cliente->call($session, 'cart_customer.set', array($idCarrinho, $compradorGuest));
				
			$arrEndereco = array(
					array(
							'mode'              => 'billing',
							'use_for_shipping'  => 1,
							'firstname'         => $nome,
							'lastname'          => $sobrenome,
							'email'             => $email,
							'street'            => array($endereco),
							'city'              => $cidade,
							'region'            => $estado,
							'region_id'         => '339',
							'country_id'        => 'BR',
							'postcode'          => $cep,
							'telephone'         => $telefone,
							'is_default_billing'  => true,
							'is_default_shipping' => true,
					)
			);
			
			$resultadoEnderecoComprador = $cliente->call($session, "cart_customer.addresses", array($idCarrinho, $arrEndereco));
				
			$resultadoAdicionandoProduto = $cliente->call($session, "cart_product.add", array($idCarrinho, $arrayProdutos));
				
			$metodos = $cliente->call($session, "cart_shipping.list", array($idCarrinho));

			$formaPagamento = '';
			foreach ($metodos as $metodo){
			
				$titulometodo = explode(" ", $metodo["method_title"]);
				$titulometodo = strtolower(str_replace("-", "", $titulometodo[0]));
				
				if ($tipo == $titulometodo){
					$formaPagamento = $metodo["code"];
				}
	
			}
			
			$resultadoFormaPagamento = $cliente->call($session, "cart_shipping.method", array($idCarrinho, $formaPagamento));

			$metodoPagamento = array('method' => 'braspagboleto');
				
			$resultadoMetodoPagamento = $cliente->call($session, "cart_payment.method", array($idCarrinho, $metodoPagamento));
				
			$totalCarrinho = $cliente->call($session, "cart.totals", array($idCarrinho));
				
			$resultadoCriarPedido = $cliente->call($session, "cart.order", array($idCarrinho, null));
				
			$orders = Mage::getModel('sales/order');
			$orders->loadByIncrementId($resultadoCriarPedido);
			$orders->setCustomerEmail('emerson.silva@egbr.com.br');
			$orders->setCustomerFirstname('Pontosweb');
			$orders->setCustomerLastname(' ');
		
			$orders->setState(Mage_Sales_Model_Order::STATE_HOLDED);
			$orders->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
			
			$pedidos = $orders->getBillingAddress();
			$pedidos->setFirstname('Pontosweb - '.$codcliente.' - '.$codpw);
			$pedidos->setLastname(' ');
			$pedidos->save();
			
			$orders->save();
				
			//$orders->sendNewOrderEmail();
				
			$totaldosprodutos = number_format($totalCarrinho[0]['amount'], 2);
			$totaldofrete = number_format($totalCarrinho[2]['amount'], 2);
			$desconto = number_format($totalCarrinho[1]['amount'], 2);
			//$totaldopedido = $totaldosprodutos + $totaldofrete;
			
			$totaldopedido = number_format($totalCarrinho[3]['amount'], 2);

			$pai = $doc->createElement("pedido");
			$doc->appendChild($pai);
			
			$attProdutos = $doc->createAttribute("totalprodutos");
			$attProdutos->appendChild($doc->createTextNode($totaldosprodutos));
			$pai->appendChild($attProdutos);
			
			$attFrete = $doc->createAttribute("valorfrete");
			$attFrete->appendChild($doc->createTextNode($totaldofrete));
			$pai->appendChild($attFrete);
			
			$attDesconto = $doc->createAttribute("desconto");
			$attDesconto->appendChild($doc->createTextNode($desconto));
			$pai->appendChild($attDesconto);
			
			$attPedido = $doc->createAttribute("totalpedido");
			$attPedido->appendChild($doc->createTextNode($totaldopedido));
			$pai->appendChild($attPedido);
			
			$attCodParceiro = $doc->createAttribute("codigoparceiro");
			$attCodParceiro->appendChild($doc->createTextNode($resultadoCriarPedido));
			$pai->appendChild($attCodParceiro);
			
			$attCodPontosweb = $doc->createAttribute("codigopontosweb");
			$attCodPontosweb->appendChild($doc->createTextNode($codpw));
			$pai->appendChild($attCodPontosweb);
			
			$attCodCliente = $doc->createAttribute("codcliente");
			$attCodCliente->appendChild($doc->createTextNode($codcliente));
			$pai->appendChild($attCodCliente);
			
			echo $doc->saveXML();
			$cliente->endSession($session);
			die();
				
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";
		}
		
	}
}