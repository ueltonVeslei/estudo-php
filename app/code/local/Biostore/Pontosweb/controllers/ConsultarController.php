<?php
class Biostore_Pontosweb_ConsultarController extends Mage_Core_Controller_Front_Action {

	public function estoqueAction() {

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
				
				if ($price != '0.00'){

					$idsubcategorias = $produto->getCategoryIds();
					
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

			$total = $doc->createAttribute("total");
			$total->appendChild($doc->createTextNode($contando));
			$pai->appendChild($total);

			echo $doc->saveXML();
			$cliente->endSession($session);


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
			
		
		$price = number_format($produtox->getSpecialPrice(), 2);

		if ($price == null || $price == '0.00'){
			$price = number_format($produtox->getPrice(), 2);
		}
		
		$attPreco = $doc->createAttribute("preco");
		$attPreco->appendChild($doc->createTextNode($price));
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