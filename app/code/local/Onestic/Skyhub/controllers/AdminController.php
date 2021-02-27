<?php
class Onestic_Skyhub_AdminController extends Mage_Adminhtml_Controller_Action
{
    
    public function sendAction() {
        $products = $this->getRequest()->getParam('product');
        $result = Mage::getModel('onestic_skyhub/products_updater')->sendSelection($products);
         
        if ($result['errors']) {
            Mage::getSingleton('adminhtml/session')->addError($result['errors'] . " produtos não puderam ser enviados!");
        }
         
        if ($result['success']) {
            Mage::getSingleton('adminhtml/session')->addSuccess($result['success'] . " produtos foram enviados!");
        }
         
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalog_product/index", array('product'=>$products)));
    }
	
    public function syncAction() {
        $orders = $this->getRequest()->getParam('order_ids');
        $checker = Mage::getModel('onestic_skyhub/checker');
        $errors = $success = 0;
        foreach($orders as $order) {
            $result = $checker->checkOrder($order);
            if ($result) {
                $success++;
            } else { 
                $errors++;
            }
        }
        
        if ($success) {
            Mage::getSingleton('adminhtml/session')->addSuccess($success . " pedidos sincronizados com Skyhub!");
        }
        
        if ($errors) {
            Mage::getSingleton('adminhtml/session')->addError($errors . " pedidos não sincronizaram com Skyhub!");
        }
        
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index", array('order_ids'=>$orders)));
    }
    
    public function indexAction() {
        $this->loadLayout()->renderLayout();
    }
    
    public function postAction() {
        try {
            $order = $this->getRequest()->getParam('order');
            Mage::getModel('onestic_skyhub/updater')->orderFix($order);
            Mage::getSingleton('adminhtml/session')->addSuccess("Pedido " . $order . " sincronizado com sucesso!");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError("Erro ao sincronizar o pedido " . $order . ": " . $e->getMessage());
        }
        $this->_redirect('*/*');
    }
}