<?php
class Controller_RecoveryPassword extends Controller {

    /**
     * Função que envia um email com o código de recuperação para o email do cliente
     * e grava o código no banco 
     */
    protected function _post() {
		//variáveis de retorno
		$status = Standard::STATUS200;
		$res = '';

		if ($post = $this->getData('body')) {
			//Model customer 
			$customer = new Model_Customer();
			$res = $customer->sendRecovery($post);
		} else {
			$status = Standard::STATUS404;
			$res = 'Dados não informados';
		}
		$this->setResponse('status',$status);
		$this->setResponse('data',$res);
	}

	/**
     * Verifica o email do cliente e o código que foi enviado por email e o status
     * do código, se estiver ok altera a senha e altera o status do código para 0
     * que é desativado.
     */
	protected function _put() {
		//variáveis de retorno
		$status = Standard::STATUS200;
		$res = '';
		if ($post = $this->getData('body')) {
			//Model customer 
			$customer = new Model_Customer();
			$res = $customer->updatePassword($post);
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
		$this->setResponse('status',$status);
		$this->setResponse('data',$res);
	}

}