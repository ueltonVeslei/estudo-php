<?php

class Netreviews_Pla_Adminhtml_NetreviewsplaController extends Mage_Adminhtml_Controller_Action {

    public function exportProductAction() {
        $DATA = Mage::helper('avisverifies/Data');
        $export = Mage::helper('netreviews_pla/Export');
        $plaAll = $export->getAllPlaConfiguration();
        $allShops = $export->getAllShopName();
        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog/product";

        $allFiles = array();
        foreach ($allShops as $storeId => $storeName) {
            $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
            $data = array();
            $collections = Mage::getModel('catalog/product')->getCollection();
            $collections->addStoreFilter($storeId);
            $collections->addAttributeToSelect('entity_id');
            $collections->addAttributeToSelect('sku');
            $collections->addAttributeToSelect('small_image');
            $collections->addAttributeToSelect('url_path');
            $collections->addAttributeToSelect('name');
            // get product $data according to format pla
            $tmp = array();
            if (isset($plaAll[$storeId])) {
                foreach ($plaAll[$storeId] as $fields) {
                    // first check if its the product id
                    $collections->addAttributeToSelect($fields['static_value']);
                    $tmp[] = $fields['static_value'];
                }
            }
            $data[] = array_merge(array('storeName', 'storeId', 'productId', 'sku', 'productName', 'url', 'image', '-----Pla Info-----'), $tmp);
            foreach ($collections as $collection) {
                $array = $collection->getData();
                $tmp = array();
                $tmp[] = $storeName;
                $tmp[] = $storeId;
                $tmp[] = (isset($array['entity_id'])) ? $array['entity_id'] : "NULL";
                $tmp[] = (isset($array['sku'])) ? $array['sku'] : "NULL";
                $tmp[] = (isset($array['name'])) ? $array['name'] : "NULL";
                $tmp[] = (isset($array['url_path'])) ? $baseUrl . $array['url_path'] : "NULL";
                $tmp[] = (isset($array['small_image']) && $array['small_image'] != 'no_selection') ? $mediaUrl . $array['small_image'] : "NULL";
                $tmp[] = '-----';
                // get product $data according to format pla
                if (isset($plaAll[$storeId])) {
                    foreach ($plaAll[$storeId] as $fields) {
                        // first check if its the product id
                        $tmp[] = (isset($array[$fields['static_value']])) ? $array[$fields['static_value']] : "NULL";
                    }
                }
                $data[] = $tmp;
            }

            $fileName = $allFiles[] = $path . 'exportProduct_' . $storeId . '.csv';
            $fp = fopen($fileName, 'w');
            foreach ($data as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
        }


        $tmpName = 'exportProduct' . date("ymdhis") . '.zip';
        $zipname = $path . $tmpName;
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($allFiles as $file) {
            $zip->addFile($file);
        }
        $zip->close();

        ///Then download the zipped file.
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $tmpName);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        $DATA->exitnow();
    }

    public function plaAction() {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('netreviews_pla/adminhtml_edit')) // save button
                ->_addLeft($this->getLayout()->createBlock('netreviews_pla/adminhtml_tabs')); // tabs + content
        $this->renderLayout();
    }

    public function saveAction() {
        // get current store id 
        $storeCode = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');

        if (empty($storeCode)) {
            $message = $this->__('Please select a store view');
            Mage::getSingleton('core/session')->addError($message);
            $this->_redirect('*/*/pla');
            return;
        } else {
            $store = Mage::getModel('core/store')->load($storeCode);
            $storeId = $store->getId();
        }

        $post = $this->getRequest()->getPost();
        // first of all save the sku / id configuration
        $test = $this->getDataField($post, 'id');
        // id is set to sku , then update the store config sku
        if (empty($test['static_value'])) {
            $message = $this->__('Please select the value for ID field');
            Mage::getSingleton('core/session')->addError($message);
            $this->_redirect('*/*/pla/website/' . $website . '/store/' . $storeCode);
            return;
        }

        $mageselc = new Mage_Core_Model_Config();
        /* Code Change 
          if ($test['static_value'] == 'sku') {
          $mageselc->saveConfig('avisverifies/extra/use_product_sku',1,'stores',$storeId);
          }
          else {
          $mageselc->saveConfig('avisverifies/extra/use_product_sku',NULL,'stores',$storeId);
          }
         */
        // now the data filtered from empty values 
        $filtered = $this->getDataFiltered($post);
        // now we loop over the eav to get the id 
        foreach ($filtered as $index => $data) {
            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $data['static_value']);
            $filtered[$index]['attribute_id'] = $attributeModel->getData('attribute_id');
        }
        $mageselc->saveConfig('avisverifies/extra/pla_configuration', json_encode($filtered), 'stores', $storeId);
        $message = $this->__('Your settings have been submitted successfully.');
        Mage::getSingleton('core/session')->addSuccess($message);
        $this->_redirect('*/*/pla/website/' . $website . '/store/' . $storeCode);
        return;
    }

    /**
     * Return if the admin allowed to access this controller.
     * @return bool
     */
    protected function _isAllowed() {
        // Switch on action name
        switch (strtolower($this->getRequest()->getActionName())) {
            case 'save':
            case 'exportproduct':
            case 'pla':
                $acl = 'admin/catalog/avisverifies/pla';
                break;
            default:
                return false;
        }

        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

    protected function getDataField($array, $name) {
        if (empty($array['field'])) {
            return array();
        }
        foreach ($array['field'] as $data) {
            if ($data['name'] == $name) {
                return $data;
            }
        }
        return array();
    }

    protected function getDataFiltered($array) {
        if (empty($array['field'])) {
            return array();
        }
        $tmp = array();
        foreach ($array['field'] as $data) {
            if (!empty($data['static_value'])) {
                $tmp[] = $data;
            }
        }
        return $tmp;
    }

}
