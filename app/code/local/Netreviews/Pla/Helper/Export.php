<?php

// Our EXPORT CLASS. 

class Netreviews_Pla_Helper_Export extends Netreviews_Avisverifies_Helper_Export {

    protected $collection;
    protected $plaProducts = array();
    protected $defaultProducts = array();
    protected $newCsvHeader = array();

    public function __construct() {
        $this->collection = Mage::getModel("sales/order")->getCollection();
        $this->resource = Mage::getSingleton('core/resource');
        $this->dataExport = array();
        $this->newCsvHeader[] = array(
            'id_order',
            'email',
            'lastname',
            'firstname',
            'date_order',
            'delay',
            'id_shop',
            'amount_order',
            'id_order_state',
            'id_product',
            'sku',
            'product_name',
            'product_url',
            'url_image',
            'brand',
            'category',
            'gtin_ean',
            'gtin_upc',
            'gtin_jan',
            'gtin_isbn',
            'mpn',
            'info1', 'info2', 'info3',
            'info4', 'info5', 'info6',
            'info7', 'info8', 'info9', 'info10'
        );
        // check for sale order module version
        $version = Mage::getConfig()->getModuleConfig("Mage_Sales")->version;
        $version = $this->convertVersion($version);
        // stable version (known): 1.4.0.15, but going with 1.4.0.0
        $stableVersion = $this->convertVersion('1.4.0.0');
        $this->isVersion13 = ($version < $stableVersion) ? true : false;
    }

    public function getDataExport() {
        // some memory optimization
        $dataExport = ($this->dataExport) ? $this->dataExport : array();
        $this->dataExport = array();
        $this->collection = Null;
        return $dataExport;
    }

    public function createExportCSV($from, $to) {
        $this->newQuery();

        $results = Mage::getSingleton('core/resource')->getConnection('core_read')->query("select $from as date_time")->fetch();
        $this->collection->addAttributeToFilter('created_at', array('gteq' => $results['date_time']));
        $this->dataExport = array_merge($this->newCsvHeader, $this->reformatDataForCSV($this->collectionPagination()));
    }

    public function createExportAPI(array $config) {
        $this->newQuery();
        // filter by date
        if (isset($config['from']) && isset($config['to'])) {
            $this->collection->addAttributeToFilter('created_at', array(
                'from' => $config['from'],
                'to'   => $config['to'],
                'date' => true, // specifies conversion of comparison values
            ));
        }
        // filter by av_flag
        if (isset($config['flag'])) {
            $this->collection->addFieldToFilter('av_flag', 0);
        }
        // filter by status
        if (isset($config['status'])) {
            $this->collection->addAttributeToFilter('status', array("in" => $config['status']));
        }

        $this->dataExport = $this->collectionPagination();
    }

    protected function newQuery() {
        $this->collection
                ->addFieldToFilter('store_id', $this->arrStoresIds)
                ->addAttributeToSort('entity_id', 'DESC')
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('grand_total')
                ->addAttributeToSelect('av_flag')
                ->addAttributeToSelect('av_horodate_get')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('customer_email')
                ->addAttributeToSelect('customer_firstname')
                ->addAttributeToSelect('customer_lastname')
                ->addAttributeToSelect('store_id')
                ->setPageSize(50);
        $this->collection->getSelect()->columns('DATE_FORMAT(created_at,"%d/%m/%Y %H:%i") as createdAtt')
                ->columns('UNIX_TIMESTAMP(created_at) as timestamp');
    }

