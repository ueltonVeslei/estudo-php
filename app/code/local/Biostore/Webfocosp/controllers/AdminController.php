<?php

class Biostore_Webfocosp_AdminController extends Mage_Core_Controller_Front_Action
{
    public function testAction() {
    	Mage::getModel('webfocosp/observer')->sendProductToWebfocosp();
    }
}
