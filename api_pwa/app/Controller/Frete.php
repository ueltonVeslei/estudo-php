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
		    $estimate = Mage::getSingleton('av5_freteproduto/estimate');
            $productid = $post['product_id'];
            $product = Mage::getModel('catalog/product')->load($productid);

            $cartData = [
                'product' => $productid,
                'estimate' => [
                    'postcode' => $post['cep'],
                    'country_id' => 'BR',
                    'no_customer' => '1'
                ],
                'qty' => $post['qty']
            ];

            if (isset($post['options'])) {
                $cartData['super_attribute'] = [];
                foreach ((array)$post['options'] as $ind => $value) {
                    $cartData['super_attribute'][(int)$ind] = $value;
                }
            }

            $product->setAddToCartInfo($cartData);
            $estimate->setProduct($product);
            $addressInfo = $cartData['estimate'];
            $estimate->setAddressInfo((array) $addressInfo);
            $estimate->estimate();

            $rates = [];
            foreach ($estimate->getResult() as $code => $_rates):
                foreach ($_rates as $_rate):
                    if ($_rate->getPrice() <= 0)
                        continue;
                    $rateData = [];
                    $title = [];
                    $title = explode(' - ', $_rate->getMethodTitle());
                    if (!is_array($title)) {
                        $title[0] = $_rate->getMethodTitle();
                    }

                    switch($title[0]) {
                        case 'TRANSPORTADORA':
                        case 'Retirar na Loja':
                            $img = 'transportadora.png';
                            break;
                        case 'Frete Grátis':
                            $img = 'frete_gratis.png';
                            break;
                        case 'PAC':
                            $img = 'av5_correios_41068.gif';
                            break;
                        case 'Sedex':
                            $img = 'av5_correios_40010.gif';
                            break;
                        case 'E-Sedex':
                            $img = 'av5_correios_81019.gif';
                            break;
                    }

                    $rateData['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'images/av5/' . $img;
                    $rateData['method_title'] = $title[0];
                    $rateData['method_subtitle'] = (isset($title[1])) ? $title[1] : '';
                    $_excl = Mage::helper('tax')->getShippingPrice(
                        $_rate->getPrice(),
                        Mage::helper('tax')->displayShippingPriceIncludingTax(),
                        $estimate->getQuote()->getShippingAddress());
                    $rateData['price'] = $_excl;
                    $_incl = Mage::helper('tax')->getShippingPrice(
                        $_rate->getPrice(),
                        true,
                        $estimate->getQuote()->getShippingAddress());
                    $rateData['price_with_tax'] = $_incl;

                    $rates[] = $rateData;
                endforeach;
            endforeach;

            $this->setResponse('status',Standard::STATUS200);
            return $this->setResponse('data',$rates);

			/*$cep = $post['cep'];
        	$productid = $post['product_id'];

			$_product = Mage::getModel('catalog/product')->load($productid);
	        if ($_product->isSaleable()) {
	            $quote = Mage::getModel('sales/quote');

	            // PATCH INCLUSÃO DE PRODUTOS COM REQUIRED OPTIONS
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
	            // FIM PATCH

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
	        } */

			$this->setResponse('status',Standard::STATUS500);
			return $this->setResponse('data','Produto não está disponível para a venda');
		}


		$this->setResponse('status',Standard::STATUS500);
		$this->setResponse('data','Dados do CEP não informados');


	}

	// Excluir comentário do pedido
	protected function _delete() {}

}