<?php

class Biostore_Maispreco_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('maispreco/observer')->sendProductToMaispreco();
    }
}
