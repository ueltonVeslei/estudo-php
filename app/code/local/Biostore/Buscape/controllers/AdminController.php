<?php

class Biostore_Buscape_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('buscape/observer')->sendProductToBuscape();
    }
}
