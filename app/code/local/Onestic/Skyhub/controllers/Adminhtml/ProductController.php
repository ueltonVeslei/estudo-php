<?php
class Onestic_Skyhub_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function syncAction() {
	    $controlId = $this->getRequest()->getParam('id');
	    $product = Mage::getModel('onestic_skyhub/products')->load($controlId);
	    if ($product->getProductId()) {
	    	$result = Mage::getModel('onestic_skyhub/products_updater')->sync($product->getProductId());
			if ($result) {
    			Mage::getSingleton('adminhtml/session')->addSuccess("Produto " . $product->getSku() . " sincronizado com Skyhub!");
    		} else {
    			Mage::getSingleton('adminhtml/session')->addError("Produto " . $product->getSku() . " não sincronizado com Skyhub!");
    		}
	    } else {
	    	
	    }
	    $this->_redirect('*/*/');
	}
	
	public function exportprogressAction() {
		if(!Mage::getModel('core/session')->getImportPage()) {
			Mage::getModel('core/session')->setImportPage(1);
		}
		if(!Mage::getModel('core/session')->getImportTotal()) {
			Mage::getModel('core/session')->setImportTotal(0);
		}
		$total = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('visibility', array('in' => array(2,4)))
			->addAttributeToFilter('type_id',array('in' => array('configurable','simple')))->getSize();
		
		$page = Mage::getModel('core/session')->getImportPage();
		$syncTotal = Mage::getModel('core/session')->getImportTotal();
		$success = $errors = 0;
		if ($syncTotal < $total) {
			$result = Mage::getModel('onestic_skyhub/products_updater')->export($page);
			$syncTotal += $result['count'];
			$success = $result['success'];
			$errors = $result['errors'];
			Mage::getModel('core/session')->setImportTotal($syncTotal);
			Mage::getModel('core/session')->setImportPage($page+1);
		} else {
			$syncTotal = 0;
		}
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function exportAction() {
		Mage::getModel('core/session')->setImportPage(1);
		Mage::getModel('core/session')->setImportTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function populateAction() {
        $current_page = Mage::helper('onestic_skyhub')->getConfig('current_product_page');
        if(!Mage::getModel('core/session')->getImportPage()) {
			Mage::getModel('core/session')->setImportPage($current_page);
		}
		if(!Mage::getModel('core/session')->getImportTotal()) {
		    $importTotal = 0;
		    if ($current_page > 1) {
                $per_page = Mage::helper('onestic_skyhub')->getConfig('products_per_page');
                $importTotal = $per_page * $current_page;
            }
			Mage::getModel('core/session')->setImportTotal($importTotal);
		}
        $status = Mage::helper('onestic_skyhub')->getConfig('product_status_import');
        $total = Mage::getModel('onestic_skyhub/products')->getTotal(array('per_page'=>1, 'page'=>1, 'filters[status]' => $status));
		$page = Mage::getModel('core/session')->getImportPage();
		$syncTotal = Mage::getModel('core/session')->getImportTotal();
		$success = $errors = 0;
		if ($syncTotal < $total) {
			$result = Mage::getModel('onestic_skyhub/products')->populate($page);
			$syncTotal += $result['count'];
			$success = $result['success'];
			$errors = $result['errors'];
			Mage::getModel('core/session')->setImportTotal($syncTotal);
			Mage::getModel('core/session')->setImportPage($page+1);
		} else {
			$syncTotal = 0;
		}
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function progressAction() {
		Mage::getModel('core/session')->setImportPage(1);
		Mage::getModel('core/session')->setImportTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function resyncprogressAction() {
		Mage::getModel('core/session')->setSyncProductPage(1);
		Mage::getModel('core/session')->setSyncProductTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function resyncAction() {
		$model = 'onestic_skyhub/products';
		//$model = 'catalog/product';
		if(!Mage::getModel('core/session')->getSyncProductPage()) {
			Mage::getModel('core/session')->setSyncProductPage(1);
		}
		if(!Mage::getModel('core/session')->getSyncProductTotal()) {
			Mage::getModel('core/session')->setSyncProductTotal(0);
		}
		$page = Mage::getModel('core/session')->getSyncProductPage();
		$per_page = Mage::helper('onestic_skyhub')->getConfig('products_per_page');
		$syncTotal = Mage::getModel('core/session')->getSyncProductTotal();
		$total = Mage::getModel($model)->getCollection()->getSize();
		$success = $errors = 0;
		if ($syncTotal < $total) {
			$products = Mage::getModel($model)
							->getCollection()
							->setPageSize($per_page)
							->setCurPage($page);
			foreach($products as $product) {
				if ($product->getProductId()) {
					try {
						/* VERIFICAÇÃO DE PEDIDOS */
						$result = Mage::getModel('onestic_skyhub/products_updater')->sync($product->getProductId());
						if ($result) {
							$success++;
						} else {
							$errors++;
							Mage::log('PRD SYNC :: ERROR RESULT >> ' . $product->getSku(),null,'skyhub_sync_prd.log');
						}
					} catch(Exception $e) {
						$errors++;
						Mage::log('PRD SYNC :: EXCEPTION >> ' . $product->getSku() . ' >> ' . $e->getMessage(),null,'skyhub_sync_prd.log');
					}
				} else {
					Mage::getModel('onestic_skyhub/api_products')->remove($product->getSku());
					$localProduct = Mage::getModel('onestic_skyhub/products')->load($product->getSku(), 'sku');
					if ($localProduct->getId()) {
						$localProduct->delete();
					}
					$errors++;
					Mage::log('PRD SYNC :: REMOVE >> ' . $product->getSku(),null,'skyhub_sync_prd.log');
				}
				$syncTotal++;
			}
			Mage::getModel('core/session')->setSyncProductTotal($syncTotal);
			Mage::getModel('core/session')->setSyncProductPage($page+1);
		} else {
			$syncTotal = 0;
		}
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function deleteAction() {
		$controlId = $this->getRequest()->getParam('id');
		if ($controlId) {
			$product = Mage::getModel('onestic_skyhub/products')->load($controlId);
			if ($product->getId()) {
				$codeProduct = $product->getSku();
				Mage::getModel('onestic_skyhub/api_products')->remove($product->getSku());
				$product->setData('removed','SIM');
				$product->save();
				Mage::getSingleton('adminhtml/session')->addSuccess("Produto " . $codeProduct . " excluído com sucesso!");
			} else {
				Mage::getSingleton('adminhtml/session')->addSuccess("Produto não encontrado!");
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addSuccess("Código do produto não informado!");
		}
		$this->_redirect('*/*/');
	}
	
	public function cleanAction() {
		Mage::getResourceModel('onestic_skyhub/products')->cleanDatabase();
		Mage::getSingleton('adminhtml/session')->addSuccess("Tabela de produtos limpa com sucesso!");
		$this->_redirect('*/*/');
	}
	
	public function _isAllowed() {
	    return true;
	}
}