    protected function collectionPagination() {
        $page = 1; // 0 & 1 are the same pages.
        $pages = $this->collection->getLastPageNumber();
        $orders = array();
        $pla = $this->getAllPlaConfiguration();
        $allShops = $this->getAllShopName();
        try {
            do {
                $this->collection->setCurPage($page)->load();
                foreach ($this->collection as $order) {
                    $order_data = array();
                    $products_data = array();
                    $storeId = $order->getStoreId();
                    $order_data['entity_id'] = $order->getEntityId();
                    $order_data['order_id'] = $order->getIncrementId();
                    $order_data['date'] = $order->getData('createdAtt');
                    $order_data['timestamp'] = $order->getTimestamp();
                    $order_data['amount_order'] = $order->getGrandTotal();
                    $order_data['date_av_getted_order'] = $order->getAvHorodateGet();
                    $order_data['is_flag'] = $order->getAvFlag();
                    $order_data['status_order'] = $order->getstatus();
                    $order_data['prenom'] = utf8_decode($order->getCustomerFirstname());
                    $order_data['nom'] = utf8_decode($order->getCustomerLastname());
                    $order_data['email'] = $order->getCustomerEmail();
                    $order_data['id_shop'] = $allShops[$storeId];
                    // check if option get product active
                    if ($this->isProductExport && empty($pla[$storeId])) { // default Product array compatible with our API return 
                        $products_data = $this->defaultProductsData($order);
                    } elseif ($this->isProductExport) { // PLA Product array compatible with our API return 
                        $products_data = $this->plaProductsData($order, $pla[$storeId]);
                    } else { // default EMPTY array compatible with our API return 
                        $products_data[] = array('product_id' => '', 'product_name' => '', 'url' => '', 'url_image' => '');
                    }
                    // create multiple line per product
                    foreach ($products_data as $_tmp) {
                        $order_data = array_merge($order_data, $_tmp);
                        $orders[] = $order_data;
                    }
                    $orders[] = $order_data;
                }
                $page++;
                $this->collection->clear();
            } while ($page <= $pages);
        } catch (Exception $e) {
            var_dump($e);
        }
        // some memory optimisation
        $this->plaProducts = array();
        $this->defaultProducts = array();
        return $orders;
    }

