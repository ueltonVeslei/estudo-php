<?php
class Controller_Frete extends Controller {

	// Retornar todos os banners secundários da home
	protected function _get() {
		$bannersDir = Mage::getBaseDir('media') . '/leimageslider/image';
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data',$bannersDir);
	}

	// Inclui comentário no pedido
	protected function _post() {
		if ($post = (array)$this->getData('body')) {
			$cep = $post['cep'];
        	$productid = $post['product_id'];

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

	            $result = '';

	            foreach ($rates as $code => $_rates){
	                foreach ($_rates as $_rate){
	                    $result .= '<li>';
	                    $title = $_rate->getMethodTitle();
	                    $result .= '<strong>' . preg_replace('/[ ]/', ' </strong>', $title, 1);
	                    $result .= " - ";
	                    $result .= $formattedPrice = Mage::helper('core')->currency($_rate->getPrice(), true, false);
	                    $result .= "</li>";
	                }
	            }

				$this->setResponse('status',Standard::STATUS200);
				return $this->setResponse('data','<ul style="list-style:none">'.$result.'</ul>');
	        }

			$this->setResponse('status',Standard::STATUS500);
			return $this->setResponse('data','Produto não está disponível para a venda');
		}


		$this->setResponse('status',Standard::STATUS500);
		$this->setResponse('data','Dados do CEP não informados');


	}

	// Excluir comentário do pedido
	protected function _delete() {}

}