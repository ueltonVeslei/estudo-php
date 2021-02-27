<?php
class Av5_LogViewer_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function getLogFiles() {
    	return $this->_getFiles(Mage::getBaseDir('log'));
    }
    
    public function getReportFiles() {
        return $this->_getFiles(Mage::getBaseDir('var') . '/report/');    
    }

    public function getExportFiles() {
        return $this->_getFiles(Mage::getBaseDir('var') . '/export/');
    }
    
    protected function _getFiles($location) {
    	$files = array();
    	if ($handle = opendir($location)) {
    		while (false !== ($entry = readdir($handle))) {
    			if ($entry != "." && $entry != "..") {
    				$files[] = $entry;
    			}
    		}

    		asort($files);
    		
    		closedir($handle);
    	}
    	
    	return $files;
    }
    
}
	 