    protected function plaProductsData($order, $pla) {
//		var_dump($pla);die;
        $products = $order->getAllVisibleItems(); //filter out simple products
        $products_arr = array();
        foreach ($products as $product) {
            $productId = $product->getProductId();
            if (!empty($this->plaProducts[$productId])) {
                $products_arr[] = $this->plaProducts[$productId];
            } else {
                $full_product = Mage::getModel('catalog/product')->load($productId);
                $collection = Mage::getModel('catalog/product')->getCollection();
                $collection->addAttributeToFilter('entity_id', $productId);
                $collection->addAttributeToSelect('entity_id');
                // get product $data according to format pla
                foreach ($pla as $fields) {
                    // first check if its the product id
                    $collection->addAttributeToSelect($fields['static_value']);
                }
                $full_product = $collection->getFirstItem();
                $tmp_product = Mage::getModel('catalog/product')->load($productId);
                $_data = $full_product->getData();
                $product_data = array();
                foreach ($pla as $attribute) {
                    // product_id
                    if ($attribute['name'] == 'id') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['product_id'] = ($_val) ? $_val : '';
                    }
                    // product_description
                    if ($attribute['name'] == 'product_name') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['product_name'] = ($_val) ? $_val : '';
                    }
                    // product_link
                    if ($attribute['name'] == 'link') {
                        // get parent URL or NOT
                        $product_data['url'] = $this->getProductUrlOrParentUrl($productId, $full_product->getData($attribute['static_value']), $order->getStoreId(), true);
                    }
                    // product_image_link
                    if ($attribute['name'] == 'image_link') {
                        $product_data['url_image'] = $this->getProductImageOrParentImage($productId, $full_product->getData($attribute['static_value']), true);
                    }
                    // product_sku
                    if ($attribute['name'] == 'sku') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['sku'] = ($_val) ? $_val : '';
                    }
                    // product_brand
                    if ($attribute['name'] == 'brand') {
                        $_val = $full_product->getData($attribute['static_value']);

                        // If selectbox $_val is number so rather get Text.
                        if (is_numeric($_val)) {
                            $_val = $full_product->getAttributeText($attribute['static_value']);
                        }
                        $product_data['brand'] = ( $_val ) ? $_val : '';
                    }
                    // product_category
                    if ($attribute['name'] == 'category') {
                        $_val = $full_product->getData($attribute['static_value']);

                        // If selectbox $_val is number so rather get Text.
                        if (is_numeric($_val)) {
                            $_val = $full_product->getAttributeText($attribute['static_value']);
                        }
                        $product_data['category'] = ( $_val ) ? $_val : '';
                    }
                    // product_gtin_ean
                    if ($attribute['name'] == 'gtin_ean') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['gtin_ean'] = ($_val) ? $_val : '';
                    }
                    // product_gtin_upc
                    if ($attribute['name'] == 'gtin_upc') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['gtin_upc'] = ($_val) ? $_val : '';
                    }
                    // product_gtin_jan
                    if ($attribute['name'] == 'gtin_jan') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['gtin_jan'] = ($_val) ? $_val : '';
                    }
                    // product_gtin_isbn
                    if ($attribute['name'] == 'gtin_isbn') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['gtin_isbn'] = ($_val) ? $_val : '';
                    }
                    // product_mpn
                    if ($attribute['name'] == 'mpn') {
                        $_val = $full_product->getData($attribute['static_value']);
                        $product_data['mpn'] = ($_val) ? $_val : '';
                    }
                    // extra info 1 to 10
                    for ($i = 1; $i < 11; $i++) {
                        $name = 'Extra Info' . $i;
                        // product_mpn
                        if ($attribute['name'] == $name) {
                            $_val = $full_product->getData($attribute['static_value']);
                            $product_data['info' . $i] = ($_val) ? $_val : '';
                        }
                    }
                }

                // now test if basic value are present , if not the add them
                // product_id
                if (empty($product_data['product_id'])) {
                    $product_data['product_id'] = $tmp_product->getId();
                }
                // product_description
                if (empty($product_data['product_name'])) {
                    $product_data['product_name'] = utf8_decode(str_replace(",", " - ", $tmp_product->getName()));
                }
                // product_link
                if (empty($product_data['url'])) {
                    $product_data['url'] = $this->getProductUrlOrParentUrl($productId, $tmp_product->getUrlInStore(array('_store' => $order->getStoreId())), $order->getStoreId());
                }
                // product_image_link
                if (empty($product_data['url_image'])) {
                    try {
                        $product_data['url_image'] = $this->getProductImageOrParentImage($productId, $tmp_product->getImageUrl());
                    } catch (Exception $e) {
                        $product_data['url_image'] = '';
                    };
                }
                // product_sku
                if (empty($product_data['sku'])) {
                    $product_data['sku'] = $full_product->getSku();
                }
                $products_arr[] = $this->plaProducts[$productId] = $product_data;
            }
        }
        return $products_arr;
    }

    protected function defaultProductsData($order) {
        $products = $order->getAllVisibleItems(); //filter out simple products
        $products_arr = array();
        foreach ($products as $product) {
            $productId = $product->getProductId();
            if (!empty($this->defaultProducts[$productId])) {
                $products_arr[] = $this->defaultProducts[$productId];
            } else {
                $full_product = Mage::getModel('catalog/product')->load($productId);
                $product_data = array();
                $product_data['product_name'] = utf8_decode(str_replace(",", " - ", $full_product->getName()));
                $product_data['product_id'] = $full_product->getId();
                $product_data['sku'] = $full_product->getSku();
                $product_data['url'] = '';
                $product_data['url_image'] = '';
                try {
                    $product_data['url'] = $this->getProductUrlOrParentUrl($productId, $full_product->getUrlInStore(array('_store' => $order->getStoreId())), $order->getStoreId());
                    $product_data['url_image'] = $this->getProductImageOrParentImage($productId, $full_product->getImageUrl());
                } catch (Exception $e) {
                    
                }

                $products_arr[] = $this->defaultProducts[$productId] = $product_data;
            }
        }
        return $products_arr;
    }

    public function getAllPlaConfiguration() {
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope', 'stores')
                ->addFieldToFilter('path', 'avisverifies/extra/pla_configuration');
        $array = array();
        foreach ($resource as $store) {
            $json = json_decode($store->getData('value'), true);
            if (is_array($json)) {
                $array[$store->getData('scope_id')] = $json;
            }
        }
        return $array;
    }

    public function getAllShopName() {
        $array = array();
        foreach (Mage::getModel('core/store')->getCollection() as $store) {
            $array[$store->getId()] = $store->getName();
        }
        return $array;
    }

    protected function reformatDataForCSV($data) {
        $tmp = array();
        foreach ($data as $value) {
            if (!empty($value['product_id']) && !empty($value['product_name'])) {
                $tmp[] = array(
                    $value['order_id'],
                    $value['email'],
                    $value['nom'],
                    $value['prenom'],
                    $value['date'],
                    $this->delay,
                    $value['id_shop'],
                    $value['amount_order'],
                    $value['status_order'],
                    $this->safeAccessAray($value, 'product_id'),
                    $this->safeAccessAray($value, 'sku'),
                    $this->safeAccessAray($value, 'product_name'),
                    $this->safeAccessAray($value, 'url'),
                    $this->safeAccessAray($value, 'url_image'),
                    $this->safeAccessAray($value, 'brand'),
                    $this->safeAccessAray($value, 'category'),
                    $this->safeAccessAray($value, 'gtin_ean'),
                    $this->safeAccessAray($value, 'gtin_upc'),
                    $this->safeAccessAray($value, 'gtin_jan'),
                    $this->safeAccessAray($value, 'gtin_isbn'),
                    $this->safeAccessAray($value, 'mpn'),
                    $this->safeAccessAray($value, 'info1'),
                    $this->safeAccessAray($value, 'info2'),
                    $this->safeAccessAray($value, 'info3'),
                    $this->safeAccessAray($value, 'info4'),
                    $this->safeAccessAray($value, 'info5'),
                    $this->safeAccessAray($value, 'info6'),
                    $this->safeAccessAray($value, 'info7'),
                    $this->safeAccessAray($value, 'info8'),
                    $this->safeAccessAray($value, 'info9'),
                    $this->safeAccessAray($value, 'info10'),
                );
            } else {
                $tmp[$value['order_id']] = array(
                    $value['order_id'],
                    $value['email'],
                    $value['nom'],
                    $value['prenom'],
                    $value['date'],
                    $this->delay,
                    $value['id_shop'],
                    $value['amount_order'],
                    $value['status_order'],
                );
            }
        }
        return $tmp;
    }

    public function safeAccessAray($array, $index) {
        return isset($array[$index]) ? $array[$index] : '';
    }

    public function getProductUrlOrParentUrl($productId, $URL, $storeId, $baseURL = false) {

        if (!$this->forceParentId) {
            $baseUrl = ($baseURL) ? Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) : '';
            return ($URL) ? $baseUrl . $URL : '';
        }
        // get parent Id
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
        if (count($parentIds) > 0) {
            $product = Mage::getModel('catalog/product')->load($parentIds[0]);
            return $product->getUrlInStore(array('_store' => $storeId));
        } else {
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
            if (count($parentIds) > 0) {
                $product = Mage::getModel('catalog/product')->load($parentIds[0]);
                return $product->getUrlInStore(array('_store' => $storeId));
            }
        }
        // else
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getUrlInStore(array('_store' => $storeId));
    }

    public function getProductImageOrParentImage($productId, $URL, $baseURL = false) {

        if (!$this->forceParentId) {
            $baseUrl = ($baseURL) ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog/product" : '';
            return ($URL) ? $baseUrl . $URL : '';
        }
        // get parent Id
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
        if (count($parentIds) > 0) {
            $product = Mage::getModel('catalog/product')->load($parentIds[0]);
            try {
                return $product->getImageUrl();
            } catch (Exception $e) {
                return '';
            }
        } else {
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
            if (count($parentIds) > 0) {
                $product = Mage::getModel('catalog/product')->load($parentIds[0]);
                try {
                    return $product->getImageUrl();
                } catch (Exception $e) {
                    return '';
                }
            }
        }
        // else
        $product = Mage::getModel('catalog/product')->load($productId);
        try {
            return $product->getImageUrl();
        } catch (Exception $e) {
            return '';
        }
    }

}
