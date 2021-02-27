<?php
class Av5_LogViewer_Adminhtml_LogsController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Av5 Log Viewer"));
	   $this->renderLayout();
    }
	
    public function viewAction() {
    	$this->getRequest()->setParam('type','log');
        $this->loadLayout();
        $this->_title($this->__("Av5 Log Viewer"));
        $this->renderLayout();
    }
    
	public function postAction() {
		$log = $this->getRequest()->getParam('name');
		try {
			if (empty($log)) {
				Mage::throwException($this->__('Erro ao processar a solicita&ccedil;&atilde;o.'));
			}
			
			$message='';
			if($log){
				$fp = fopen(Mage::getBaseDir('log') . DS . $log, "r+");
				ftruncate($fp, 0);
				fclose($fp);
				$message = $this->__('O arquivo ' . $log . ' foi limpo com sucesso.');
				Mage::getSingleton('adminhtml/session')->addSuccess($message);
			}
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}

	public function deleteAction() {
		$log = $this->getRequest()->getParam('name');
		try {
			if (empty($log)) {
				Mage::throwException($this->__('Erro ao processar a solicita&ccedil;&atilde;o.'));
			}
	
			$message='';
			if($log){
				unlink(Mage::getBaseDir('log') . DS . $log);
				$message = $this->__('O arquivo ' . $log . ' foi excluído com sucesso.');
				Mage::getSingleton('adminhtml/session')->addSuccess($message);
			}
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}
	
	function downloadAction($file_name) {
	    $file_name = $this->getRequest()->getParam('name');
	    $file = Mage::getBaseDir('log') . DS . $file_name;
	
	    if(!file_exists($file)) die("Arquivo não existe!");
	
	    $type = filetype($file);
	    $today = date("F j, Y, g:i a");
	    $time = time();
	    header("Content-type: $type");
	    header("Content-Disposition: attachment;filename=$file_name");
	    header("Content-Transfer-Encoding: binary");
	    header('Pragma: no-cache');
	    header('Expires: 0');
	    set_time_limit(0);
	    readfile($file);
	}
	
	
}