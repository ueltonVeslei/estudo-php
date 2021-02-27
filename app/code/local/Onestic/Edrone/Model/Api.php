<?php
class Onestic_Edrone_Model_Api
{
    const API_URL           = 'https://api.edrone.me/topic_publish';
    protected $params       = NULL;
    protected $appId        = NULL;
    protected $product      = NULL;
    protected $productID    = NULL;

    public function send($productID)
    {
        $this->productID = $productID;
        $this->_request();
    }

    protected function _getAppId()
    {
        return Mage::helper('onestic_edrone')->getConfig('app_id');
    }

    protected function _getProduct()
    {
        if (!$this->product) {
            $this->product = Mage::getModel('catalog/product')->load($this->productID);
        }

        return $this->product;
    }

    protected function _getCategoryNames()
    {
        $product = $this->_getProduct();
        $categories = $product->getCategoryIds();
        $names = [];

        foreach ($categories as $category) {
            $catName = Mage::getModel('catalog/category')->load($category);
            $names[] = $catName->getName();
        }

        return implode(',',$names);
    }

    protected function _getParams()
    {
        $product = $this->_getProduct();
        Mage::log($product,null,'log_edr.log');
        $this->params = [
            'app_id=' . $this->_getAppId(),
            'product_ids=' . $product->getId(),
            'topic_id=' . $product->getId(),
            'product_urls=' . $product->getProductUrl(),
            'product_skus=' . $product->getSku(),
            'product_titles=' . $product->getName(),
            'product_images=' . Mage::helper('catalog/image')->init($product, 'image')->resize(1000),
            'product_category_ids=' . implode(',',$product->getCategoryIds()),
            'product_category_names=' . $this->_getCategoryNames()
        ];
        Mage::log($this->params,null,'log_edrone.log');
        return '?' . implode('&',$this->params);
    }

    protected function _request()
    {
        Mage::log('URL EDRONE: ' . self::API_URL . $this->_getParams(),null,'log_edrone_request.log');
        try {
            $curl_session = curl_init();
            curl_setopt($curl_session, CURLOPT_URL, self::API_URL . $this->_getParams());
            curl_setopt($curl_session, CURLOPT_FAILONERROR, true);
            curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl_session, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl_session);
            Mage::log($response,null,'log_edrone_2.log');
            curl_close($curl_session);
        } catch (Exception $e) {
            Mage::logException("Erro: " . $e->getMessage());
        };
    }
}