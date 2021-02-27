<?php
require_once 'abstract.php';

class Mage_Shell_Compiler extends Mage_Shell_Abstract
{


	public function run()
	{

		ini_set("memory_limit", "500M");
		set_time_limit(0);

		$strStockPath = "/var/www/clients/client0/web1/private/stock/";

		# Read stock files folder
		if(is_dir($strStockPath)) {
			$objStockDir = dir($strStockPath);
			$arrStockFiles = array();

			while (FALSE !== ($strEntry = $objStockDir->read())) {
				if ($strEntry != "." && $strEntry != "..") {
					$arrStockFiles[] = $strStockPath . $strEntry;
				}
			}

			$objStockDir->close();

			# Check if has more than one file on folder

			if (count($arrStockFiles) != 1) {
				throw new Exception( "This directory: '" . $strStockPath . "' must have exactly one file." );
			} else {

				$objFile = fopen($arrStockFiles[ 0 ], "r");
				$i = 0;

				$config = Mage::getConfig()->getResourceConnectionConfig('core_write');
				$conn = mysqli_connect($config->host,$config->username,$config->password) or die(mysqli_error());

				if ($conn) {
					mysqli_select_db($conn, $config->dbname);
				}

				$strQuery = "SELECT attribute_id FROM eav_attribute WHERE entity_type_id=10 AND attribute_code='price'";
				$result = mysqli_query($conn, $strQuery);

				while ($rowPriceId = mysqli_fetch_assoc($result)) {
					$priceId = $rowPriceId["attribute_id"];
				}

				$strQuery = "SELECT attribute_id FROM eav_attribute WHERE entity_type_id=10 AND attribute_code='special_price'";
				$result = mysqli_query($conn, $strQuery);

				while ($rowSpecialPriceId = mysqli_fetch_assoc($result)) {
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
					$intProductId = $objProductModel->getIdBySku($mixProductSku);

					if ($intProductId) {
						Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

						$intStock = (int) $arrPieces[ 6 ] <= 0 ? "0" : (int) $arrPieces[ 6 ];
						$strInStock = ( $intStock > 0 ) ? "1" : "0";

						$strQuery = "UPDATE cataloginventory_stock_item SET qty = " . $intStock . ", is_in_stock = " . $strInStock ." WHERE product_id = " . $intProductId;
						$boolStatus = mysqli_query($conn, $strQuery);

						# store_id <> 6 => não atualiza os preços da loja HEEL
						$strQuery = "UPDATE catalog_product_entity_decimal SET value=" . $arrPieces[4] . " WHERE store_id <> 6 and entity_id=" . $intProductId . " AND attribute_id=" . $priceId;
						$boolStatus = mysqli_query($conn, $strQuery);

						# store_id <> 6 => não atualiza os preços da loja HEEL
						$strQuery = "UPDATE catalog_product_entity_decimal SET value=" . $arrPieces[5] . " WHERE store_id <> 4 and entity_id=" . $intProductId . " AND attribute_id=" . $specialPriceId;
						$boolStatus = mysqli_query($conn, $strQuery);
						
						# Update product update_at date time
						$strQuery = "UPDATE catalog_product_entity SET updated_at = now() WHERE entity_id = " . $intProductId;
						$boolStatus = mysqli_query($conn,  $strQuery);

						# Update product skyhub_products
						$strQuery = "UPDATE skyhub_products SET updated_at = NULL WHERE product_id = " . $intProductId;
						$boolStatus = mysqli_query($conn, $strQuery);						
						
						# Put product in Skyhub's updation query
						$strQuery = "UPDATE skyhub_products SET updated_at = now(), status_sync = 'N�O' WHERE product_id = " . $intProductId;
						$boolStatus = mysqli_query($conn, $strQuery);

						$intUpdatedItems++;
					} else {
						$intNotUpdatedItems++;
					}
					$i++;
				}

				# Reporting...
				
				$headers = "From: sac@farmadelivery.com";
				$dataehora = date('d-m-Y H:i:s');
				$msg = "Data: $dataehora, Total de itens no arquivo CSV: $intTotalItems, Total de itens atualizados: $intUpdatedItems, Total de itens que nao foram encontrados no estoque: $intNotUpdatedItems, Total de linhas invalidas: $invalidLines";

				mail('camila@farmadelivery.com', 'Stockupdate', $msg, $headers);
				
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
						/*
						 shell_exec('rm -rf /var/www/producao/var/locks/*');
							
						shell_exec('rm -rf /var/www/producao/var/stock/*');
							
						shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_price > /var/www/producao/var/stock/infoReindexPrice.txt &');

						shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex cataloginventory_stock > /var/www/producao/var/stock/infoReindexStock.txt &');
							
						shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_attribute > /var/www/producao/var/stock/infoReindexProductAttribute.txt &');
							
						shell_exec('php -f /var/www/producao/shell/indexer.php -- -reindex catalog_product_flat > /var/www/producao/var/stock/infoReindexProductFlat.txt &');
						*/
							
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
		} else {
			throw new Exception( "Directory '" . $strStockPath . "' doesn't exist" );
		}
	}


	/**
	 * Retrieve Usage Help Message
	 *
	 */
	public function usageHelp()
	{
		return <<<USAGE
Usage:  php -f updatestock.php -- [options]

  state         Show Compilation State
  compile       Run Compilation Process
  clear         Disable Compiler include path and Remove compiled files
  enable        Enable Compiler include path
  disable       Disable Compiler include path
  help          This help

USAGE;
	}
}

$shell = new Mage_Shell_Compiler();
$shell->run();


