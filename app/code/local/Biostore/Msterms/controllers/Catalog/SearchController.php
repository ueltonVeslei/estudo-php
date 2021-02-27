<?php
include_once("Mage/Adminhtml/controllers/Catalog/SearchController.php");
class Biostore_Msterms_Catalog_SearchController extends Mage_Adminhtml_Catalog_SearchController
{

    /**
     * Save search query
     *
     */
    public function saveAction()
    {
        
        $hasError   = false;
        $data       = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost() && $data) {

            /**
             * Como vai funcionar? 
             * O script pega o ID da loja. Se o ID da loja for 0, entao o usuario selecionou a opcao 'Todas as lojas'.
             * Se ele selecionou essa opcao, a variavel $lojas tera os dados de todas as lojas. Caso contrario, a variavel
             * $lojas sera um array contendo apenas o ID da loja selecionada pelo cliente no indice 0. Assim, quando o foreach
             * for feito, o script verifica se a variavel $dados_loja eh um objeto ou um numero. Se for um objeto, eh porque o
             * usuario selecionou a opcao todas as lojas, portanto, deve ser chamado o metodo para obter o ID da loja atual,
             * caso contrario, o ID da loja eh o valor de $dados_loja.
             */

            // Get store id
            $store_id = $this->getRequest()->getPost('store_id', null);

            // Verifica se o usuario selecionou a opcao 'todas as lojas'
            if ($store_id == 0){
                $lojas = Mage::app()->getStores();
            } else {
                $lojas[0] = $store_id;
            }   

            // Obtem a URL de redirecionamento original
            $redirect_original = $data['redirect'];
            

            // Percorre todas as lojas
            foreach ($lojas as $id_loja => $dados_loja){

                // Verifica e define o store_id
                if (is_object($dados_loja)){
                    $storeId = Mage::app()->getStore($id_loja)->getId();
                    $storeUrl = Mage::app()->getStore($id_loja)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                    $data['redirect'] = str_replace('http://URL_DA_LOJA/', $storeUrl, $redirect_original);
                    $data['store_id'] = $storeId;
                    unset($data['query_id']);
                    $queryId = NULL;
                } else {
                    $storeId = $dados_loja;
                    $queryId    = $this->getRequest()->getPost('query_id', null);
                }

                $storeName = Mage::app()->getStore($storeId)->getName();
                
                /* @var $model Mage_CatalogSearch_Model_Query */
                $model = Mage::getModel('catalogsearch/query');

                // validate query
                $queryText  = $this->getRequest()->getPost('query_text', false);
                
                try {
                    if ($queryText) {
                        
                        $model->setStoreId($storeId);

                        $model->loadByQueryText($queryText);
                        if ($model->getId() && $model->getId() != $queryId) {
                        	$exception_message = 'O termo de busca já existe na loja ' . $storeName . '.';
                            Mage::throwException(
                                Mage::helper('catalog')->__($exception_message)
                            );
                        } else if (!$model->getId() && $queryId) {
                            $model->load($queryId);
                        }
                    } else if ($queryId) {
                        $model->load($queryId);
                    }
                    
                    $model->addData($data);
                    $model->setIsProcessed(0);
                    $model->save();

                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                    $hasError = true;
                } catch (Exception $e) {
                    $this->_getSession()->addException($e,
                        Mage::helper('catalog')->__('An error occurred while saving the search query.')
                    );
                    $hasError = true;
                }

            }

        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            $this->_redirect('*/*/edit', array('id' => $queryId));
        } else {
            $this->_redirect('*/*');
        }
    }

}
