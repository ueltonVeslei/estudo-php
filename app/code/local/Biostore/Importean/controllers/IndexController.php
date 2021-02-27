<?php
class Biostore_Importean_IndexController extends Mage_Adminhtml_Controller_Action{
	
	public function getDate(){
		return date("d-m-Y H:m:s", Mage::getModel("core/date")->timestamp(time()));
	}

	public function indexAction(){
		
		//echo 'teste';
		//die();
		
		$strStockPath = Mage::getBaseDir().'/tmp/importean/';

		if(!is_dir($strStockPath)){
			mkdir($strStockPath, 0777);
		}
		
		if(is_dir($strStockPath)){
			
			
			$objStockDir = dir( $strStockPath );
			$arrStockFiles = array();
			
			while(FALSE !== ($strEntry = $objStockDir->read())){
				
				if($strEntry != "." && $strEntry != ".."){
					$arrStockFiles[] = $strStockPath . $strEntry;
				}
				
			}
			
			$objStockDir->close();
			
			if(count($arrStockFiles) != 1){
				
				Mage::getSingleton('adminhtml/session')->addError('Esse diretório: '.$strStockPath.' contém mais de um arquivo.');
				$this->_redirect('importean/index/file/');
				return;
				
			}else{
				
				
				
				$objFile = fopen($arrStockFiles[ 0 ], "r");
				$ok =0;
				$no =0;
				//Mage::getSingleton('adminhtml/session')->addSuccess('Inicio do processo de faturamento: '.$this->getDate());
				
				//var_dump($objFile);
				//die();
				
				
				//$arrPieces = fgetcsv($objFile, 0,",");
				
				//var_dump($arrPieces);
				//die();
				
				/*
				//array(7) { [0]=> string(3) "SKU"
							 [1]=> string(3) "EAN"
							 [2]=> string(19) "DescriÃ§Ã£o do item"
							 [3]=> string(16) "PrincÃ­pio Ativo"
							 [4]=> string(10) "Fabricante"
							 [5]=> string(17) "CÃ³digo ABC Farma"
							 [6]=> string(7) "NÂº DCB" }
				*/
				
				
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				
				$sql = "DELETE FROM  `catalog_product_ean`";
				$write->query($sql);
				
				
				//$contando = 1;
				while (($data = fgetcsv($objFile, 0, ",")) !== FALSE) {
					
					if ($data[0] == 'SKU'){
						continue;
					}
					
					//echo $contando;
					echo '<pre>';
					var_dump($data);
					echo '</pre>';
					//echo '<br>';

					die();
					
					/*
					$contando++;
					
					
					if ($data[6] == '' || $data[6] == ' '){
						$data[6] = 'nulo';
					}
					*/
					
					
					$sql = 'INSERT INTO `catalog_product_ean`
							(`sku`, `ean`, `descricao`, `principio_ativo`, `fabricante`, `abc`, `dcb`)
							VALUES ("'.$data[0].'", "'.$data[1].'", "'.$data[2].'", "'.$data[3].'", "'.$data[4].'", "'.$data[5].'", "'.$data[6].'");';
					
					//echo $sql;
					//echo '<br>';
					
					
					
					$write->query($sql);
					
					
					
				}
					# Close the File.
				fclose($objFile);
				
				//die();
				
				/*
				die();
				
				foreach ($arrPieces as $piece){

					/*
					DELETE
					FROM  `catalog_product_ean`
					
					/*
					"INSERT INTO `hom_loja_farma17`.`catalog_product_ean` (`sku`, `ean`, `descricao`, `principio_ativo`, `fabricante`, `abc`, `dcb`)
							VALUES ('123456', 13131, 'sadasda', 'asdasda', 'sadsasda', 3121, 313131351);
					";


					
					
					var_dump($piece);
					die();
					
				}
				*/
					
				Mage::getSingleton('adminhtml/session')->addSuccess('CSV inserido com sucesso!');
				//Mage::getSingleton('adminhtml/session')->addSuccess('Total de pedidos que não foram faturados: ' .$no);
				$this->_redirect('importean/index/file/');
				return;
			}
		
		}else{
			
			Mage::getSingleton('adminhtml/session')->addError('O diretório '.$strStockPath.' não existe');
			$this->_redirect('importean/index/file/');
			return;

		}
	}

