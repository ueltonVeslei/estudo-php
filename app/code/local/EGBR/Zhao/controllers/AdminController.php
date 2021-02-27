<?php

class EGBR_Zhao_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('zhao/observer')->sendProductTozhao();
    }
    
    public function novoAction(){
    	Mage::getModel('zhao/observer')->changeAttSet();
    }
}