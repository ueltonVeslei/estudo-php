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
 * @package    Onestic_Vidalink
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Vidalink_Model_Api extends Mage_Api_Model_Resource_Abstract {

    public function consultarEstoqueProdutos($Itens) {
    	Mage::helper('onestic_vidalink')->log('CONSULTAR ESTOQUE PRODUTOS: ' . var_export($Itens,true));
        $result = array();
        foreach ($Itens as $item) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
            if ($product) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if ($stock->getQty() >= $item->Quantidade && $stock->getIsInStock() == 1) {
                    $status = 'ProdutoEmEstoque';
                } else {
                    $status = 'ProdutoForaDeEstoque';
                }
            } else {
                $status = 'ProdutoNaoEncontrado';
            }
            $result[] = array('EAN' => $item->EAN, 'Status' => $status);
        }
        $object = new stdClass();
        $object->complexObjectArray = $result;
        Mage::helper('onestic_vidalink')->log('RETORNO CONSULTAR ESTOQUE PRODUTOS: ' . var_export($result,true));
        return $object;
    }
    
    public function consultarProdutos($Requisicao) {
    	Mage::helper('onestic_vidalink')->log('CONSULTAR PRODUTOS: ' . var_export($Requisicao,true));
        $result = array(
            'CEP'           => $Requisicao->CEP,
            'IdConsulta'    => $Requisicao->IdConsulta,
            'UFEnvio'       => Mage::helper("onestic_vidalink")->getUf($Requisicao->CEP),
        );

        $itensConsulta = $this->_itensConsultaProduto($Requisicao->Itens);
        $result['Itens'] = $itensConsulta['Itens'];
        
        if ($itensConsulta['Disponibilidade']) {
            $result['ValorFrete'] = Mage::getModel("onestic_vidalink/cart")->estimatePost($Requisicao->CEP, $itensConsulta['Itens']);
        } else {
            $result['ValorFrete'] = 0;
        }
        Mage::helper('onestic_vidalink')->log('RETORNO CONSULTAR PRODUTOS: ' . var_export($result,true));
        return $result;
    }
    
    public function enviarPedido($Pedido) {
    	Mage::helper('onestic_vidalink')->log('ENVIAR PEDIDO: ' . var_export($Pedido,true));
        $result = Mage::getModel('onestic_vidalink/order')->create($Pedido);
        Mage::helper('onestic_vidalink')->log('RETORNO ENVIAR PEDIDO: ' . var_export($result,true));
        return $result;
    }
    
    public function consultarConfirmacaoPedido($IdPedido) {
    	Mage::helper('onestic_vidalink')->log('CONSULTAR CONFIRMAÇÃO PEDIDO: ' . $IdPedido);
        $result = $this->_getConfirmacaoPedido($IdPedido);
        Mage::helper('onestic_vidalink')->log('RETORNO CONFIRMAÇÃO PEDIDO: ' . var_export($result,true));
        return $result;
    }
    
    public function consultarConfirmacaoEntregaPedido($IdPedido) {
    	Mage::helper('onestic_vidalink')->log('CONSULTAR CONFIRMAÇÃO ENTREGA PEDIDO: ' . $IdPedido);
        $result = $this->_getConfirmacaoEntregaPedido($IdPedido);
        Mage::helper('onestic_vidalink')->log('RETORNO CONFIRMAÇÃO ENTREGA PEDIDO: ' . var_export($result,true));
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
            'AutorizacaoVidalink'       => $order->getData('ov_autorizacao'),
            'CNPJ'                      => Mage::helper('onestic_vidalink')->getCnpjEmissorNf(),
            'DataVenda'                 => str_replace(' ','T',$order->getCreatedAt()),
            'IdPedido'                  => $order->getId(),
            'Itens'                     => $this->_orderItems($order),
            'ChaveAcessoNFe'            => $this->_getNotaFiscal($order),
            //'NrPDV'                     => Mage::helper('onestic_vidalink')->getPdv(),
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
            'AutorizacaoVidalink'       => $order->getOvAutorizacao(),
            'DataEntrega'               => str_replace(' ','T',$order->getUpdatedAt()),
            'EntregaRealizada'          => 1,
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
                $precoLiquido = $product->getFinalPrice($item->Quantidade);
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
    
}