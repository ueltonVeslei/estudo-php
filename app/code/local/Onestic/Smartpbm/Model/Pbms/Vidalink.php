<?php
/**
 * Onestic - Smart PBMs
 *
 * @title      Magento -> Módulo Smart PBMs
 * @category   Integração
 * @package    Onestic_Smartpbm
 * @author     Onestic
 * @copyright  Copyright (c) 2016 Onestic
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Smartpbm_Model_Pbms_Vidalink extends Onestic_Smartpbm_Model_Pbms_Abstract {
	
    protected $_name = 'vidalink';
    protected $_label = 'Vidalink';
    protected $_cnpj;
    
    protected $_urlP = 'https://www.vidalink.com.br/AutorizadorWs/autorizador.asmx?WSDL';
    protected $_url = 'https://www.vidalink.com.br/AutorizadorWs/autorizador.asmx';
    
	public function __construct() {
		$this->_environment = self::ENV_PROD;
		$this->_url = $this->_urlP;
		$this->_cnpj = Mage::helper('smartpbm')->getConfigData('vidalink/cnpj');
	}
	
	public function listaConvenios() {
	    $obj = new stdClass();
	    $obj->sCnpj = $this->_cnpj;
	    $return = null;
	    try { 
	       $response = $this->getClient()->listaConvenios($obj);
	       
	       if (!empty($response)) {
	           $return = new SimpleXMLElement($response->listaConveniosResult);
	       }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function validaCartao($card, $conv) {
	    $obj = new stdClass();
	    $obj->sCnpj = $this->_cnpj;
	    $obj->sConvenio = $conv;
	    $obj->sCartao = $card;
	    $return = null;
	    try {
            $response = $this->getClient()->validaCartao($obj);
	    
	        if (!empty($response)) {
                $return = new SimpleXMLElement($response->validaCartaoResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
        return $return;
	}
	
	public function enviaProduto($data) {
	    $this->_debug('INICIA ENVIO DE PRODUTO');
	    $obj = new stdClass();
	    $obj->sCnpj = $this->_cnpj;
	    $obj->sCupom = '';
	    $obj->sConvenio = $data['conv'];
	    $obj->sCartao = $data['card'];
	    $obj->sTerminal = '0';
	    $obj->sTipoConselho = 'CRM';
	    $obj->sNrConselho = $data['crm'];
	    $obj->sUfConselho = $data['uf'];
	    $obj->sDtReceita = date('dmY');
	    
	    // xmlProdutos
	    $xmlProdutos = '<?xml version="1.0" encoding="UTF-8"?><produtos>';
	    $xmlProdutos .= '<produto><ean>' . $data['product']->getEan() . '</ean>' .
	       '<quantidade>1</quantidade>' . 
	       '<codigo_interno>' . $data['product']->getId() . '</codigo_interno>' . 
	       '<preco_bruto>' . number_format($data['product']->getFinalPrice(),2,'','') . '</preco_bruto>' . 
	       '<preco_liquido>' . number_format($data['product']->getFinalPrice(),2,'','') . '</preco_liquido>' . 
	       '<descricao>' . $data['product']->getName() . '</descricao>' . 
	       '</produto>';
	    $xmlProdutos .= '</produtos>';
	    $obj->sProdutos = $xmlProdutos;
	    
	    
	    $return = null;
	    try {
	        $response = $this->getClient()->enviaProdutos($obj);
	         
	        if (!empty($response)) {
	            $return = new SimpleXMLElement($response->enviaProdutosResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	     
	    return $return;
	}
	
	public function enviaProdutos($card, $conv) {
	    $obj = new stdClass();
	    $obj->sCnpj = $this->_cnpj;
	    $obj->sConvenio = $conv;
	    $obj->sCartao = $card;
	    $obj->sTerminal = '0';
	    $obj->sTipoConselho = '';
	    $obj->sNrConselho = '';
	    $obj->sUfConselho = '';
	    $obj->sDtReceita = '';
	    
	    // xmlProdutos
	    $xmlProdutos = '<?xml version="1.0" encoding="UTF-8"?><produtos>';
	    $items = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
	    foreach($items as $item) {
	        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
	        $xmlProdutos .= '<produto><ean>' . $product->getEan() . '</ean><quantidade>' . $item->getQty() . '</quantidade><preco_bruto>' . number_format($product->getFinalPrice(),2,'','') . '</preco_bruto><preco_liquido>' . number_format($product->getFinalPrice(),2,'','') . '</preco_liquido><descricao>' . $product->getName() . '</descricao></produto>'; 
	    }
	    $xmlProdutos .= '</produtos>';
	    $obj->sProdutos = $xmlProdutos;
	    
	    $return = null;
	    try {
    	    $response = $this->getClient()->enviaProdutos($obj);
    	    $this->_debug("enviaProdutosResponse: " . var_export($response,true));
	    
	        if (!empty($response)) {
        	    $return = new SimpleXMLElement($response->enviaProdutosResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    
	    return $return;
	}
	
	public function efetivaVenda($data) {
	    $obj = new stdClass();
	    $obj->sCnpj = $this->_cnpj;
	    $obj->sConvenio = $data['conv'];
	    $obj->sCartao = $data['card'];
	    $obj->sCupom = $data['order'];
	    $obj->sAutorizacao = $data['auth'];
	     
	    // xmlProdutos
	    $xmlProdutos = '<?xml version="1.0" encoding="UTF-8"?><produtos>';
	    $items = Mage::getModel('sales/order')->load($order)->getAllVisibleItems();
	    foreach($items as $item) {
	        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
	        $xmlProdutos .= '<produto><ean>' . $product->getEan() . '</ean><quantidade>' . $item->getQty() . '</quantidade></produto>';
	    }
	    $xmlProdutos .= '</produtos>';
	    $obj->sProdutos = $xmlProdutos;
	     
	    $return = null;
	    try {
	        $response = $this->getClient()->efetivaVenda($obj);
	        $this->_debug("efetivaVendaResponse: " . var_export($response,true));
	         
	        if (!empty($response)) {
	            $return = new SimpleXMLElement($response->efetivaVendaResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	     
	    return $return;
	}
	
	public function confirmaTransacao($order, $auth) {
	    $obj = new stdClass();
	    $obj->sAutorizacao = $auth;
	
	    // xmlProdutos
	    $xmlProdutos = '<?xml version="1.0" encoding="UTF-8"?><produtos>';
	    $items = Mage::getModel('sales/order')->load($order)->getAllVisibleItems();
	    foreach($items as $item) {
	        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
	        $xmlProdutos .= '<produto><ean>' . $product->getEan() . '</ean></produto>';
	    }
	    $xmlProdutos .= '</produtos>';
	    $obj->sCRMProdutos = $xmlProdutos;
	
	    $return = null;
	    try {
	        $response = $this->getClient()->confirmaTransacao($obj);
	        $this->_debug("confirmaTransacaoResponse: " . var_export($response,true));
	
	        if (!empty($response)) {
	            $return = new SimpleXMLElement($response->confirmaTransacaoResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	
	    return $return;
	}
	
	public function anulaTransacao($auth) {
	    $obj = new stdClass();
	    $obj->sAutorizacao = $auth;
	    $return = null;
	    try {
    	    $response = $this->getClient()->anulaTransacao($obj);
    	    $this->_debug("anulaTransacaoResponse: " . var_export($response,true));
	    
	        if (!empty($response)) {
        	    $return = new SimpleXMLElement($response->anulaTransacaoResult);
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function validaBeneficio($data) {
	    $check = $this->validaCartao($data['card'],$data['conv']);
	    if ($check->status == 'OK') {
	        if (Mage::getResourceModel('smartpbm/products')->checkProgram($data['product'],'vidalink')) { // Produto é elegível ao Vidalink
	            $product = Mage::getModel('catalog/product')->load($data['product']);
	            if ($product->getEan()) {
	                $data['product'] = $product;
	                $price = $this->enviaProduto($data);
	                if (is_object($price)) {
	                    $discount = str_replace(',','.',$price->valor_desconto);
	                    Mage::getSingleton('checkout/session')->setSmartpbmPbm('vidalink');
	                    Mage::getSingleton('checkout/session')->setSmartpbmCard($data['card']);
	                    Mage::getSingleton('checkout/session')->setSmartpbmDiscount($discount/100);
	                    Mage::getSingleton('checkout/session')->setSmartpbmEan($product->getEan());
	                    return true;
	                }
	            }
	        }
	    }
	     
	    return false;
	}
	
	public function confirmaBeneficio($data) {
	    $this->efetivaVenda($data);
	}

}