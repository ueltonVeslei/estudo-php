<?php 
class Av5_LogViewer_Model_File extends Varien_Object
{

	public function load($fileName, $filePath) {
        $this->addData(array(
            //'id'   => $fileName,
            'path' => $filePath,
            'name' => $fileName,
        	'time' => filemtime($filePath . DS . $fileName),
            'date_object' => new Zend_Date((int)filemtime($filePath . DS . $fileName), Mage::app()->getLocale()->getLocaleCode())
        ));
        
        if ($filePath == Mage::getBaseDir('log')) {
        	$type = 'log';
        } elseif ($filePath == (Mage::getBaseDir('var') . '/report/')) {
        	$type = 'report';
        } else {
            $type = 'export';
        }
        
        $this->setType($type);

        return $this;
    }
}
