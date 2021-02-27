<?php

define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/app/Mage.php';


class Flowecommerce_Stockupdater_IndexController extends Mage_Core_Controller_Front_Action
{
	# 12/07/2012
	# O id das entidades foi alterado para compatibilidade com a versão 1.7.

	# Este é o caminho para o diretorio que contem os arquivos
	# Caso haja mais de um arquivo no diretorio quando a url for chamada,
	# o script não será executado.
	private $strStockPath = "/var/www/producao/tmp/stock/";
	//private $strStockPath = "/var/www/vhosts/mage17/farma.aws/farma/tmp/stock/";

	public function getDate()
	{
		return date("Y-m-d H:m:s", Mage::getModel("core/date")->timestamp(time()));
	}

	public function indexAction()
	{
		
		ob_start();
		ob_end_clean();
		ob_start();
		
		echo 'inciando';
		
		$xstrStockPath = Mage::getBaseDir().'/tmp/logstockupdate/';
		if(!is_dir($xstrStockPath)){
			mkdir($xstrStockPath, 0777);
		}
		
		$data = date("d/m/Y H:i:s");
		$arquivo = "log_stockupdate.txt";
		$msg = 'Iniciando o stockupdate.';
		$texto = "[$data] $msg \n";
		$manipular = fopen($xstrStockPath.$arquivo, "a+");
		fwrite($manipular, $texto);
		fclose($manipular);
		
		
		ini_set("memory_limit", "500M");
		set_time_limit(0);
		
		# Read stock files folder
		if(is_dir($this->strStockPath)) {
			$objStockDir = dir($this->strStockPath);
			$arrStockFiles = array();
			
			while (FALSE !== ($strEntry = $objStockDir->read())) {
				if ($strEntry != "." && $strEntry != "..") {
					$arrStockFiles[] = $this->strStockPath . $strEntry;
				}
			}
			
			$objStockDir->close();
			
			# Check if has more than one file on folder
			
			if (count($arrStockFiles) != 1) {
				throw new Exception( "This directory: '" . $this->strStockPath . "' must have exactly one file." );
			} else {
				$objFile = fopen($arrStockFiles[ 0 ], "r");
				$i = 0;
				
				$config = Mage::getConfig()->getResourceConnectionConfig('core_write');		  
				$conn = mysql_connect($config->host,$config->username,$config->password) or die(mysql_error());
				
				if ($conn) {
					mysql_select_db($config->dbname,$conn);
				}
				
				$strQuery = "SELECT attribute_id FROM eav_attribute WHERE entity_type_id=10 AND attribute_code='price'";
				$result = mysql_query($strQuery);
				
				while ($rowPriceId = mysql_fetch_assoc($result)) {
					$priceId = $rowPriceId["attribute_id"];
				}

				$strQuery = "SELECT attribute_id FROM eav_attribute WHERE entity_type_id=10 AND attribute_code='special_price'";
				$result = mysql_query($strQuery);
				
				while ($rowSpecialPriceId = mysql_fetch_assoc($result)) {
					$specialPriceId = $rowSpecialPriceId["attribute_id"];
				}

				//echo 'Horario do inicio da atualizacao de estoque: ' . $this->getDate() . '<br /><br />';

				$intTotalItems = 0;
				$intUpdatedItems = 0;
				$intNotUpdatedItems = 0;
				$invalidLines = 0;
				
				while ( ($arrPieces = fgetcsv($objFile, 1000, ",")) !== FALSE) {
					$intTotalItems++;

					if ( count($arrPieces) != 7) {
						$invalidLines++;
						continue;
					}

					// Desconsiderando os três primeiros itens do array que são: store, websites e attribute_set
					$mixProductSku = $arrPieces[3];
					$objProductModel = Mage::getModel( "catalog/product" );
					$intProductId = $objProductModel->getIdBySku( $mixProductSku );
					
					if ($intProductId) {
						Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
						
						$strQuery = "SELECT entity_id FROM catalog_product_entity WHERE sku='" . $mixProductSku . "'";
						$result = mysql_query($strQuery);
						$productId = null;
						
						while ($rowEntityId = mysql_fetch_assoc($result)) {
							$productId = $rowEntityId["entity_id"];
						}

						if (!$productId) {
							continue;
						}

						$intStock = (int) $arrPieces[ 6 ] <= 0 ? "0" : (int) $arrPieces[ 6 ];
						$strInStock = ( $intStock > 0 ) ? "1" : "0";

						$strQuery = "UPDATE cataloginventory_stock_item SET qty = " . $intStock . ", is_in_stock = " . $strInStock ." WHERE product_id = " . $productId;
						$boolStatus = mysql_query( $strQuery, $conn );

						# store_id <> 6 => não atualiza os preços da loja HEEL
						$strQuery = "UPDATE catalog_product_entity_decimal SET value=" . $arrPieces[4] . " WHERE store_id <> 6 and entity_id=" . $productId . " AND attribute_id=" . $priceId;
						$boolStatus = mysql_query( $strQuery, $conn );

						# store_id <> 6 => não atualiza os preços da loja HEEL
						$strQuery = "UPDATE catalog_product_entity_decimal SET value=" . $arrPieces[5] . " WHERE store_id <> 4 and entity_id=" . $productId . " AND attribute_id=" . $specialPriceId;
						$boolStatus = mysql_query( $strQuery, $conn );

						$intUpdatedItems++;
					} else {
						$intNotUpdatedItems++;
					}
					$i++;
				}

				# Reporting...
				//echo "Total de itens no arquivo CSV: " . $intTotalItems . "<br />";
				//echo "Total de itens atualizados: " . $intUpdatedItems . "<br />";
				//echo "Total de itens que nao foram encontrados no estoque: " . $intNotUpdatedItems . "<br />";
				//echo "Total de linhas invalidas: " . $invalidLines . "<br /><br />";

				$version = substr(Mage::getVersion(), 0, 3);
				if ($version == '1.4') {
					$process_collection = Mage::getModel('index/process')->getCollection()->getData();
					$indexer = Mage::getSingleton('index/indexer');
					foreach ($process_collection as $process) {
						$process = $indexer->getProcessById($process['process_id']);
						
						if ($process) {
							$process->reindexEverything();
						}
					}
				} else {
					/*
					# Stock
					Mage::getSingleton( "cataloginventory/stock_status" )->rebuild();

					# Flat Product Table
					Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();

					# Flat Category Table
					Mage::getResourceModel('catalog/category_flat')->rebuild();

					# Layered Navigation
					$flag = Mage::getModel('catalogindex/catalog_index_flag')->loadSelf();
					$flag->setState(Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_QUEUED)->save();

					# Search Index
					Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex();

					# Catalog Index
					Mage::getSingleton('catalog/index')->rebuild();
					*/
					
					// Guilherme
					// Magento 1.7
					
					//$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
					//foreach ($indexingProcesses as $process) {
					      //$process->reindexEverything();
					//}
					
					// $i = 0;
// 		
					// for ($i = 0; $i < 10; $i++) {
// 						
						// if($i == 1 || $i == 2 || $i == 4 || $i == 8 || $i == 9){	 
							// $process = Mage::getModel('index/process')->load($i);
							// $process->reindexAll();
						// }
					// }
					
					shell_exec('echo parte dos reindex');	
					//shell_exec('rm -rf /var/www/producao/var/locks/*');
					
					//shell_exec('rm -rf /var/www/producao/var/stock/*');
					
					//shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_price > /var/www/producao/var/stock/infoReindexPrice.txt &');
						
					//shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex cataloginventory_stock > /var/www/producao/var/stock/infoReindexStock.txt &');
					
					//shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_attribute > /var/www/producao/var/stock/infoReindexProductAttribute.txt &');
					
					//shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_flat > /var/www/producao/var/stock/infoReindexProductFlat.txt &');
					
					//shell_exec('rm -rf /var/www/producao/var/locks/*');
				}
				
				//echo "Inventory Stock Status ok...<br />";

				# Flat Product Table
				//echo "Flat Product Table ok...<br />";

				# Flat Category Table
				//echo "Flat Category Table ok...<br />";

				# Layered Navigation
				//echo "Layered Navigation ok...<br />";

				# Search Index
				//echo "Search Index ok...<br />";

				# Catalog Index
				//echo "Catalog Index ok...<br /><br />";

				//echo 'Horario de termino: ' . $this->getDate();
			}
			
			$xstrStockPath = Mage::getBaseDir().'/tmp/logstockupdate/';
			if(!is_dir($xstrStockPath)){
				mkdir($xstrStockPath, 0777);
			}
			
			$data = date("d/m/Y H:i:s");
			$arquivo = "log_stockupdate.txt";
			$msg = 'Fechando o stockupdate.';
			$texto = "[$data] $msg \n";
			$manipular = fopen($xstrStockPath.$arquivo, "a+");
			fwrite($manipular, $texto);
			fclose($manipular);
			
		} else {
			throw new Exception( "Directory '" . $this->strStockPath . "' doesn't exist" );
		}
	}
}


$shell = new Flowecommerce_Stockupdater_IndexController();
$shell->getdate();
$shell->indexAction();

?>
