<?php

class EGBR_cliquefarma_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('cliquefarma/observer')->sendProductTocliquefarma();
    }
    
    public function novoAction(){
    	Mage::getModel('cliquefarma/observer')->changeAttSet();
    }
}