<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 14/12/14
 * Time: 18:12
 */ 
class Saffira_Koin_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    public function  __construct() {


        parent::__construct();

        $order = new Mage_Sales_Model_Order();
        $_order = $this->getOrder();
        $orderRealId = $_order->getRealOrderId();


        $order->loadByIncrementId($orderRealId);
        $payment_method = $order->getPayment()->getMethodInstance()->getTitle();

     

        $consumerKey =  Mage::getStoreConfig("payment/Saffira_Koin_Standard/consumer_key");

        $url = "https://boleto.koin.com.br/home/pdf?t=".$consumerKey."&p=".$orderRealId;

        if($payment_method == "Koin Pós-Pago"){

            $this->_addButton('gerarBoleto', array(
                'label'     => Mage::helper('Sales')->__('Gerar Boleto Koin'),
                'onclick'   => "window.open('$url', '_blank');" ,
                'class'     => 'go'
            ), 0, 100, 'header', 'header');
        }
    }
}