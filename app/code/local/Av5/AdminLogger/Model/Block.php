<?php
class Av5_AdminLogger_Model_Block extends Mage_Cms_Model_Resource_Block {

	protected function _afterSave(Mage_Core_Model_Abstract $object)
	{
		$message = [
			'TIPO: BLOCO',
			'BLOCO ID: ' . $object->getData('identifier')
		];

		Mage::helper('av5_adminlogger')->logMessage($message);

		return parent::_afterSave($object);
	}

}