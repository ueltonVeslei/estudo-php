<?php

define('EDRONE_SDK_VERSION','PHPSDK_1.0.0');

abstract class EdroneEvent{
	
   	protected $field = array();
	
  	abstract public function init();
   
   	public function pre_init(){
            //preInitObject            
   	}

	public function userCid($value){
		$this->field['c_id'] = trim('phpsd_'.$value);
	}

	public function userEmail($value){
		$this->field['email'] = trim($value);
	}

	public function userFirstName($value){
		$this->field['first_name'] = trim($value);
	}

	public function userLastName($value){
		$this->field['last_name'] = trim($value);
	}
        
	public function userSubscriberStatus($value){
		$this->field['subscriber_status'] = trim($value);
	}

	public function userCountry($value){
		$this->field['country'] = trim($value);
	}

	public function userCity($value){
		$this->field['city'] = trim($value);
	}
        
        public function userPhone($value){
                $this->field['phone'] = trim($value);
        }
        
        public function userTag($value){
                $this->field['customer_tags'] = trim($value);
        }
        
        
	public function get(){
		return $this->field;
		return $this;
	}	
}

class EdroneEventOrder extends EdroneEvent{
        public function init(){
		$this->field['action_type'] = 'order';
	}
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
	public function userCid($value){
            parent::userCid($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userEmail($value){
            parent::userEmail($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userFirstName($value){
            parent::userFirstName($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userLastName($value){
            parent::userLastName($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userSubscriberStatus($value){
            parent::userSubscriberStatus($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userCountry($value){
            parent::userCountry($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userCity($value){
            parent::userCity($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function userOrder($value){
            parent::userPhone($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOther
         */
        public function userTag($value){
            parent::userTag($value);
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return  \EdroneEventOrder Description
         */
        public function productSkus($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_skus'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productIds($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_ids'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productTitles($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_titles'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productImages($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_images'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productUrls($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_urls'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productCounts($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_counts'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productCategoryIds($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_category_ids'] = $value;
            return $this;
        }
        /**
         * 
         * @param type $value
         * @return \EdroneEventOrder
         */
        public function productCategoryNames($value){
            if(is_array($value)){ $value=  implode('|', $value);}
            $this->field['product_category_names'] = $value;
            return $this;
        }
        
        
        public function orderId($value){
            $this->field['order_id'] = $value;
            return $this;
        }
        
        public function orderPaymentValue($value){
            $this->field['order_payment_value'] = $value;
            return $this;
        }
        
        public function orderBasePaymentValue($value){
            $this->field['base_payment_value'] = $value;
            return $this;
        }
        
        public function orderDetails($value){
            $this->field['order_details'] = $value;
            return $this;
        }
        
        public function orderCurrency($value){
            $this->field['order_currency'] = $value;
            return $this;
        }
        
        public function orderBaseCurrency($value){
            $this->field['base_currency'] = $value;
            return $this;
        }
        
                /**
	 * @return EdroneEventOrder
	 */
	public static function create(){
		return new EdroneEventOrder();
	}       
}

class EdroneIns{
	
	/** @var string AppId */
	private $appid 			= 	'';
	/** @var string $secret Secret */
	private $secret 		= 	'';
	/** @var string Trace url. def https://api.edrone.me/trace */
	private $trace_url 		= 	'';
	/** @var array  Array of request field */
	private $fiels			= 	array();
	/** @var array  Prepared request array */
	private $preparedpack           =	array();
	/** @var Closure Closure method called on error */
        private $errorHandle            =       null;
        /** @var Closure Closure method called on success */
        private $readyHandle            =       null;
    /** @var Array Last request information */
    private $lastRequest            =       null;
	
    /**
     * Construct new Edrone Request instace
     * @param string $appid	AppId of tracker
     * @param string $secret Secret of tracer
     * @param string $trace_url def https://api.edrone.me/trace
     * @since 1.0.0
     */
	function __construct($appid,$secret,$trace_url='https://api.edrone.me/trace.php'){
		$this->appid = $appid;
		$this->secret = $secret;
		$this->trace_url = $trace_url;
	}		
	/**
	 * 
	 * Prepare event to send 
	 * 
	 * @param EdroneEvent $event Use object of EdroneEventAddToCart,EdroneEventOrder,EdroneEventOrder,EdroneEventOther
	 * @return EdroneIns
	 * @since 1.0.0
	 */
	public function prepare($event){
                if(!($event instanceof EdroneEvent )){
                    throw new Exception('Event must by child EdroneEvent class ');
                    $this->preparedpack = array();
                    return;
                }
                $event->pre_init();
		$event->init();
		$this->preparedpack = array_merge($event->get(),array(
				"app_id"	=>  $this->appid,
                                "version"       =>  EDRONE_SDK_VERSION,
                                "sender_type"   =>  'server',
		));
                //Calc sign - beta
                    ksort($this->preparedpack);
                    $sign = '';
                    foreach($this->preparedpack as $key=>$value){$sign .= $value;}
                    $this->preparedpack['sign'] = md5($this->secret.$sign);
                //
		return $this;
	}
	
	/**
	* 
	* Force send prepared data
	* @since 1.0.0
	*/
	public function send(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->trace_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($this->preparedpack));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,3); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$report=curl_getinfo($ch);
		curl_close($ch);
                $this->lastRequest = array("code"=>$httpcode,"response"=>$data,"info"=>$report,'fields'=>$this->preparedpack); 
		if( ($httpcode !== 200) && ($this->errorHandle !== null) ){
            call_user_func_array($this->errorHandle,array($this));
        }elseif ( ($httpcode === 200) && ($this->readyHandle !== null) ){
        	call_user_func_array($this->readyHandle,array($this));
        }
	}
	/**
	 * Return last request as array (debug)
	 * @return array
	 * @since 1.0.0
	 */
    public function getLastRequest(){
    	return $this->lastRequest;
   	}
        
    /**
    * Set Callbacks for error action and ready action
    * @param \Closure $errorHandle
    * @param \Closure $readyHandle
    * @since 1.0.0
    */
    public function setCallbacks($errorHandle=null,$readyHandle=null){
    	$this->errorHandle = $errorHandle;
        $this->readyHandle = $readyHandle;
   	}
}

class Edrone_Base_Block_Order extends Edrone_Base_Block_Base
{ 
    
    public function sendDataToServer($orderData,$customerData){
        try{ 
            $configHelper = Mage::helper('edrone/config');
            $edrone  = new EdroneIns($configHelper->getAppId(),'');
            $edrone->setCallbacks(function($obj){
                 error_log("EDRONEPHPSDK ERROR - wrong request:".  json_encode($obj->getLastRequest()));
            },function(){
                
            });
            $edrone->prepare(
                        EdroneEventOrder::create()->
                        userFirstName(($customerData['first_name']))->
                        userLastName(($customerData['last_name']))->
                        userEmail($customerData['email'])->
                        productSkus($orderData['sku'])->
                        productTitles($orderData['title'])->
                        productImages($orderData['image'])->
                        productCategoryIds($orderData['product_category_ids'])->
                        productCategoryNames($orderData['product_category_names'])->
                        orderId($orderData['order_id'])->
                        orderPaymentValue($orderData['order_payment_value'])->
                        orderCurrency($orderData['order_currency'])->
                        productCounts($orderData['product_counts'])
            )->send();
        }  catch (Exception $e){
            error_log("EDRONEPHPSDK ERROR:".$e->getMessage().' more :'.json_encode($e));
        }
    }
    /**
     * @return array
     */
    public function getOrderData()
    {
        
        $orderData = $skus = $titles = $images = array();

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($lastOrderId);

        $product_category_names = array();
        $product_category_ids = array();
        $product_counts       = array();
        
        foreach ($order->getAllVisibleItems() as $item) {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
            if(count($parentIds) > 0){
                $product = Mage::getModel("catalog/product")->load( $parentIds[0] );
                $skus[] = $product->getSku();
                $ids[] = $product->getId();
                $titles[] = $product->getName();
                $product_counts[] = (int)$item->getQtyOrdered();
                
                
                $_Product = Mage::getModel("catalog/product")->load( $item->getProductId()  );
                $categoryIds = $_Product->getCategoryIds();//array of product categories
                $product_category_ids[] = implode("~", $categoryIds);


                $catNamesArray = array();
                
                foreach ($categoryIds as $singleCategoryId) {
                    $category = Mage::getModel('catalog/category')->load($singleCategoryId);
                    array_push($catNamesArray,$category->getName());
                }
                $product_category_names[] = implode("~", $catNamesArray);
                
                $images[] = ($product) ? (string)Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438) : '';
            }else{
                $product = Mage::getModel("catalog/product")->load( $item->getProductId() );
                $skus[] = $product->getSku();
                $ids[] = $product->getId();
                $titles[] = $product->getName();  
                $product_counts[] = (int)$item->getQtyOrdered();
                
                $_Product = Mage::getModel("catalog/product")->load( $item->getProductId()  );
                $categoryIds = $_Product->getCategoryIds();//array of product categories
                $product_category_ids[] = implode("~", $categoryIds);
                $catNamesArray = array();
                
                foreach ($categoryIds as $singleCategoryId) {
                    $category = Mage::getModel('catalog/category')->load($singleCategoryId);
                    array_push($catNamesArray,$category->getName());
                }
                $product_category_names[] = implode("~", $catNamesArray);
                
                $images[] = ($product) ? (string)Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438) : '';
            }
        }

        $orderData['sku'] = join('|', $skus);
        $orderData['id'] = join('|', $ids);
        $orderData['title'] = join('|', $titles);
        $orderData['image'] = join('|', $images);
        $orderData['order_id'] = $order->getIncrementId();
        $orderData['order_payment_value'] = $order->getGrandTotal();
        $orderData['base_payment_value'] = $order->getBaseGrandTotal();
        $orderData['base_currency'] = $order->getBaseCurrencyCode();
        $orderData['order_currency'] = $order->getOrderCurrencyCode();
        $orderData['coupon'] = $order->getCouponCode();
        $orderData['product_category_names']  = join('|',$product_category_names);
        $orderData['product_category_ids']    = join('|',$product_category_ids);
        $orderData['product_counts'] = join('|',$product_counts);

        return $orderData;
    }

    public function getCustomerData()
    {
        parent::getCustomerData();

            $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $order = Mage::getModel('sales/order')->load($lastOrderId);

            $this->customerData['first_name'] = $order->getBillingAddress()->getFirstname();
            $this->customerData['last_name'] = $order->getBillingAddress()->getLastname();
            $this->customerData['email'] = $order->getBillingAddress()->getEmail();
            $this->customerData['country'] = $order->getBillingAddress()->getCountry();
            $this->customerData['city'] = $order->getBillingAddress()->getCity();
            $this->customerData['phone'] = $order->getBillingAddress()->getTelephone();

        return $this->customerData;
    }
}