<?php

class Biostore_Liquidacaovirtual_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('liquidacaovirtual/observer')->sendProductToLiquidacaovirtual();
    }
}
