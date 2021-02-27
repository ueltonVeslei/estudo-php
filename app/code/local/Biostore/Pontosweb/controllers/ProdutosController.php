<?php
class Biostore_Pontosweb_ProdutosController extends Mage_Core_Controller_Front_Action {
	
	public function listarAction(){
		
		ob_start();
		ob_end_clean();
		ob_start();

		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/xml; charset=utf-8');
		header("Content-Type: text/xml");

		$usuario = $this->getRequest()->getParam('usuario');
		$token = $this->getRequest()->getParam('token');

		try {
				
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$cliente = new SoapClient(Mage::getBaseUrl().'/api/?wsdl');
			$session = $cliente->login($usuario, $token);
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			$pai = $doc->createElement("produtos");
			$doc->appendChild($pai);

			$select = "SELECT DISTINCT product_id FROM  `catalog_category_product`
						WHERE `category_id` = '27' OR `category_id` = '48' OR `category_id` = '49' OR `category_id` = '50'
						OR `category_id` = '51' OR `category_id` = '52' OR `category_id` = '56'	OR `category_id` = '62'
						OR `category_id` = '63'	OR `category_id` = '64'	OR `category_id` = '66' OR `category_id` = '67'
						OR `category_id` = '74'	OR `category_id` = '75'	OR `category_id` = '76'	OR `category_id` = '77'
						OR `category_id` = '78'	OR `category_id` = '79'	OR `category_id` = '80'	OR `category_id` = '81'
						OR `category_id` = '82'	OR `category_id` = '92'	OR `category_id` = '94'	OR `category_id` = '107'
						OR `category_id` = '114' OR `category_id` = '165' OR `category_id` = '200' OR `category_id` = '223'
						OR `category_id` = '305' OR `category_id` = '329' OR `category_id` = '340' OR `category_id` = '341'
						OR `category_id` = '589' OR `category_id` = '590' OR `category_id` = '593' OR `category_id` = '595'
						OR `category_id` = '596' OR `category_id` = '597' OR `category_id` = '598' OR `category_id` = '599'
						OR `category_id` = '600' OR `category_id` = '601' OR `category_id` = '602' OR `category_id` = '603'
						OR `category_id` = '605' OR `category_id` = '615' OR `category_id` = '616' OR `category_id` = '640'
						OR `category_id` = '642' OR `category_id` = '713' OR `category_id` = '714' OR `category_id` = '734'
						OR `category_id` = '735' OR `category_id` = '736' OR `category_id` = '738' OR `category_id` = '739'
						OR `category_id` = '740' OR `category_id` = '743' OR `category_id` = '744' OR `category_id` = '746'
						OR `category_id` = '747' OR `category_id` = '753' OR `category_id` = '754' OR `category_id` = '756'
						OR `category_id` = '757' OR `category_id` = '758' OR `category_id` = '767' OR `category_id` = '769'
						OR `category_id` = '770' OR `category_id` = '771' OR `category_id` = '772' OR `category_id` = '774'
						OR `category_id` = '775' OR `category_id` = '776' OR `category_id` = '777' OR `category_id` = '778'
						OR `category_id` = '779' OR `category_id` = '780' OR `category_id` = '781' OR `category_id` = '782'
						OR `category_id` = '783' OR `category_id` = '784' OR `category_id` = '785' OR `category_id` = '786'
						OR `category_id` = '787' OR `category_id` = '788' OR `category_id` = '789' OR `category_id` = '790'
						OR `category_id` = '791' OR `category_id` = '792' OR `category_id` = '793' OR `category_id` = '794'
						OR `category_id` = '795' OR `category_id` = '798' OR `category_id` = '800' OR `category_id` = '801'
						OR `category_id` = '802' OR `category_id` = '803' OR `category_id` = '804' OR `category_id` = '806'
						OR `category_id` = '807' OR `category_id` = '808' OR `category_id` = '809' OR `category_id` = '928'
						OR `category_id` = '932' OR `category_id` = '938' OR `category_id` = '946' OR `category_id` = '948'
						OR `category_id` = '950' OR `category_id` = '952' OR `category_id` = '954' OR `category_id` = '956'
						OR `category_id` = '996' OR `category_id` = '1020' OR `category_id` = '1022'
						OR `category_id` = '1026' OR `category_id` = '1028' OR `category_id` = '1030'
						OR `category_id` = '1034' OR `category_id` = '1036' OR `category_id` = '1038'
						OR `category_id` = '1040' OR `category_id` = '1042' OR `category_id` = '1044'
						OR `category_id` = '1046' OR `category_id` = '1048' OR `category_id` = '1050'
						OR `category_id` = '1052' OR `category_id` = '1054' OR `category_id` = '1056'
						OR `category_id` = '1058' OR `category_id` = '1060' OR `category_id` = '1064'
						OR `category_id` = '1066' OR `category_id` = '1068' OR `category_id` = '1070'
						OR `category_id` = '1072' OR `category_id` = '1074' OR `category_id` = '1076'
						OR `category_id` = '1078' OR `category_id` = '1080' OR `category_id` = '1082'
						OR `category_id` = '1084' OR `category_id` = '1086' OR `category_id` = '1088'
						OR `category_id` = '1092' OR `category_id` = '1094' OR `category_id` = '1098'
						OR `category_id` = '1100' OR `category_id` = '1102' OR `category_id` = '1104'
						OR `category_id` = '1106' OR `category_id` = '1108' OR `category_id` = '1116'
						OR `category_id` = '1167' OR `category_id` = '1168' OR `category_id` = '1169'
						OR `category_id` = '1170' OR `category_id` = '1172' OR `category_id` = '1200'
						OR `category_id` = '1226' OR `category_id` = '1254' OR `category_id` = '1280'
						OR `category_id` = '1290' OR `category_id` = '1354' OR `category_id` = '1444'
						OR `category_id` = '1458' OR `category_id` = '1476' OR `category_id` = '1512'
						OR `category_id` = '1520' OR `category_id` = '1552' OR `category_id` = '1626'
						OR `category_id` = '1628' OR `category_id` = '1630' OR `category_id` = '1632'
						OR `category_id` = '1634'OR `category_id` = '1636'";
			
			$categorias = $read->fetchAll($select);
				
			$contando = 0;
			foreach ($categorias as $value){

				$produto = Mage::getModel('catalog/product');
				$produto->load($value['product_id']);

				$estoque = $produto->getStockItem()->getIsInStock();

				if ($estoque == 1 && $produto->getStatus() == 1)
					$status = '1';
				else
					$status = '0';

				$price = number_format($produto->getSpecialPrice(), 2);
				
				if ($price == null || $price == '0.00'){
					$price = number_format($produto->getPrice(), 2);
				}

				if ($price != '0.00'){					$idsubcategorias = $produto->getCategoryIds();
						
					if (count($idsubcategorias) == 0 ){
						continue;
					}

					$blacklist = array('Busca de Produtos','Farma Delivery', 'Sol Brasil','Fabricantes','', ' ','Programas de Descontos',
							'Outlet', 'Dia das Mães', 'Categorias e Campanhas');
					
					$idsubcategorias = $produto->getCategoryIds();
					$contarsub = count($idsubcategorias) - 1;
					
					$valor_subcategoria = $idsubcategorias[$contarsub];
					$sqlsub = "SELECT name, parent_id
					FROM  `catalog_category_flat_store_1`
					WHERE entity_id = '{$valor_subcategoria}' LIMIT 1";
					
					$query_sub = $read->fetchAll($sqlsub);
					
					$nomesubcategoria = $query_sub[0]['name'];
					$idparent = $query_sub[0]['parent_id'];
					
					if ($nomesubcategoria == null || $nomesubcategoria == ''){
						$contarsub = $contarsub - 1;
						$valor_subcategoria = $idsubcategorias[$contarsub];
						$sqlsub = "SELECT name, parent_id
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$valor_subcategoria}' LIMIT 1";
						$query_sub = $read->fetchAll($sqlsub);
							
						$nomesubcategoria = $query_sub[0]['name'];
						$idparent = $query_sub[0]['parent_id'];
						
						$sqlcat = "SELECT name
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$idparent}' LIMIT 1";
						$query_cat = $read->fetchAll($sqlcat);
						$nomecategoria = $query_cat[0]['name'];
					}
					
					$sqlcat = "SELECT name
					FROM  `catalog_category_flat_store_1`
					WHERE entity_id = '{$idparent}' LIMIT 1";
					$query_cat = $read->fetchAll($sqlcat);
					$nomecategoria = $query_cat[0]['name'];
					
					if (in_array($nomecategoria, $blacklist)){
						
						$contarsub = $contarsub - 1;
						$valor_subcategoria = $idsubcategorias[$contarsub];
						$sqlsub = "SELECT name, parent_id
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$valor_subcategoria}' LIMIT 1";
						$query_sub = $read->fetchAll($sqlsub);
							
						$nomesubcategoria = $query_sub[0]['name'];
						$idparent = $query_sub[0]['parent_id'];
						
						$sqlcat = "SELECT name
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$idparent}' LIMIT 1";
						$query_cat = $read->fetchAll($sqlcat);
						$nomecategoria = $query_cat[0]['name'];
						
					}
					
