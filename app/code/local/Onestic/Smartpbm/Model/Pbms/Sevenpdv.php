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
class Onestic_Smartpbm_Model_Pbms_Sevenpdv extends Onestic_Smartpbm_Model_Pbms_Abstract {
	
    protected $_name = 'sevenpdv';
    protected $_label = 'Seven PDV';
    protected $_cnpj;
    protected $_sistema;
    protected $_chave;
    protected $_tabela;
    protected $_nrcentral;
    protected $_horacentral;
    protected $_ctlap;
    protected $_interface;
    protected $_transaction;
    protected $_administradora;
    
    protected function checkDesvio($data) {
        switch ($data) {
            case 'CargaTabela':
                $this->tabela();
                break;
            case 'LogOn':
                $this->logon();
                break;
        }
    }
    
    protected function getTransaction() {
        if (!$this->_transaction) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvTransaction()) {
                $this->_transaction = Mage::getSingleton('checkout/session')->getSevenpdvTransaction();
            } else {
                $this->_transaction = Mage::helper('smartpbm')->getConfigData('sevenpdv/transaction') + 1;
                Mage::getSingleton('checkout/session')->setSevenpdvTransaction($this->_transaction);
            }
        }
        
        return $this->_transaction;
    }
    
    protected function getChave() {
        if (!$this->_chave) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvChave()) {
                $this->_chave = Mage::getSingleton('checkout/session')->getSevenpdvChave();
            } else {
                $this->_chave = Mage::helper('smartpbm')->getConfigData('sevenpdv/chave');
                Mage::getSingleton('checkout/session')->setSevenpdvChave($this->_chave);
            }
        }
    
        return $this->_chave;
    }
    
    protected function getNrCentral() {
        if (!$this->_nrcentral) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvNrCentral()) {
                $this->_nrcentral = Mage::getSingleton('checkout/session')->getSevenpdvNrCentral();
            } else {
                $this->_nrcentral = '0';
                Mage::getSingleton('checkout/session')->setSevenpdvNrCentral($this->_nrcentral);
            }
        }
    
        return $this->_nrcentral;
    }
    
    protected function getHoraCentral() {
        if (!$this->_horacentral) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvHoraCentral()) {
                $this->_horacentral = Mage::getSingleton('checkout/session')->getSevenpdvHoraCentral();
            } else {
                $this->_horacentral = date('c');
                Mage::getSingleton('checkout/session')->setSevenpdvHoraCentral($this->_horacentral);
            }
        }
    
        return $this->_horacentral;
    }
    
    protected function getCtlAP() {
        if (!$this->_ctlap) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvCtlAP()) {
                $this->_ctlap = Mage::getSingleton('checkout/session')->getSevenpdvCtlAP();
            } else {
                $this->_ctlap = 'AP00';
                Mage::getSingleton('checkout/session')->setSevenpdvCtlAP($this->_ctlap);
            }
        }
    
        return $this->_ctlap;
    }
    
    protected function getAdministradora() {
        if (!$this->_administradora) {
            if (Mage::getSingleton('checkout/session')->getSevenpdvAdministradora()) {
                $this->_administradora = Mage::getSingleton('checkout/session')->getSevenpdvAdministradora();
            } else {
                $this->_administradora = '999';
                Mage::getSingleton('checkout/session')->setSevenpdvAdministradora($this->_administradora);
            }
        }
    
        return $this->_administradora;
    }
    
    protected function updateTabela($image) {
        // PROGRAMAR O ATUALIZADOR DA TABELA
        $table = new SimpleXMLElement($image);
        Mage::helper('smartpbm')->setConfigData('sevenpdv/tabela',$table['VersaoTabela']);
        Mage::helper('smartpbm')->setConfigData('sevenpdv/interface',$table['VersaoInterface']);
        $this->_tabela = Mage::helper('smartpbm')->getConfigData('sevenpdv/tabela');
        $this->_interface = Mage::helper('smartpbm')->getConfigData('sevenpdv/interface');
        foreach ($table->Produtos->Produto as $produto) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('ean',$produto['EAN']);
            if ($product) {
                Mage::getResourceModel('smartpbm/products')->newRelation($product->getId(),'sevenpdv');
            }
        }
    }
    
    protected function updateSessionData($data) {
        if ($data->Sessao) {
            $this->_chave = NULL;            
            Mage::getSingleton('checkout/session')->setSevenpdvChave($data->Sessao);
        }
        
        if ($data->NrCentral) {
            $this->_nrcentral = NULL;
            Mage::getSingleton('checkout/session')->setSevenpdvNrCentral($data->NrCentral);
        }
        
        if ($data->HoraCentral) {
            $this->_horacentral = NULL;
            Mage::getSingleton('checkout/session')->setSevenpdvHoraCentral($data->HoraCentral);
        }
            
        if ($data->CtlAP) {
            $this->_ctlap = NULL;
            Mage::getSingleton('checkout/session')->setSevenpdvCtlAP($data->CtlAP);
        }
        
        if ($data->Administradora) {
            $this->_administradora = NULL;
            Mage::getSingleton('checkout/session')->setSevenpdvAdministradora($data->Administradora);
        }
    }
    
	public function __construct() {
	    //parent::__contruct();
		$this->_environment = self::ENV_PROD;
		$this->_cnpj = Mage::helper('smartpbm')->getConfigData('sevenpdv/cnpj');
		$this->_sistema = Mage::helper('smartpbm')->getConfigData('sevenpdv/sistema');
		$this->_tabela = Mage::helper('smartpbm')->getConfigData('sevenpdv/tabela');
		$this->_interface = Mage::helper('smartpbm')->getConfigData('sevenpdv/interface');
	}
	
	public function logon() {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVLogOnV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('c');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->VersaoTabela = $this->_tabela;
	    $obj->VersaoInterface = $this->_interface;
	    $return = null;
	    try { 
	       $this->_debug("LogonRequest: " . var_export($obj,true));
	       $response = $this->getClient()->LogOn($obj);
	       $this->_debug("LogonResponse: " . var_export($response,true));
	       
	       if (!empty($response)) {
	           $return = $response->LogOnResult;
	           $this->updateSessionData($return);
	           if ($return->DesvioFluxo) {
	               $this->checkDesvio($return->DesvioFluxo);
	           }
	       }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function tabela() {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVTabelaV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('c');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->VersaoTabela = $this->_tabela;
	    $obj->VersaoInterface = $this->_interface;
	    $return = null;
	    try { 
	       $response = $this->getClient()->Tabela($obj);
	       //$this->_debug("TabelaResponse: " . var_export($response,true));
	       
	       if (!empty($response)) {
	           $return = $response->TabelaResult;
	           $this->updateTabela($return->ImagemTabela);
	           if ($return->DesvioFluxo) {
	               $this->checkDesvio($return->DesvioFluxo);
	           } else {
	               $this->logon();
	           }
	       }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }

	    return $return;
	}
	
	public function consultaProduto($data) {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVPreAuthV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('c');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->Administradora = $this->getAdministradora();
	    $obj->Terminal = '00000000';
	    $obj->OrigemServico = ord('B');
	    $obj->IdOrigem = $this->_cnpj;
	    $obj->OpcaoOperador = ord('X');
	    $obj->Usuario = 'AUTHB2C';
	    $obj->CPFConsumidor = $data['cpf'];
	    $obj->CPFAtendimento = '';
	    $obj->Cartao = $data['card'];
	    $obj->QtdeSolicitada = $data['qty'];
	    
	    $product = Mage::getModel('catalog/product')->load($data['product']);
	    $obj->EAN = $product->getEan();
	    $obj->PrecoBruto = number_format($product->getFinalPrice(),2,'','');
	    $obj->PrecoLiquido = number_format($product->getFinalPrice(),2,'','');
	    
	    $return = null;
	    try {
	        $response = $this->getClient()->PreAuthV1($obj);
	
	        if (!empty($response)) {
	            $return = $response->PreAuthV1Result;
	            $this->updateSessionData($return);
	            if ($return->DesvioFluxo) {
	                $this->checkDesvio($return->DesvioFluxo);
	            }
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function efetiva($order) {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVEfetivaV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('Y-m-dTH:i:s');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->Administradora = $this->getAdministradora();
	    $obj->Terminal = '00000000';
	    $obj->OrigemServico = 'B';
	    $obj->IdOrigem = $this->_cnpj;
	    $obj->OpcaoOperador = 'X';
	    $obj->Usuario = 'B2C';
	    $obj->Documento = $order;

	    $return = null;
	    try {
	        $response = $this->getClient()->PDVEfetiva($obj);
	        $this->_debug("PDVEfetivaResponse: " . var_export($response,true));
	
	        if (!empty($response)) {
	            $return = new SimpleXMLElement($response->PDVEfetivaResult);
	            $this->updateSessionData($return);
	            if ($return->DesvioFluxo) {
	                $this->checkDesvio($return->DesvioFluxo);
	            }
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function finaliza($order,$func) {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVFinalizaTrnV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('Y-m-dTH:i:s');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->Administradora = $this->getAdministradora();
	    $obj->Terminal = '00000000';
	    $obj->OrigemServico = 'B';
	    $obj->IdOrigem = $this->_cnpj;
	    $obj->OpcaoOperador = 'X';
	    $obj->Usuario = 'B2C';
	    $obj->Documento = $order;
	    $obj->Funcao = $func;
	
	    $return = null;
	    try {
	        $response = $this->getClient()->PDVFinalizaTrn($obj);
	        $this->_debug("PDVFinalizaTrnResponse: " . var_export($response,true));
	
	        if (!empty($response)) {
	            $return = $response->PDVFinalizaTrnResult;
	            $this->updateSessionData($return);
	            if ($return->DesvioFluxo) {
	                $this->checkDesvio($return->DesvioFluxo);
	            }
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}
	
	public function ativarProduto($card,$productId) {
	    $this->_url = 'https://portal.advantagecentre.com.br/PDVAtivarPrdV1.asmx?wsdl';
	    $this->_client = NULL;
	    $obj = new stdClass();
	    $obj->SeuSistema = $this->_sistema;
	    $obj->NrLocal = $this->getTransaction();
	    $obj->HoraLocal = date('Y-m-dTH:i:s');
	    $obj->Sessao = $this->getChave();
	    $obj->NrCentral = $this->getNrCentral();
	    $obj->HoraCentral = $this->getHoraCentral();
	    $obj->CtlAP = $this->getCtlAP();
	    $obj->Administradora = $this->getAdministradora();
	    $obj->Terminal = '00000000';
	    $obj->OrigemServico = 'B';
	    $obj->IdOrigem = $this->_cnpj;
	    $obj->OpcaoOperador = 'X';
	    $obj->Usuario = 'B2C';
	    $obj->CPFConsumidor = '00000000000';
	    $obj->Cartao = $card;
	     
	    $product = Mage::getModel('catalog/product')->load($productId);
	    $obj->EAN = $product->getEan();
	     
	    $return = null;
	    try {
	        $response = $this->getClient()->AtivarPrdV1($obj);
	        $this->_debug("AtivarPrdV1Response: " . var_export($response,true));
	
	        if (!empty($response)) {
	            $return = new SimpleXMLElement($response->AtivarPrdV1Result);
	            $this->updateSessionData($return);
	            if ($return->DesvioFluxo) {
	                $this->checkDesvio($return->DesvioFluxo);
	            }
	        }
	    } catch (Exception $e) {
	        $this->_debug('Erro: ' . $e->getMessage());
	    }
	    return $return;
	}

	public function validaBeneficio($data) {
	    $card = $data['card'];
        if (Mage::getResourceModel('smartpbm/products')->checkProgram($data['product'],'sevenpdv')) { // Produto é elegível ao SevenPDV
            $product = Mage::getModel('catalog/product')->load($data['product']);
            if ($product->getEan()) {
                $this->logon();
                $price = $this->consultaProduto($data);
                if ($price->StatusServico == 0) {
                    $discount = str_replace(',','.',$price->Lista->ListaProdutos->DescPerc);
                    Mage::getSingleton('checkout/session')->setSmartpbmPbm('sevenpdv');
                    Mage::getSingleton('checkout/session')->setSmartpbmCard($data['card']);
                    Mage::getSingleton('checkout/session')->setSmartpbmDiscount($discount/100);
                    Mage::getSingleton('checkout/session')->setSmartpbmEan($product->getEan());
                    return true;
                }
            }
        }
	     
	    return false;
	}
	
	public function confirmaBeneficio($data) {
	    $this->finaliza($data['order'],'F');
	}
	
}
