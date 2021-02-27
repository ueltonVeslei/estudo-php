<?php
class Biostore_Pontosweb_DomController extends Mage_Core_Controller_Front_Action {

	public function todosprodutosAction(){
		
		try {
	
			$filtro = $this->getRequest()->getParam('filtro');
			
			if (!$filtro){
				die();
			}
			
			if ($filtro == '1'){
				$colecao = Mage::getModel('catalog/product')->getCollection()
				->setStoreId('1')
				->addFieldToFilter('entity_id',array('lt'=>'6000'));
			}elseif ($filtro == '2'){
				$colecao = Mage::getModel('catalog/product')->getCollection()
				->setStoreId('1')
				->addFieldToFilter('entity_id',array('gt'=>'5999'))
				->addFieldToFilter('entity_id',array('lt'=>'12000'));
			}elseif ($filtro == '3'){
				$colecao = Mage::getModel('catalog/product')->getCollection()
				->setStoreId('1')
				->addFieldToFilter('entity_id',array('gt'=>'11999'));
			}else{
				die();
			}
			
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
				
			$pai = $doc->createElement("produtos");
			$doc->appendChild($pai);
	
			/*
			$colecao = Mage::getModel('catalog/product')->getCollection()
			->setStoreId('1')
			->addFieldToFilter('entity_id',array('lt'=>'6000'));
			
			        // ->addFieldToFilter('entity_id',array('gt'=>'5999'))
					// ->addFieldToFilter('entity_id',array('lt'=>'12000'));
			//		 ->addFieldToFilter('entity_id',array('gt'=>'11999'));
			*/
			
			$totalcolecao = count($colecao);
			
			$contar = 0;
			foreach ($colecao as $value){
				
				$produto = Mage::getModel('catalog/product');
				$produto->load($value['entity_id']);
	
				$stockItem = $produto->getStockItem();
				$estoque = $stockItem->getIsInStock();
	
				if ($estoque == 1 && $produto->getStatus() == 1)
						$status = '1';
				else
						$status = '0';
								
				$price = number_format($produto->getSpecialPrice(), 2);
								
				if ($price != '0.00'){
	
					$categoria = Mage::getModel('catalog/category');
					$subcategoria = Mage::getModel('catalog/category');
									
					$idsubcategorias = $produto->getCategoryIds();

					//if(in_array('26', $idsubcategorias) || in_array('28', $idsubcategorias) || in_array('295', $idsubcategorias)){
					//	continue;		
					//}else{
					
					$contarsub = count($idsubcategorias) - 1;
					
					if ($contarsub >= 0){
								
						$subcategoria->load($idsubcategorias[$contarsub]);
										
						$categoria->load($subcategoria->getParentId());
										
						$nomesubcategoria = $subcategoria->getName();
						$nomecategoria = $categoria->getName();
						
						$blacklist_categorias = array(
							"Produtos da Home","Produtos da Home Natura","Busca de Produtos",
							"Derma Nail","Vagisil","Fire Up","Gilette Mach 3","Centrum","Óleo de Coco",
							"Accu-Chek Performa","MP","Acquasome","Grecin","Blowtex","Eparema","Accutrend",
							"Oenobiol","Dermacyd","FibrAlive","Dermo Spa","accutrendecoagucchek","Algasiv",
							"Óleo de Abacate","Equaliv","Nutraway","Resultados para Aparelho de Pressão",
							"Resultados para Bolsas Térmicas","Resultados para Primeiros Socorros",
							"Resultados para Protetor Facial","Resultados para Lancetas Accu-Chek",
							"Resultados para 'oh2 nutrition'","Resultados para Psylium","Agecare",
							"Categorias e Campanhas","Mantecorp Dermo e Risque","Long Life","Arpoador Cosméticos",
							"Loja do Prazer","Integralmédica","PayPal","Filabé","Datas Especiais","Dia das Crianças",
							"Dia do Cliente","Dia dos Pais","Dia das Mães","Mãe Coruja","Mãe de Primeira Viagem","Mãe Esportista",
							"Mãe Moderna","Mãe Tradicional","Mãe Vaidosa","Dia do Médico","Halloween","Presentes de Natal",
							"Dia dos Namorados","Presentes para namorada","Presentes para namorado","Esquente a relação",
							"Medicamentos","Teste Categoria","Vida Sexual","Anticoncepcional","Endoceptivo","Implante Subcutâneo",
							"Pílulas","Pílula do Dia Seguinte","Anel Vaginal","DIUs","Injetáveis","Adesivos Transdérmicos",
							"Genéricos","Genéricos Abbott","Genéricos Alcon","Genéricos Biosintética","Genéricos Brainfarma",
							"Genéricos Cristália","Genéricos EMS","Genéricos Eurofarma","Genéricos Germed","Genéricos Medley",
							"Genéricos Merck","Genéricos Neoquímica","Genéricos Ranbaxy","Genéricos Ratiopharm","Genéricos Sandoz",
							"Genéricos Teuto","Genéricos União Química","Tarjados","Controlados","Insulinas","Dr.Veit","Flora Intestinal",
							"Pingi","Planet Cosmetics","Planet Sabonete Grátis","Revista","Sigvaris","Fabricantes","Saldão de Natal",
							"Ache","Astrazeneca","Bayer","BD","Coolsense","Curaprox","Eurofarma","EMS","Escova Dental Tepe","G-Tech",
							"Germed","Glaxosmithkline","Johnson & Johnson","La Roche Posay","L'oreal","Mantecorp","Medley",
							"Melora Derme","Myralis Pharma","Nestle Health Science","Novartis","Pfizer","Vichy","Roche Diagnostics",
							"Roche","Sanofi Aventis","Tepe","Ferrosan","Dermaglós","Coaguchek","Acte","Beurer","Wiso","Bioshape",
							"Produtos sem categoria","Rede Vida","Bioland","Programa Dose Dupla","Revista 0708","Pharmaton",
							"Ofertas","Programas de Descontos","Bayer pra Você","Conexão Saúde","Cuidados pela Vida","Faz Bem",
							"Janssen","Lilly Melhor pra Você","Mais Pfizer","Melhor Idade","Receita de Vida","Saúde Extra","Saúde Fácil",
							"Sou Mais Vida","Vale Mais Saúde","Veja Bem","Vida Mais","Root Catalog","Accu-Check Delivery","Accu-Check",
							"Sol Brasil","Loja Heel","Heel"
							);
						
						if(in_array($nomecategoria, $blacklist_categorias)){
							continue;		
						}
						if (in_array($nomesubcategoria, $blacklist_categorias)){
							continue;
						}	
						
					}else{

						$nomesubcategoria = " ";
						$nomecategoria = " ";
										
					}
					

					$descricaocurta =  $produto->getName();
					//$descricaocurta = str_replace('"', "'", $produto->getShortDescription());
					//$descricaocurta = html_entity_decode($descricaocurta);
					//$descricaocurta = str_replace("&#160;", ' ', $descricaocurta);
					
					$descricaolonga = str_replace('"', "'", $produto->getDescription());
					//$descricaolonga = html_entity_decode($descricaolonga);
					//$descricaolonga = str_replace("&#160;", ' ', $descricaolonga);
					
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
					$attDescricaoCurta->appendChild($doc->createTextNode($descricaocurta));
					$filho->appendChild($attDescricaoCurta);
	
					$attDescricaoLonga = $doc->createAttribute("descricaolonga");
					$attDescricaoLonga->appendChild($doc->createTextNode($descricaolonga));
					$filho->appendChild($attDescricaoLonga);
	
					$attImagem = $doc->createAttribute("imagem");
					$attImagem->appendChild($doc->createTextNode($produto->getImageUrl()));
					$filho->appendChild($attImagem);
	
					$pai->appendChild($filho);
					
					$contar++;
					//}
					
				}
				
				
				$nomesubcategoria = " ";
				$nomecategoria = " ";
	
			}
			
			$total = $doc->createAttribute("total");
			$total->appendChild($doc->createTextNode($contar));
			$pai->appendChild($total);
	
			$path_to_save_xml = Mage::getBaseDir().'/pontosweb/';
        
			if(is_dir($path_to_save_xml)){
				$doc->save($path_to_save_xml.'pontosweb.xml');
			}
			else{
				mkdir($path_to_save_xml, 0777);
			    $doc->save($path_to_save_xml.'pontosweb.xml');
			}

			echo 'ok';		        
			exit;
	
		} catch (Exception $e) {
			echo  $e->getMessage(), "\n";	
		}
	}
}