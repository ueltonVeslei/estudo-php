<?php
class Av5_LogViewer_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Av5 Log Viewer"));
	   $this->renderLayout();
    }
	
    public function viewAction() {
        $this->loadLayout();
        $this->_title($this->__("Av5 Log Viewer"));
        $this->renderLayout();
    }
    
    public function reportsAction() {
        $this->loadLayout();
        $this->_title($this->__("Av5 Log Viewer"));
        $this->renderLayout();
    }
    
	public function postAction() {
		$post = $this->getRequest()->getPost();
		try {
			if (empty($post)) {
				Mage::throwException($this->__('Erro ao processar a solicita&ccedil;&atilde;o.'));
			}
			
			$message='';
			if(isset($post['log'])){
				$fp = fopen(Mage::getBaseDir('log') . '/' . $post['log'], "r+");
				ftruncate($fp, 0);
				fclose($fp);
				$message = $this->__('O arquivo ' . $post['log'] . ' foi limpo com sucesso.');
				Mage::getSingleton('adminhtml/session')->addSuccess($message);
			}
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}	
	
	function downloadAction($file_name) {
	    $file_name = $this->getRequest()->getParam('filename');
	    $file = Mage::getBaseDir('log').'/'.$file_name.'.log';
	
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