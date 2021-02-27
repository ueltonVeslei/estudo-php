<?php

abstract class MageMigrator_Migrator_Model_Type_Abstract extends Mage_Payment_Model_Method_Abstract {
	
	/**
	 * @desc Inicia o processo de exportação
	 */
	abstract public function export();
	
	/**
	 * @desc Inicia o processo de importação
	 */
	abstract public function import();
	
}