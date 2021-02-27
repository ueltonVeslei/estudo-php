<?php  

class Av5_Logviewer_Block_Adminhtml_Backend extends Mage_Adminhtml_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setFormAction(Mage::getUrl('*/*/post'));
    }
    
    public function getFile() {
        $file = array();
        $file['name'] = Mage::app()->getRequest()->getParam('name');
        $file['type'] = Mage::app()->getRequest()->getParam('type');
        $file['content'] = $this->readFile($file['name'],$file['type']);
        
        return $file;
    }
    
    public function readFile($name,$type='log') {
        $file = Mage::getBaseDir('var').'/'.$type.'/'.$name;
        $data = '';
        if (file_exists($file)) {
            $size = round((filesize($file)/1024)/1024);
            if ($size < 21){
                $data = file_get_contents($file);
            } else {
                $data = "Arquivo maior que 20MB. Efetue o download do mesmo.\n\nTamanho atual: " . $size. "MB";
            }
        } else {
            $data = "Arquivo " . $name . " inexistente!";
        }
        
        return $data;
    }
}