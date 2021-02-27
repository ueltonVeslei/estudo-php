<?php class Axado_Shipping_Model_Carrier_Status extends Varien_Event_Observer
{
    public function notifySaleEvent(Varien_Event_Observer $observer) {
        $order = $observer->getInvoice()->getOrder();
        $orderId = $order->getId();
        $quoteId = $order->getQuoteId();
        $shippingMethod = $order->getShippingMethod();

        /*
         * Dreadful Magento's paradigm makes it easier to query using simple SQL 
         * rather than using its abnormal "magic methods".
         *
         * TODO: Change it back. Or not. Hey, don't look at me, it works!
         */
        $axado_token = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll("
            SELECT axado_token 
            FROM sales_flat_quote_shipping_rate 
            WHERE address_id = (
                SELECT address_id
                FROM sales_flat_quote_address
                WHERE quote_id = $quoteId
                AND shipping_method = '$shippingMethod')
            AND code = '$shippingMethod'");

	if (isset($axado_token[0]['axado_token'])) {
            $token_split = explode('-', $axado_token[0]['axado_token']);
            $json = '{"status": 2}';
    
            $_token = Mage::getStoreConfig('carriers/axado/token');
            $api_url = "http://api.axado.com.br/v2/cotacao/{$token_split[0]}/{$token_split[1]}/status/?token=$_token";
    
            $client = new Zend_Http_Client($api_url);
            $client->setRawData($json, 'text');
            $httpResponse = $client->request('PUT');
            
            if (!$httpResponse) {
                Mage::log("Error while communicating with Axado API.", null, 'axado.log');
                Mage::log("Order ID: $orderId", null, 'axado.log');
                Mage::log("Quote ID: $quoteId", null, 'axado.log');
                Mage::throwException("Error while communicating with Axado API.");
            } else {
                if ($httpResponse->getStatus() != 200){
                    Mage::log("Error while hiring.", null, 'axado.log');
                    Mage::log("Order ID: $orderId", null, 'axado.log');
                    Mage::log("Quote ID: $quoteId", null, 'axado.log');
                    Mage::log("Reason: {$httpResponse->getBody()}", null, 'axado.log');
                    #Mage::throwException($httpResponse->getBody());
                }
            }
        }

        return $this;
    }
}