	public function fileAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('importean/index_edit'));
		$this->renderLayout();
	}
	
	public function gridAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('importean/index_grid'));
		$this->renderLayout();
	}

	public function saveAction(){
			
		$strStockPath = Mage::getBaseDir().'/tmp/importean/';
		
		if(!is_dir($strStockPath)){
			mkdir($strStockPath, 0777);
		}
			
		if($this->getRequest()->isPost()){
			
			$file = (isset($_FILES['arquivo']))? $_FILES['arquivo']: array();
			
			if(preg_match('/\.(csv|CSV)$/', $file['name'])){
				
				$filename = 'importean.csv';
				
				if(file_exists($strStockPath . $filename)){
					unlink($strStockPath . $filename);
				}
					
				if(move_uploaded_file($file['tmp_name'], $strStockPath . $filename)){
					
					$strStockPath = Mage::getBaseDir().'/tmp/importean/';
					
					if(!is_dir($strStockPath)){
						mkdir($strStockPath, 0777);
					}
					
					if(is_dir($strStockPath)){
							
							
						$objStockDir = dir( $strStockPath );
						$arrStockFiles = array();
							
						while(FALSE !== ($strEntry = $objStockDir->read())){
					
							if($strEntry != "." && $strEntry != ".."){
								$arrStockFiles[] = $strStockPath . $strEntry;
							}
					
						}
							
						$objStockDir->close();
							
						if(count($arrStockFiles) != 1){
					
							Mage::getSingleton('adminhtml/session')->addError('Esse diretório: '.$strStockPath.' contém mais de um arquivo.');
							$this->_redirect('importean/index/file/');
							return;
					
						}else{
					
					
					
							$objFile = fopen($arrStockFiles[ 0 ], "r");
							$ok =0;
							$no =0;
							//Mage::getSingleton('adminhtml/session')->addSuccess('Inicio do processo de faturamento: '.$this->getDate());
					
							//var_dump($objFile);
							//die();
					
					
							//$arrPieces = fgetcsv($objFile, 0,",");
					
							//var_dump($arrPieces);
							//die();
					
							//echo $contando;
							
							
							/*
							 //array(7) { [0]=> string(3) "SKU"
							[1]=> string(3) "EAN"
							[2]=> string(19) "DescriÃ§Ã£o do item"
							[3]=> string(16) "PrincÃ­pio Ativo"
							[4]=> string(10) "Fabricante"
							[5]=> string(17) "CÃ³digo ABC Farma"
							[6]=> string(7) "NÂº DCB" }
							*/
					
					
							$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					
							$sql = "DELETE FROM  `catalog_product_ean`";
							$write->query($sql);
					
					
							//$contando = 1;
							while (($data = fgetcsv($objFile, 0, ",")) !== FALSE) {
									
								if ($data[0] == 'SKU'){
									continue;
								}
								/*
								 echo $contando;
								echo '<br>';
								var_dump($data);
								echo '<br>';
								echo '<br>';
					
								$contando++;
									
									
								if ($data[6] == '' || $data[6] == ' '){
								$data[6] = 'nulo';
								}
								*/

								/*
								echo '<pre>';
								var_dump($data);
								echo '</pre>';
								//echo '<br>';
									
								die();
								*/
									
								$sql = 'INSERT INTO `catalog_product_ean`
							(`sku`, `ean`, `descricao`, `principio_ativo`, `fabricante`, `abc`, `dcb`)
							VALUES ("'.$data[0].'", "'.$data[1].'", "'.$data[2].'", "'.$data[3].'", "'.$data[4].'", "'.$data[5].'", "'.$data[6].'");';
									
								//echo $sql;
								//echo '<br>';
									
									
									
								$write->query($sql);
									
									
									
							}
							# Close the File.
							fclose($objFile);
					
							//die();
					
							/*
							die();
					
							foreach ($arrPieces as $piece){
					
							/*
							DELETE
							FROM  `catalog_product_ean`
								
							/*
							"INSERT INTO `hom_loja_farma17`.`catalog_product_ean` (`sku`, `ean`, `descricao`, `principio_ativo`, `fabricante`, `abc`, `dcb`)
							VALUES ('123456', 13131, 'sadasda', 'asdasda', 'sadsasda', 3121, 313131351);
							";
					
					
								
								
							var_dump($piece);
							die();
								
							}
							*/
								
							Mage::getSingleton('adminhtml/session')->addSuccess('CSV inserido com sucesso!');
							//Mage::getSingleton('adminhtml/session')->addSuccess('Total de pedidos que não foram faturados: ' .$no);
							$this->_redirect('importean/adminhtml_ean/index');
							return;
						}
					
						}else{
							
						Mage::getSingleton('adminhtml/session')->addError('O diretório '.$strStockPath.' não existe');
						$this->_redirect('importean/index/file/');
						return;
					
						}
					
					//Mage::getSingleton('adminhtml/session')->addSuccess('O arquivo foi enviado com sucesso.');
				}else{
					Mage::getSingleton('adminhtml/session')->addError('O arquivo não foi enviado contate o suporte.');
				}
					
				$this->_redirect('importean/index/file/');
				return;
					
			}else{
				
				Mage::getSingleton('adminhtml/session')->addError('Arquivo inválido. Envie um arquivo com extensão csv.');
				$this->_redirect('importean/index/file/');
				return;
			}
			
		}else{
			
			Mage::getSingleton('adminhtml/session')->addError('Nenhum arquivo foi enviado.');
			$this->_redirect('importean/index/file/');
			return;
		}
	}
}
