<?php // Our Custome Class . different then Magento Review class.
class Netreviews_Avisverifies_Model_Reviews extends Mage_Core_Model_Abstract{
				
    public $idProduct = null;
    public $skuProduct = '';
    public $page;
    
    protected $idWebsite;
    protected $limit;
    
    
    
    public function _construct() {
        $this->_init('avisverifies/reviews');
        $string = Mage::getStoreConfig('avisverifies/extra/relatedstoreslist');
        $string = (empty($string))? '' : $string;
        $this->idWebsite = explode(';', $string);
        $this->limit = ( Mage::getStoreConfig('avisverifies/extra/number_reviews_in_product_page') ) ? (int) Mage::getStoreConfig('avisverifies/extra/number_reviews_in_product_page') : 5;
        if ( is_integer( $this->limit ) != true || $this->limit == 0 ) {
            $this->limit = 5;
        }
        $this->page = 1;
    }
    
    
    
    protected function throwException() {
        $DATA = Mage::helper('avisverifies/Data');
        $this->skuAndId();
        if ($this->idProduct === null) {
            $DATA->echome('<!-- Please specify: "idProduct" -> ($your_object->idProduct = $idProduct). -->');
        }
    }
    
    
    
    protected function skuAndId(){
        if (Mage::registry('product') && empty($this->idProduct) && empty($this->skuProduct)) {
            $this->idProduct = Mage::registry('product')->getId();
            $this->skuProduct = Mage::registry('product')->getSku();
        }
        elseif (Mage::registry('product') && $this->idProduct == Mage::registry('product')->getId()) {
            $this->idProduct = Mage::registry('product')->getId();
            $this->skuProduct = Mage::registry('product')->getSku();
        }
        elseif (Mage::registry('product') && $this->skuProduct == Mage::registry('product')->getSku()) {
            $this->idProduct = Mage::registry('product')->getId();
            $this->skuProduct = Mage::registry('product')->getSku();
        }
        elseif (!empty($this->idProduct) && empty($this->skuProduct)) {
            $product = Mage::getModel('catalog/product')->load($this->idProduct);
            $this->idProduct = $product->getId();
            $this->skuProduct = $product->getSku();
        }
        elseif(!empty($this->skuProduct) && empty($this->idProduct)) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->idProduct);
            $this->idProduct = $product->getId();
            $this->skuProduct = $product->getSku();
        }
        elseif (empty($this->skuProduct) && empty($this->idProduct)) {
            $this->idProduct = NULL;
            $this->skuProduct = NULL;
        }
    }
    
    
    
    public function getReviewsCount() { 
        $this->throwException();
        $active = Mage::getStoreConfig(strtolower('AVISVERIFIES/system/GETPRODREVIEWS')); //récupérer les avis produits ou non			
        
        $stats = $this->getStatsProduct();
        if (($stats !== NULL) && ($stats->getData('nb_reviews') > 1 && $active == 'yes')) {
            return $stats->getData('nb_reviews');
        }
        else {
            return '';
        }
    }
    
    
    
    public function getStatsProduct() { 
        $this->throwException();
        try {
            $collection = Mage::getModel('avisverifies/average')->getCollection()
                    ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                    ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)));
            return ($collection)? $collection->getFirstItem() : Mage::getModel('avisverifies/average');
        } catch (Exception $ex) {
            return Mage::getModel('avisverifies/average');
        }
        
    }
    
    
    
    public function getProductReviews( $_sorting = 'horodate_DESC' ) {
        $this->throwException();
        
        // Define query order according to the filter
        $a_sorting = explode( "_", $_sorting );
        $sortBy = $a_sorting[0];
        $query_order = $a_sorting[1];
        
        // Security query sorting filter
        if ( $sortBy != "rate" && $sortBy != "helpfulrating" ) {
            $sortBy = "horodate";
        }
        
        if ( $query_order != "ASC" && ! is_numeric( $query_order ) ) {
            $query_order = "DESC";
        } elseif ( $query_order == 1 || $query_order == 2 || $query_order == 3 || $query_order == 4 || $query_order == 5 ) {
            $sortBy = "rate";
        } /*elseif ( $query_order > 5 || $query_order <= 0 ) { // if another number like 83.
            $query_order = "DESC";
        }*/
        
        // Query
        if ( $sortBy == "horodate" && $query_order == "ASC" ) { // Simple sort filter.
            try {
                $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                            ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)))
                            ->addAttributeToSort( $sortBy, $query_order )
                            ->setPageSize($this->limit)
                            ->setCurPage($this->page);
                $maxPages = $collection->getLastPageNumber();
                if ($this->page > $maxPages) {
                    return array();
                }    
                else {
//return "1) " . $sortBy . " " . $query_order;
                    return $collection;
                }
            } catch (Exception $ex) {
                return array();
            }
        } elseif ( $sortBy == "rate" && is_numeric( $query_order ) ) { // Double sort filter.
            try {
                $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                            ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)))
                            ->addFieldToFilter( 'rate', $query_order )
                            ->addAttributeToSort( 'horodate', 'DESC' )
                            ->setPageSize($this->limit)
                            ->setCurPage($this->page);
                $maxPages = $collection->getLastPageNumber();
                if ($this->page > $maxPages) {
                    return array();
                }    
                else {
//return "2) " . $sortBy . " " . $query_order;
                    return $collection;
                }
            } catch (Exception $ex) {
                return array();
            }
        } else { // Double sort filter.
            try {
                $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                            ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)))
                            ->addAttributeToSort( $sortBy, $query_order )
                            ->addAttributeToSort( 'horodate', 'DESC' )
                            ->setPageSize($this->limit)
                            ->setCurPage($this->page);
                $maxPages = $collection->getLastPageNumber();
                if ($this->page > $maxPages) {
                    return array();
                }    
                else {
//return "3) " . $sortBy . " " . $query_order;
                    return $collection;
                }
            } catch (Exception $ex) {
                return array();
            }
        }
        // end Query
        
    }
    
    
    
    public function getProductReviewsByRate( $_rate = 5 ) {
        $this->throwException();
        
        // Define query order according to the filter
        $a_sorting = explode( "_", $_sorting );
        $sortBy = $a_sorting[0];
        $query_order = $a_sorting[1];
        
        // Security query sorting filter
        if ( $sortBy != "rate" && $sortBy != "helpfulrating" ) {
            $sortBy = "horodate";
        }
        if ( $query_order != "ASC" ) {
            $query_order = "DESC";
        }
        
        if ( $sortBy == "horodate" && $query_order == "ASC" ) { // Simple sort filter.
            try {
                $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                            ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)))
                            ->addAttributeToSort( $sortBy, $query_order )
                            ->setPageSize($this->limit)
                            ->setCurPage($this->page);
                $maxPages = $collection->getLastPageNumber();
                if ($this->page > $maxPages) {
                    return array();
                }    
                else {
                    return $collection;
                }
            } catch (Exception $ex) {
                return array();
            }
        } else { // Double sort filter.
            try {
                $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                            ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)))
                            ->addAttributeToSort( $sortBy, $query_order )
                            ->addAttributeToSort( 'horodate', 'DESC' )
                            ->setPageSize($this->limit)
                            ->setCurPage($this->page);
                $maxPages = $collection->getLastPageNumber();
                if ($this->page > $maxPages) {
                    return array();
                }    
                else {
                    return $collection;
                }
            } catch (Exception $ex) {
                return array();
            }
        }
        
    }
		
    public function getNbAvis() {
        $this->throwException();
        try {
            $collection = Mage::getModel('avisverifies/average')->getCollection()
                    ->addFieldToFilter('website_id',array("in"=>$this->idWebsite))
                    ->addFieldToFilter('ref_product',array("in"=>array($this->idProduct)));
            if ($collection) {
                $first = $collection->getFirstItem();
                return $first->getData('nb_reviews');
            }
            else {
                return NULL;
            }  
        } catch (Exception $ex) {
            return NULL;
        }
    }
    
    
    
    // for Backward compatibility.
    public function getNote() {
        return $this->getReviewNote();
	}
    
    public function getReviewNote() {
        $this->throwException();
        try {
            $collection = Mage::getModel('avisverifies/reviews')->getCollection()
                        ->addFieldToFilter('website_id', array("in" => $this->idWebsite))
                        ->addFieldToFilter('ref_product', array("in" => array($this->idProduct, $this->skuProduct)))
                        ->addAttributeToSort('horodate', 'desc')
            ;
            //get the first item of the collection (load will be called automatically)
            $first = $collection->getFirstItem();
            //look at the data in the first item
            return $first->getData('rate');
        } catch (Exception $ex) {
            return NULL;
        }
    }

    public function discussion($discussion, $review) {
        $my_review = array();
        $unserialized_discussion = array();
        try {
            $unserialized_discussion = unserialize($this->AV_decode_base64($discussion));
        } catch (Exception $exc) {
            // Handle unserialize error normal string .
        }
        // Handle unserialize false .
        if ($unserialized_discussion === false) {
            return array();
        }
        foreach ($unserialized_discussion as $each_discussion) {
            // test if timestamp or not
            if (is_numeric($each_discussion['horodate'])) {
                $k_discussion = $each_discussion['horodate'];
            } else {
                $k_discussion = strtotime($each_discussion['horodate']);
            }
            // if same time as older review just add +1,
            if (isset($my_review[$k_discussion])) {
                $k_discussion++;
            }

            $my_review[$k_discussion]['commentaire'] = $each_discussion['commentaire'];
            $my_review[$k_discussion]['horodate'] = $k_discussion;
            if ($each_discussion['origine'] == 'ecommercant') {
                $my_review[$k_discussion]['origine'] = Mage::helper('avisverifies')->__('Webmaster');
            } elseif ($each_discussion['origine'] == 'internaute') {
                $my_review[$k_discussion]['origine'] = urldecode($review->getData('customer_name'));
            } else {
                $my_review[$k_discussion]['origine'] = Mage::helper('avisverifies')->__('Moderator');
            }
        }
        // sort the array ASC
        ksort($my_review);
        return $my_review;
    }
    
    
    
    public function formatNote($note){
        return is_numeric($note)? round($note*1,1): (!empty($note)?$note:"");
    }
    
    
    
    // Get number or reviews with rate equal to 1.
    public function getNbAvis1() {
        $this->throwException();
        
        try {
            $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                        ->addFieldToFilter( 'website_id', array( "in" => $this->idWebsite ) )
                        ->addFieldToFilter( 'ref_product', array( "in" => array( $this->idProduct, $this->skuProduct ) ) )
                        ->addFieldToFilter('rate', 1);
            $nbAvis_1 = $collection->getSize();
            return $nbAvis_1;
        } catch (Exception $ex) {
            return 00;
        }
    }
    
    
    
    public function addGroupByRateFilter()
    {
        $this->getSelect()->group('rate');
        return $this;
    }
    public function getNbAvisByRate() {
        $this->throwException();
        
        try {
            $collection =  Mage::getModel('avisverifies/reviews')->getCollection()
                        ->addFieldToFilter( 'website_id', array( "in" => $this->idWebsite ) )
                        ->addFieldToFilter( 'ref_product', array( "in" => array( $this->idProduct, $this->skuProduct ) ) )
                        ->addAttributeToSort('rate', 'DESC');
            
             $collection ->getSelect()
                ->columns('COUNT(*) AS ratingsize')
                ->group('rate');
             
            return $collection;
            
        } catch (Exception $ex) {
            return array();
        }
    }
    
    
    
    public function AV_encode_base64($sData){
        $sBase64 = base64_encode($sData);
        return strtr($sBase64, '+/', '-_');
    }
    
    
    
    public function AV_decode_base64($sData){
        $sBase64 = strtr($sData, '-_', '+/');
        return base64_decode($sBase64);
    }
    
    
    
    public function AV_sgbd_decode($value) {
		return stripslashes(urldecode($value));
	}
}
