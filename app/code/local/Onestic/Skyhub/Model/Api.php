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
 * @package    Onestic_Skyhub
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Skyhub_Model_Api {

    public function consultarEstoqueProdutos($itens) {
        $result = array();
        foreach ($itens as $item) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
            if ($product) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if ($stock->getQty() > $item->Quantidade) {
                    $status = 'ProdutoEmEstoque';
                } else {
                    $status = 'ProdutoForaDeEstoque';
                }
            } else {
                $status = 'ProdutoNaoEncontrado';
            }
            $result[] = array('EAN' => $item->EAN, 'Status' => $status);
        }
        
        return $result;
    }
    
    public function consultarProdutos($requisicao) {
        $result = array(
            'CEP'           => $requisicao->CEP,
            'IdConsulta'    => $requisicao->IdConsulta,
            'UFEnvio'       => Mage::helper("onestic_vidalink")->getUf($requisicao->CEP),
        );

        $itensConsulta = $this->_itensConsultaProduto($requisicao->Itens);
        $result['Itens'] = $itensConsulta['Itens'];
        
        if ($itensConsulta['Disponibilidade']) {
            $result['ValorFrete'] = Mage::getModel("onestic_vidalink/cart")->estimatePost($requisicao->CEP, $requisicao->Itens);
        } else {
            $result['ValorFrete'] = 0;
        }
        
        return $result;
    }
    
    public function enviarPedido($pedido) {
        $result = Mage::getModel('onestic_vidalink/order')->create($pedido);
        return $result;
    }
    
    public function consultarConfirmacaoPedido($idPedido) {
        $result = $this->_getConfirmacaoPedido($idPedido);
        return $result;
    }
    
    public function consultarConfirmacaoEntregaPedido($idPedido) {
        $result = $this->_getConfirmacaoEntregaPedido($idPedido);
        return $result;
    }
    
    public function ConsultarConfirmacaoPedidosPorData($inicio,$fim) {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from'=>$inicio, 'to'=>$fim))
            ->addAttributeToFilter('ov_referencia',array('notnull' => true));
        $result = array();
        foreach ($orders as $order) { 
            $result[] = $this->_getConfirmacaoPedido($order->getId());
        }
        return $result;
    }
    
    public function consultarEntregaPedidosPorData($inicio,$fim) {
        $orders = Mage::getModel('sales/order')->getCollection()
        ->addAttributeToFilter('created_at', array('from'=>$inicio, 'to'=>$fim))
        ->addAttributeToFilter('ov_referencia',array('notnull' => true));
        $result = array();
        foreach ($orders as $order) {
            $result[] = $this->_getConfirmacaoEntregaPedido($order->getId());
        }
        return $result;
    }
    
    protected function _getConfirmacaoPedido($idPedido) {
        $order = Mage::getModel('sales/order')->load($idPedido);
        $result = array(
            'AutorizacaoVidaLink'       => $order->getOvAutorizacao(),
            'CNPJ'                      => Mage::helper('onestic_vidalink')->getCnpjEmissorNf(),
            'DataVenda'                 => str_replace(' ','T',$order->getCreatedAt()),
            'IdPedido'                  => $order->getId(),
            'Itens'                     => $this->_orderItems($order),
            'NotaFiscal'                => $order->getOvNotaFiscal(),
            //'NrPDV'                     => Mage::helper('onestic_vidalink')->getPdv(),
            'VendaRealizada'            => (!in_array($order->getStatus(),array('pending','canceled','hold'))) ? true : false
        );
        
        return $result;
    }
    
    protected function _getConfirmacaoEntregaPedido($idPedido) {
        $order = Mage::getModel('sales/order')->load($idPedido);
        $result = array(
            'AutorizacaoVidaLink'       => $order->getOvAutorizacao(),
            'DataEntrega'               => str_replace(' ','T',$order->getUpdatedAt()),
            'EntregaRealizada'          => ($order->getStatus() == 'complete') ? true : false,
            //'HoraEntrega'             => '',
            'IdPedido'                  => $order->getId(),
            'NotaFiscal'                => $order->getOvNotaFiscal(),
        );
        
        return $result;
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
    
}