					if (in_array($nomecategoria, $blacklist)){
					
						$contarsub = $contarsub - 1;
						$valor_subcategoria = $idsubcategorias[$contarsub];
						$sqlsub = "SELECT name, parent_id
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$valor_subcategoria}' LIMIT 1";
						$query_sub = $read->fetchAll($sqlsub);
							
						$nomesubcategoria = $query_sub[0]['name'];
						$idparent = $query_sub[0]['parent_id'];
							
						$sqlcat = "SELECT name
							FROM  `catalog_category_flat_store_1`
							WHERE entity_id = '{$idparent}' LIMIT 1";
							$query_cat = $read->fetchAll($sqlcat);
							$nomecategoria = $query_cat[0]['name'];
					
					}
					
					if (in_array($nomecategoria, $blacklist)){
					
						$contarsub = $contarsub - 1;
						$valor_subcategoria = $idsubcategorias[$contarsub];
						$sqlsub = "SELECT name, parent_id
						FROM  `catalog_category_flat_store_1`
						WHERE entity_id = '{$valor_subcategoria}' LIMIT 1";
						$query_sub = $read->fetchAll($sqlsub);
							
						$nomesubcategoria = $query_sub[0]['name'];
						$idparent = $query_sub[0]['parent_id'];
						
						$sqlcat = "SELECT name
							FROM  `catalog_category_flat_store_1`
							WHERE entity_id = '{$idparent}' LIMIT 1";
							$query_cat = $read->fetchAll($sqlcat);
							$nomecategoria = $query_cat[0]['name'];
					
					}
					
					if ($nomesubcategoria == null || $nomesubcategoria == ''){
						continue;
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
			}

			$total = $doc->createAttribute("total");
			$total->appendChild($doc->createTextNode($contando));
			$pai->appendChild($total);

			print $doc->saveXML();
			$cliente->endSession($session);

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

			//echo $session;
			//die();
			
			
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

			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select_region = "SELECT region_id FROM directory_country_region WHERE code = '{$estado}'";
			$region_id = $read->fetchAll($select_region);
			
			$idregiao = $region_id[0]['region_id'];
			
			
			//var_dump($idregiao);
			//die();
			
			
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
							'region_id'         => $idregiao,//'339',
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
			$orders->setCustomerEmail('pedidosfarmadelivery@pontosweb.com.br');
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

			/*
			echo '<pre>';
			var_dump($totalCarrinho);
			echo '</pre>';
			die();
			*/
			
			if (count($totalCarrinho) == '3'){
				
				$totaldosprodutos = number_format($totalCarrinho[0]['amount'], 2);
				$totaldofrete = number_format($totalCarrinho[1]['amount'], 2);
				$desconto = number_format($totalCarrinho[3]['amount'], 2);
				$totaldopedido = number_format($totalCarrinho[2]['amount'], 2);
				
			}else{
				
				$totaldosprodutos = number_format($totalCarrinho[0]['amount'], 2);
				$totaldofrete = number_format($totalCarrinho[2]['amount'], 2);
				$desconto = number_format($totalCarrinho[1]['amount'], 2);
				$totaldopedido = number_format($totalCarrinho[3]['amount'], 2);
				
			}
			
			/*
			$totaldosprodutos = number_format($totalCarrinho[0]['amount'], 2);
			$totaldofrete = number_format($totalCarrinho[2]['amount'], 2);
			$desconto = number_format($totalCarrinho[1]['amount'], 2);
			//$totaldopedido = $totaldosprodutos + $totaldofrete;
			
			$totaldopedido = number_format($totalCarrinho[3]['amount'], 2);
			*/

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