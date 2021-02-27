<?php 
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Integrator
 * @package    Onestic_ApiServer
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_ApiServer_Model_Api extends Mage_Api_Model_Resource_Abstract {

    public function consultarEstoqueProdutos($Itens) {
    	Mage::helper('onestic_apiserver')->log('CONSULTAR ESTOQUE PRODUTOS: ' . var_export($Itens,true));
        $result = array();
        foreach ($Itens as $item) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
            Mage::helper('onestic_apiserver')->log('CONSULTAR ESTOQUE PRODUTO: ' . $product->getCodigoBarras() . ' - ' . $product->getName());
            if ($product) {
                if($product->getTypeId() == "grouped"){
                    $qtd=100000;
                    $products = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    foreach($products as $p){
                        $productSimple = Mage::getModel('catalog/product')->load($p->getId());
                        $stocksimple = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productSimple);
                        Mage::log($p->getId() . " - " . $stocksimple->getQty(), null, 'qtd.log');
                        if($stocksimple->getQty() < $qtd){
                            $qtd = $stocksimple->getQty();
                        }
                    }
                    $status = $qtd;
                }else{
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                    if ($stock->getQty() > $item->Quantidade && $stock->getIsInStock() == 1) {
                        $status = $stock->getQty();
                    } else {
                        $status = $stock->getQty();
                    }                    
                }
            } else {
                $status = 'ProdutoNaoEncontrado';
            }
            $result[] = array('EAN' => $item->EAN, 'Status' => $status);
        }
        $object = new stdClass();
        $object->complexObjectArray = $result;
        return $object;
    }
	
    public function consultarEstoque($Requisicao) {
    	Mage::helper('onestic_apiserver')->log('CONSULTAR ESTOQUE: ' . var_export($Requisicao,true));

		$product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$Requisicao->CodigoBarra);
		if ($product) {
			$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
			if ($stock->getQty() > $item->Quantidade && $stock->getIsInStock() == 1) {
				$result = $stock->getQty();
			} else {
				$result = 0;
			}
		} else {
			$result = 0;
		}
        
        return $result;
    }	
    
    public function consultarProdutos($Requisicao) {
    	Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS: ' . var_export($Requisicao,true));
        //$result = array(
        //    'CEP'           => $Requisicao->CEP,
        //    'IdConsulta'    => $Requisicao->IdConsulta,
        //    'UFEnvio'       => Mage::helper("onestic_apiserver")->getUf($Requisicao->CEP),
        //);

        //$itensConsulta = $this->_itensConsultaProduto($Requisicao->Itens);
        //$result['Itens'] = $itensConsulta['Itens'];
        
        //if ($itensConsulta['Disponibilidade']) {
            $result['RetornoFrete'] = Mage::getModel("onestic_apiserver/cart")->estimatePost($Requisicao);
			Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - Result: ' . var_export($result,true));
        //} else {
        //    $result['ValorFrete'] = 0;
        //}
        
        return $result;
    }
    
    public function consultarConfirmacaoPedido($IdPedido) {
    	Mage::helper('onestic_apiserver')->log('CONSULTAR CONFIRMAÇÃO PEDIDO: ' . $IdPedido);
        $result = $this->_getConfirmacaoPedido($IdPedido);
        return $result;
    }
    
    public function consultarConfirmacaoEntregaPedido($IdPedido) {
    	Mage::helper('onestic_apiserver')->log('CONSULTAR CONFIRMAÇÃO ENTREGA PEDIDO: ' . $IdPedido);
        $result = $this->_getConfirmacaoEntregaPedido($IdPedido);
        return $result;
    }
    
    public function ConsultarConfirmacaoPedidosPorData($DataInicio,$DataFinal) {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from'=>$DataInicio, 'to'=>$DataFinal))
            ->addAttributeToFilter('ov_referencia',array('notnull' => true));
        $result = array();
        foreach ($orders as $order) { 
            $result[] = $this->_getConfirmacaoPedido($order->getId());
        }
        $object = new stdClass();
        $object->complexObjectArray = $result;
        return $object;
    }
    
    public function consultarEntregaPedidosPorData($DataInicio,$DataFinal) {
        $orders = Mage::getModel('sales/order')->getCollection()
        ->addAttributeToFilter('created_at', array('from'=>$DataInicio, 'to'=>$DataFinal))
        ->addAttributeToFilter('ov_referencia',array('notnull' => true));
        $result = array();
        foreach ($orders as $order) {
            $result[] = $this->_getConfirmacaoEntregaPedido($order->getId());
        }
        $object = new stdClass();
        $object->complexObjectArray = $result;
        return $object;
    }
    
    protected function _getConfirmacaoPedido($IdPedido) {
        if (strlen($IdPedido) >= 9) { //increment_id
            $aux = Mage::getModel("sales/order")->loadByAttribute("increment_id", $IdPedido);
            $IdPedido = $aux->getId();
        }
        $order = Mage::getModel('sales/order')->load($IdPedido);
        $result = array(
            'AutorizacaoApiServer'       => $order->getData('ov_autorizacao'),
            'CNPJ'                      => Mage::helper('onestic_apiserver')->getCnpjEmissorNf(),
            'DataVenda'                 => str_replace(' ','T',$order->getCreatedAt()),
            'IdPedido'                  => $order->getId(),
            'Itens'                     => $this->_orderItems($order),
            'ChaveAcessoNFe'            => $this->_getNotaFiscal($order),
            //'NrPDV'                     => Mage::helper('onestic_apiserver')->getPdv(),
            'VendaRealizada'            => (!in_array($order->getStatus(),array('pending','canceled','hold'))) ? true : false
        );
        
        return $result;
    }
    
    protected function _getConfirmacaoEntregaPedido($IdPedido) {
        if (strlen($IdPedido) >= 9) { //increment_id
            $aux = Mage::getModel("sales/order")->loadByAttribute("increment_id", $IdPedido);
            $IdPedido = $aux->getId();
        }
        $order = Mage::getModel('sales/order')->load($IdPedido);
        $result = array(
            'AutorizacaoApiServer'       => $order->getOvAutorizacao(),
            'DataEntrega'               => str_replace(' ','T',$order->getUpdatedAt()),
            'EntregaRealizada'          => ($order->getStatus() == 'complete') ? true : false,
            //'HoraEntrega'             => '',
            'IdPedido'                  => $order->getId(),
            'ChaveAcessoNFe'             => $this->_getNotaFiscal($order),
        );
        
        return $result;
    }
    
    protected function _getNotaFiscal($order) {
        $nfKey = '';
        foreach ($order->getStatusHistoryCollection() as $status) {
            if (strpos($status->getComment(), 'NF ') !== false) {
                $nfKey = str_replace('NF ','',$status->getComment());
                break;
            }
        }
        
        return trim($nfKey);
    }
    
    protected function _itensConsultaProduto($itens) {
        $result = array('Disponibilidade' => true, 'Itens' => array());
        foreach ($itens as $item) {
            $status = $desconto = $precoBruto = $precoLiquido = 0;
            $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
            if ($product) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $precoLiquido = $product->getFinalPrice();
                $precoBruto = $product->getPrice();
                if ($precoLiquido <> $precoBruto) {
                    $desconto = ( ($precoBruto - $precoLiquido) * 100 ) / $precoBruto;
                }
                if ($stock->getQty() < $item->Quantidade) {
                    $result['Disponibilidade'] = false;
                    $status = 6; // ProdutoSemEstoque
                }
            } else {
                $status = 2; // EanNaoEncontrado
            }
            $result['Itens'][] = array(
                'EAN'                   => $item->EAN,
                'PercentualDesconto'    => $desconto,
                'Quantidade'            => $item->Quantidade,
                'Status'                => $status,
                'ValorUnitarioBruto'    => $precoBruto,
                'ValorUnitarioLiquido'  => $precoLiquido
            );
        }
        
        return $result;
    }
    
    protected function _orderItems($order) {
        $items = $order->getItemsCollection();
        $result = array();
        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $result[] = array('EAN' => $product->getCodigoBarras(),'Quantidade' => $item->getQtyOrdered());
        }
        return $result;
    }
	
    public function enviarPedido($Pedido) {
    	Mage::helper('onestic_apiserver')->log('ENVIAR PEDIDO: ' . var_export($Pedido,true));
        Mage::getModel('onestic_apiserver/orders')->create($Pedido);
        $result = array(
            'IdRetornoPedido'       => 11111,
            'StatusProcessamento'   => 1,
            'Mensagem'              => ''
        );
        return $result;
    }
	
    public function syncInvoice($order) {
        
		return;
    }	
	
    public function syncShipment($order) {
        
		return;
    }		
    
}