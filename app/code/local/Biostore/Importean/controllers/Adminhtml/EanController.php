<?php
class Biostore_Importean_Adminhtml_EanController
    extends Mage_Adminhtml_Controller_Action
{
	
    public function indexAction()
    {  
    	/*
    	$this->_initAction();
    	$this->loadLayout();
    	$this->getResponse()->setBody($this->getLayout()->createBlock('followupemail/adminhtml_queue_grid')->toHtml());
    	*/
    	
    	$this->_initAction()
    	//->_addBreadcrumb($id ? $this->__('Edit Baz') : $this->__('New Baz'), $id ? $this->__('Edit Baz') : $this->__('New Baz'))
    	->_addContent($this->getLayout()->createBlock('importean/adminhtml_ean'))
    	->renderLayout();
    	
    	// Let's call our initAction method which will set some basic params for each action
        //$this->_initAction()
        //    ->renderLayout();
    }  
     
    public function newAction()
    {  
        // We just forward the new action to a blank edit form
        //$this->_forward('edit');
    }  
    
     
    protected function _initAction()
    {
    	$this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            //->_setActiveMenu('importean/ean')
            ->_title('Lista de EAN dos Produtos');
         
        return $this;
    }
    
	protected function _isAllowed()
    {
    	
    	//var_dump(Mage::getSingleton('admin/session')->isAllowed('admin/importean/ean'));
    	//die();
    	
        return true;//Mage::getSingleton('admin/session')->isAllowed('admin/importean/ean');
    }
    
}