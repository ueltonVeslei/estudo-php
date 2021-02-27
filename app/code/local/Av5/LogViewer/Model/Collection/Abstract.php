<?php

class Av5_LogViewer_Model_Collection_Abstract extends Varien_Data_Collection_Filesystem {

	protected $_baseDir;

	public function __construct()
	{
		parent::__construct();
		$this->setOrder('time', self::SORT_ORDER_DESC)
			->addTargetDir($this->_baseDir)
			->setFilesFilter('/^[a-z0-9\-\_\.]+$/')
			->setCollectRecursively(false)
		;
	}

	protected function _generateRow($filename) {
		$row = parent::_generateRow($filename);
		foreach (Mage::getSingleton('logviewer/file')->load($row['basename'], $this->_baseDir)->getData() as $key => $value) {
			$row[$key] = $value;
		}
		$row['size'] = number_format(filesize($filename)/1024,2) . 'KB';
		//$row['id'] = $row['time'] . '_' . $row['type'];
		return $row;
	}
}