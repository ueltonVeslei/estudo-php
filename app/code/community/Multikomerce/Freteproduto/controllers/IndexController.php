<?php

class Multikomerce_Freteproduto_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $cep = $this->getRequest()->getParam('cep');
        $productid = $this->getRequest()->getParam('productid');

        $_product = Mage::getModel('catalog/product')->load($productid);
        if ($_product->isSaleable()) {

            $quote = Mage::getModel('sales/quote');
            
            /** PATCH INCLUSÃO DE PRODUTOS COM REQUIRED OPTIONS **/
            $buyInfo = array(
            		'qty'           => 1,
            		'product_id'    => $_product->getId()
            );
            $options = array();
            foreach ($_product->getOptions() as $option) {
            	if ($option->getIsRequire()) {
            		$id = $option->getOptionId();
            		if (in_array($option->getType(),array('radio','drop_down','checkbox','multiple'))) {
            			$values = $option->getValues();
            			foreach ($values as $value) {
            				$options[$id] = $value->getOptionTypeId();
            				break;
            			}
            		} elseif (in_array($option->getType(),array('field','area'))) {
            			$options[$id] = 'VALOR PADRÃO';
            		} elseif (in_array($option->getType(),array('date','date_time','time'))) {
            			$options[$id] = date('Y-m-d');
            		} else {
            			$options[$id] = 'SEM ARQUIVO';
            		}
            	}
            }
            if($options) {
            	$buyInfo['options'] = $options;
            }
            $quote->addProduct($_product, new Varien_Object($buyInfo));
            /** FIM PATCH **/
            
            $quote->getShippingAddress()->setCountryId('BR');
            $quote->getShippingAddress()->setPostcode($cep);

            $quote->getShippingAddress()->setCollectShippingRates(true);
            
            $quote->getShippingAddress()->collectTotals();
            
            $rates = $quote->getShippingAddress()->getGroupedAllShippingRates();

            foreach ($rates as $code => $_rates){
                foreach ($_rates as $_rate){
                    echo '<p>';
                    $title = $_rate->getMethodTitle();
                    echo '<strong>' . preg_replace('/[ ]/', ' </strong>', $title, 1);
                    echo " - ";
                    echo $formattedPrice = Mage::helper('core')->currency($_rate->getPrice(), true, false);;
                    echo "</p>";
                }
                
            }
        }
    }

    public function comandoAction() {
        echo "Comando";
    }

}