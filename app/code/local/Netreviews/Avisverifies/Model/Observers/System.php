<?php

class Netreviews_Avisverifies_Model_Observers_System {

    public function save(Varien_Event_Observer $observer) {

        $curWebsite = Mage::app()->getRequest()->getParam('website');
        $curStore = Mage::app()->getRequest()->getParam('store');

        $forceAll = $websiteId = false;
        if (!is_null($curWebsite) && is_null($curStore)) {
            $websiteObj = Mage::getModel('core/website')->load($curWebsite, 'code');
            $allStores = $websiteObj->getStoreIds();
            $websiteId = $websiteObj->getData('website_id');
        } elseif (!is_null($curStore)) {
            $storeId = $curStore;
        } else {
            $forceAll = true;
        }

        $storeModel = Mage::getSingleton('adminhtml/system_store');
        $mageselc = new Mage_Core_Model_Config();
        $POST = Mage::app()->getRequest()->getPost();

        $secretkey = isset($POST['groups']['system']['fields']['secretkey']['value']) ? $POST['groups']['system']['fields']['secretkey']['value'] : NUll;
        $idwebsite = (isset($POST['groups']['system']['fields']['idwebsite']['value'])) ? $POST['groups']['system']['fields']['idwebsite']['value'] : NULL;
        $enabledwebsite = (isset($POST['groups']['system']['fields']['enabledwebsite']['value'])) ? $POST['groups']['system']['fields']['enabledwebsite']['value'] : NULL;
        $forceParentIds = (isset($POST['groups']['extra']['fields']['force_product_parent_id']['value'])) ? $POST['groups']['extra']['fields']['force_product_parent_id']['value'] : NULL;
        $addReviewToProductPage = (isset($POST['groups']['extra']['fields']['add_review_to_product_page']['value'])) ? $POST['groups']['extra']['fields']['add_review_to_product_page']['value'] : NULL;
        //$useProductSKU = (isset($POST['groups']['extra']['fields']['use_product_sku']['value']))? $POST['groups']['extra']['fields']['use_product_sku']['value'] : NULL ;
        $productLightWidget = (isset($POST['groups']['extra']['fields']['product_light_widget']['value'])) ? $POST['groups']['extra']['fields']['product_light_widget']['value'] : NULL;
        $hasjQuery = (isset($POST['groups']['extra']['fields']['has_jquery']['value'])) ? $POST['groups']['extra']['fields']['has_jquery']['value'] : NULL;
        $useProductUrl = (isset($POST['groups']['extra']['fields']['use_product_url']['value'])) ? $POST['groups']['extra']['fields']['use_product_url']['value'] : NULL;
        $showEmptyProductMessage = (isset($POST['groups']['extra']['fields']['show_empty_product_message']['value'])) ? $POST['groups']['extra']['fields']['show_empty_product_message']['value'] : NULL;
        $activateRichSnippets = (isset($POST['groups']['extra']['fields']['activate_rich_snippets']['value'])) ? $POST['groups']['extra']['fields']['activate_rich_snippets']['value'] : NULL;

        // forceAll , that mean we are using the default config, save config on all website + store
        if ($forceAll) {
            foreach ($storeModel->getWebsiteCollection() as $website) {
                $mageselc->saveConfig('avisverifies/system/secretkey', $secretkey, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/system/idwebsite', $idwebsite, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/system/enabledwebsite', $enabledwebsite, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/force_product_parent_id', $forceParentIds, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/add_review_to_product_page', $addReviewToProductPage, 'websites', $website->getId());
                //$mageselc->saveConfig('avisverifies/extra/use_product_sku',$useProductSKU,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/product_light_widget', $productLightWidget, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/has_jquery', $hasjQuery, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/use_product_url', $useProductUrl, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/show_empty_product_message', $showEmptyProductMessage, 'websites', $website->getId());
                $mageselc->saveConfig('avisverifies/extra/activate_rich_snippets', $activateRichSnippets, 'websites', $website->getId());
            }
            foreach ($storeModel->getStoreCollection() as $store) {
                $mageselc->saveConfig('avisverifies/system/secretkey', $secretkey, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/system/idwebsite', $idwebsite, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/system/enabledwebsite', $enabledwebsite, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/force_product_parent_id', $forceParentIds, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/add_review_to_product_page', $addReviewToProductPage, 'stores', $store->getId());
                //$mageselc->saveConfig('avisverifies/extra/use_product_sku',$useProductSKU,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/product_light_widget', $productLightWidget, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/has_jquery', $hasjQuery, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/use_product_url', $useProductUrl, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/show_empty_product_message', $showEmptyProductMessage, 'stores', $store->getId());
                $mageselc->saveConfig('avisverifies/extra/activate_rich_snippets', $activateRichSnippets, 'stores', $store->getId());
            }
        } elseif ($websiteId) {
            // website id 
            $mageselc->saveConfig('avisverifies/system/secretkey', $secretkey, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/system/idwebsite', $idwebsite, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/system/enabledwebsite', $enabledwebsite, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/force_product_parent_id', $forceParentIds, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/add_review_to_product_page', $addReviewToProductPage, 'websites', $websiteId);
            //$mageselc->saveConfig('avisverifies/extra/use_product_sku',$useProductSKU,'websites',$websiteId);
            $mageselc->saveConfig('avisverifies/extra/product_light_widget', $productLightWidget, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/has_jquery', $hasjQuery, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/use_product_url', $useProductUrl, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/show_empty_product_message', $showEmptyProductMessage, 'websites', $websiteId);
            $mageselc->saveConfig('avisverifies/extra/activate_rich_snippets', $activateRichSnippets, 'websites', $websiteId);

            foreach ($storeModel->getStoreCollection() as $store) {
                $storeId = $store->getId();
                if (in_array($storeId, $allStores)) {
                    $mageselc->saveConfig('avisverifies/system/secretkey', $secretkey, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/system/idwebsite', $idwebsite, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/system/enabledwebsite', $enabledwebsite, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/force_product_parent_id', $forceParentIds, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/add_review_to_product_page', $addReviewToProductPage, 'stores', $storeId);
                    //$mageselc->saveConfig('avisverifies/extra/use_product_sku',$useProductSKU,'stores',$storeId);
                    $mageselc->saveConfig('avisverifies/extra/product_light_widget', $productLightWidget, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/has_jquery', $hasjQuery, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/use_product_url', $useProductUrl, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/show_empty_product_message', $showEmptyProductMessage, 'stores', $storeId);
                    $mageselc->saveConfig('avisverifies/extra/activate_rich_snippets', $activateRichSnippets, 'stores', $storeId);
                }
            }
        } elseif ($storeId) {
            $mageselc->saveConfig('avisverifies/system/secretkey', $secretkey, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/system/idwebsite', $idwebsite, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/system/enabledwebsite', $enabledwebsite, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/force_product_parent_id', $forceParentIds, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/add_review_to_product_page', $addReviewToProductPage, 'stores', $storeId);
            //$mageselc->saveConfig('avisverifies/extra/use_product_sku',$useProductSKU,'stores',$storeId);
            $mageselc->saveConfig('avisverifies/extra/product_light_widget', $productLightWidget, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/has_jquery', $hasjQuery, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/use_product_url', $useProductUrl, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/show_empty_product_message', $showEmptyProductMessage, 'stores', $storeId);
            $mageselc->saveConfig('avisverifies/extra/activate_rich_snippets', $activateRichSnippets, 'stores', $storeId);
        }



        // first loop on the disactive stores, an disactive store can not be related to other stores.
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope', 'stores')
                ->addFieldToFilter('path', 'avisverifies/system/enabledwebsite')
                ->addFieldToFilter('value', '0');
        foreach ($resource as $store) {
            $mageselc->saveConfig('avisverifies/extra/relatedstoreslist', '', 'stores', $store->getData('scope_id'));
        }
        // i need to get all the idWebsite from database and then update accordingly
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('path', 'avisverifies/system/idwebsite');
        $allIdwebsite = array();
        foreach ($resource as $value) {
            $allIdwebsite[$value->getData('value')] = 1;
        }
        $allIdwebsite = array_keys($allIdwebsite);
        foreach ($allIdwebsite as $idwebsite) {
            // now we get the related stores list
            $relatedstoreslist = Mage::helper('avisverifies/Data')->getModuleActiveStoresIds($idwebsite);
            foreach ($relatedstoreslist as $storeId) {
                $mageselc->saveConfig('avisverifies/extra/relatedstoreslist', implode(';', $relatedstoreslist), 'stores', $storeId);
            }
        }
    }

}
